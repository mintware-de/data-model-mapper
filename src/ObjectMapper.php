<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\DMM;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;

use MintWare\DMM\Exception\ClassNotFoundException;
use MintWare\DMM\Exception\PropertyNotAccessibleException;
use MintWare\DMM\Exception\SerializerException;
use MintWare\DMM\Exception\TypeMismatchException;

use MintWare\DMM\Serializer\PropertyHolder;
use MintWare\DMM\Serializer\SerializerInterface;

/**
 * This class is the object mapper
 * To map a json string to a object you can easily call the
 * ObjectMapper::mapJson($json, $targetClass) method.
 *
 * @package MintWare\DMM
 */
class ObjectMapper
{
    /** @var AnnotationReader */
    protected $reader = null;

    /** @var SerializerInterface */
    private $serializer = null;

    private $primitives = [
        'int', 'integer',
        'float', 'double', 'real',
        'bool', 'boolean',
        'array',
        'string',
        'object',
        'date', 'datetime'
    ];

    /**
     * Instantiates a new ObjectMapper
     *
     * @param SerializerInterface|null $serializer The serializer
     * @throws \Exception If the Annotation reader could not be initialized
     */
    public function __construct($serializer = null)
    {
        // Symfony does this also.. ;-)
        AnnotationRegistry::registerLoader('class_exists');

        // Set the annotation reader
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        try {
            $this->reader = new AnnotationReader($parser);
        } catch (\Exception $e) {
            throw new \Exception("Failed to initialize the AnnotationReader", null, $e);
        }

        $this->serializer = $serializer;
    }

    /**
     * Maps raw data to a object
     *
     * @param string $rawData The raw data
     * @param string $targetClass The target object class
     *
     * @return mixed The mapped object
     *
     * @throws SerializerException If the data couldn't be deserialized
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the class property has no public access an no set-Method
     * @throws TypeMismatchException If The type in the raw data does not match the type in the class
     * @throws \ReflectionException If the target class does not exist
     */
    public function map($rawData, $targetClass)
    {
        // Deserialize the data
        try {
            if ($this->serializer instanceof SerializerInterface === false) {
                throw new SerializerException("You've to specify a serializer with the setSerializer() method.");
            }

            $data = $this->serializer->deserialize($rawData);
        } catch (\Exception $e) {
            throw new SerializerException('Deserialize failed: ' . $e->getMessage(), 0, $e);
        }

        // Pre initialize the result
        $result = null;

        // Check if the target object is a collection of type X
        if (substr($targetClass, -2) == '[]') {
            $result = [];
            foreach ($data as $key => $entryData) {
                // Map the data recursive
                $result[] = $this->mapDataToObject($entryData, substr($targetClass, 0, -2));
            }
        } else {
            // Map the data recursive
            $result = $this->mapDataToObject($data, $targetClass);
        }

        return $result;
    }

    /**
     * Maps the  current entry to the property of the object
     *
     * @internal
     *
     * @param array $data The array of data
     * @param string $targetClass The current object class
     *
     * @return mixed The mapped object
     *
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the mapped property is not accessible
     * @throws TypeMismatchException If the given type in the raw data does not match with the expected type
     * @throws \ReflectionException If the class does not exist.
     */
    public function mapDataToObject($data, $targetClass)
    {
        $targetClass = preg_replace('~(\\\\){2,}~', '\\', $targetClass);

        // Check if the target object class exists, if not throw an exception
        if (!class_exists($targetClass)) {
            throw new ClassNotFoundException($targetClass);
        }

        // Create the target object
        $object = new $targetClass();

        // Reflecting the target object to extract properties etc.
        $class = new \ReflectionClass($targetClass);

        // Iterate over each class property to check if it's mapped
        foreach ($class->getProperties() as $property) {

            // Extract the Annotations
            $fields = $this->reader->getPropertyAnnotations($property);

            /** @var DataField $field */
            foreach ($fields as $field) {
                if ($field instanceof DataField == false) {
                    continue;
                }

                // Check if the property is public accessible or has a setter / adder
                $propertyName = $property->getName();
                $ucw = ucwords($propertyName);
                if (!$property->isPublic()
                    && !($class->hasMethod('set' . $ucw) || $class->hasMethod('add' . $ucw))) {
                    throw new PropertyNotAccessibleException($propertyName);
                }

                if ($field->name == null) {
                    $field->name = $propertyName;
                }

                // Check if the current property is defined in the raw data
                if (!isset($data[$field->name])) continue;

                $currentEntry = $data[$field->name];

                $val = null;

                $types = explode('|', $field->type);
                $typeKeys = array_keys($types);
                $lastTypeKey = end($typeKeys);

                if ($field->preTransformer !== null) {
                    /** @var TransformerInterface $preTransformer */
                    $preTransformer = $field->preTransformer;
                    $currentEntry = $preTransformer::transform($currentEntry);
                }

                if ($field->transformer !== null) {
                    /** @var TransformerInterface $transformer */
                    $transformer = $field->transformer;
                    $val = $transformer::transform($currentEntry);
                    $types = []; // Ignore type handler!
                }

                foreach ($types as $typeKey => $type) {
                    $isLastElement = ($typeKey == $lastTypeKey);

                    // Check the type of the field and set the val
                    if ($type == '') {
                        $val = $currentEntry;
                    } elseif (in_array($type, $this->primitives)) {
                        $format = ($field instanceof DateTimeField && $field->format !== null
                            ? $field->format
                            : 'Y-m-d\TH:i:s');

                        $converted = null;
                        try {
                            $converted = $this->castType($currentEntry, $type, $field->name, $format, true);
                        } catch (TypeMismatchException $ex) {
                            if ($isLastElement) {
                                throw  $ex;
                            }
                            continue;
                        }
                        $val = $converted;
                    } else {
                        // If none of the primitives match it is an custom object

                        // Check if it's an array of X
                        if (substr($type, -2) == '[]' && is_array($currentEntry)) {
                            $t = substr($type, 0, -2);
                            $val = [];
                            foreach ($currentEntry as $entry) {
                                // Map the data recursive
                                $val[] = (object)$this->mapDataToObject($entry, $t);
                            }
                        } elseif (substr($type, -2) != '[]') {
                            // Map the data recursive
                            $val = (object)$this->mapDataToObject($currentEntry, $type);
                        }
                    }

                    if ($field->postTransformer !== null) {
                        /** @var TransformerInterface $postTransformer */
                        $postTransformer = $field->postTransformer;
                        $val = $postTransformer::transform($val);
                    }

                    if ($val !== null) {
                        break;
                    }
                }
                $this->setPropertyValue($object, $property, $val);

            }
        }
        return $object;
    }

    /**
     * Serializes an object to the raw format
     *
     * @param object $object The object
     * @param bool $returnAsString For internal usage
     * @return mixed|PropertyHolder[] The raw data or an [string => MetaDataValuePair] array
     *
     * @throws ClassNotFoundException If the target class does not exist
     * @throws PropertyNotAccessibleException If the mapped property is not accessible
     * @throws TypeMismatchException If the given type in the raw data does not match with the expected type
     * @throws SerializerException If the data couldn't be serialized
     */
    public function serialize($object, $returnAsString = true)
    {
        if ($returnAsString && $this->serializer instanceof SerializerInterface === false) {
            throw new SerializerException("You've to specify a serializer with the setSerializer() method.");
        }

        $dataForSerialization = [];
        // Reflecting the target object to extract properties etc.
        $class = new \ReflectionObject($object);

        // Iterate over each class property to check if it's mapped
        foreach ($class->getProperties() as $property) {
            // Extract the DataField Annotation

            /** @var DataField $field */
            $field = $this->reader->getPropertyAnnotation($property, DataField::class);

            // Is it not defined, the property is not mapped
            if (null === $field) {
                continue;
            }

            // Check if the property is public accessible or has a setter / adder
            $propertyName = $property->getName();
            $ucw = ucwords($propertyName);
            if (!$property->isPublic() && !($class->hasMethod('get' . $ucw))) {
                throw new PropertyNotAccessibleException($propertyName);
            }

            if ($field->name == null) {
                $field->name = $propertyName;
            }

            if (isset($dataForSerialization[$field->name]) && $dataForSerialization[$field->name] !== null) {
                continue;
            }

            $val = null;
            if ($property->isPublic()) {
                $val = $object->{$propertyName};
            } else {
                $val = $object->{'get' . $ucw}();
            }

            // Reverse order on encoding (postTransformer -> transformer -> preTransformer)
            if ($field->postTransformer !== null) {
                /** @var TransformerInterface $postTransformer */
                $postTransformer = $field->postTransformer;
                $val = $postTransformer::reverseTransform($val);
            }

            if ($field->transformer !== null) {
                /** @var TransformerInterface $transformer */
                $transformer = $field->transformer;
                $val = $transformer::reverseTransform($val);
            }

            if (is_null($val)) {
                $dataForSerialization[$field->name] = $val;
                continue;
            }

            if ($field->transformer === null) {
                $types = explode('|', $field->type);
                $type = null;

                foreach ($types as $tString) {
                    $type = $tString;
                    if (!is_object($val) || !in_array(strtolower($tString), $this->primitives)) {
                        break;
                    }
                }
                // Check the type of the field and set the val
                if (in_array($type, $this->primitives)) {
                    $format = 'Y-m-d\TH:i:s';
                    if ($field instanceof DateTimeField && $field->format !== null) {
                        $format = $field->format;
                    }
                    $val = $this->castType($val, $type, $propertyName, $format);
                } elseif ($type != null) {
                    // Check if it's an array of X
                    if (substr($type, -2) == '[]' && is_array($val)) {
                        $tmpVal = [];
                        foreach ($val as $entry) {
                            // Map the data recursive
                            $tmpVal[] = (object)$this->serialize($entry, false);
                        }
                        $val = $tmpVal;
                    } elseif (substr($type, -2) != '[]') {
                        // Map the data recursive
                        $val = (object)$this->serialize($val, false);
                    }
                }
            }

            if ($field->preTransformer !== null) {
                /** @var TransformerInterface $preTransformer */
                $preTransformer = $field->preTransformer;
                $val = $preTransformer::reverseTransform($val);
            }

            // Assign the raw data to the object property
            if ($val !== null) {
                // If the property is public accessible, set the value directly
                $dataForSerialization[$field->name] = new PropertyHolder($field, $property->name, $val);
            }
        }

        $res = $dataForSerialization;
        if ($returnAsString) {
            $res = $this->serializer->serialize($res);
        }

        return $res;
    }

    /**
     * @param mixed $dataToMap The data which should be mapped
     * @param string $type The target type
     * @param string $propertyName The name of the property (required for the exception)
     * @param string $datetimeFormat the format for DateTime deserialization
     * @param bool $fromRaw True, if the data comes from the raw data
     * @return mixed
     * @throws TypeMismatchException If the data does not match to the type
     * @throws \Exception If something went wrong during date casting
     *
     * @internal
     */
    private function castType($dataToMap, $type, $propertyName, $datetimeFormat, $fromRaw = false)
    {
        $dtCheck = function ($x) {
            return ($x instanceof \DateTime);
        };

        $checkMethod = [
            'int' => 'is_int', 'integer' => 'is_int',
            'float' => 'is_float', 'double' => 'is_float', 'real' => 'is_float',
            'bool' => 'is_bool', 'boolean' => 'is_bool',
            'date' => $dtCheck, 'datetime' => $dtCheck,
            'array' => 'is_array',
            'string' => 'is_string',
            'object' => function ($x) {
                return $x == false ? true : $x;
            },
        ];

        if (!isset($checkMethod[$type])) {
            return null;
        }

        if ($fromRaw && in_array($type, ['date', 'datetime'])) {
            // Accepts the following formats:
            // 2017-09-09
            // 2017-09-09 13:20:59
            // 2017-09-09T13:20:59
            // 2017-09-09T13:20:59.511
            // 2017-09-09T13:20:59.511Z
            // 2017-09-09T13:20:59-02:00
            $validPattern = '~^\d{4}-\d{2}-\d{2}((T|\s{1})\d{2}:\d{2}:\d{2}(\.\d{1,3}(Z|)|(\+|\-)\d{2}:\d{2}|)|)$~';

            $datetimeFormatPattern = preg_quote($datetimeFormat);
            $repl = [
                'd' => '\d{2}',
                'D' => '\w{3}',
                'j' => '\d{1,2}',
                'l' => '\w*',
                'N' => '\d{1}',
                'S' => '(st|nd|rd|th)',
                'w' => '\d{1}',
                'z' => '\d{1,3}',
                'W' => '\d{1,2}',
                'F' => '\w*',
                'm' => '\d{1,2}',
                'M' => '\w*',
                'n' => '\d{1,2}',
                't' => '\d{2}',
                'L' => '(0|1)',
                'o' => '\d{4}',
                'Y' => '\d{4}',
                'y' => '\d{2}',
                'a' => '(am|pm)',
                'A' => '(AM|PM)',
                'B' => '\d{3}',
                'g' => '\d{1,2}',
                'G' => '\d{1,2}',
                'h' => '\d{1,2}',
                'H' => '\d{1,2}',
                'i' => '\d{1,2}',
                's' => '\d{1,2}',
                'e' => '\w*',
                'I' => '(0|1)',
                'O' => '(\+|\-)\d{4}',
                'P' => '(\+|\-)\d{2}:\d{2}',
                'T' => '\w*',
                'Z' => '(\-|)\d{1,5}',
            ];
            $datetimeFormatPattern = str_replace(array_keys($repl), array_values($repl), $datetimeFormatPattern);

            $tmpVal = $dataToMap;
            if (preg_match($validPattern, $tmpVal)) {
                $dataToMap = new \DateTime($tmpVal);
            } elseif ($datetimeFormatPattern != '' && preg_match('~' . $datetimeFormatPattern . '~', $tmpVal)) {
                $dataToMap = new \DateTime($tmpVal);
            } else {
                $casted = intval($tmpVal);
                if (is_numeric($tmpVal) || ($casted == $tmpVal && strlen($casted) == strlen($tmpVal))) {
                    $dataToMap = new \DateTime();
                    $dataToMap->setTimestamp($tmpVal);
                }
            }
        }

        if (!$checkMethod[$type]($dataToMap)) {
            throw new TypeMismatchException($type, gettype($dataToMap), $propertyName);
        }

        if (in_array($type, ['int', 'integer'])) {
            $dataToMap = (int)$dataToMap;
        } elseif (in_array($type, ['float', 'double', 'real'])) {
            $dataToMap = (float)$dataToMap;
        } elseif (in_array($type, ['bool', 'boolean'])) {
            $dataToMap = (bool)$dataToMap;
        } elseif (in_array($type, ['array'])) {
            $dataToMap = (array)$dataToMap;
        } elseif (in_array($type, ['string'])) {
            $dataToMap = (string)$dataToMap;
        } elseif (in_array($type, ['object'])) {
            $tmpVal = $dataToMap;
            if (is_array($tmpVal) && array_keys($tmpVal) != range(0, count($tmpVal))) {
                $dataToMap = (object)$tmpVal;
            }
            if (!is_object($dataToMap)) {
                throw new TypeMismatchException($type, gettype($dataToMap), $propertyName);
            }
            $dataToMap = (object)$dataToMap;
        } elseif (in_array($type, ['date', 'datetime'])) {
            if ($fromRaw) {
                if (strtolower($type) == 'date') {
                    $dataToMap->setTime(0, 0, 0);
                }
            } else {

                /** @var \DateTime $dataToMap */
                if (strtolower($datetimeFormat) !== 'timestamp') {
                    $dataToMap = $dataToMap->format($datetimeFormat);
                } else {
                    $dataToMap = $dataToMap->getTimestamp();
                }
            }
        }
        return $dataToMap;
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param SerializerInterface $serializer
     * @return ObjectMapper
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Sets a property of an object
     *
     * @param object $object The object
     * @param \ReflectionProperty $property The Property
     * @param mixed $value The new value
     */
    protected function setPropertyValue($object, \ReflectionProperty $property, $value)
    {
        if ($value !== null) {
            // If the property is public accessible, set the value directly
            if ($property->isPublic()) {
                $object->{$property->name} = $value;
            } else {
                // If not, use the setter / adder
                $ucw = ucwords($property->name);
                if ($property->getDeclaringClass()->hasMethod($method = 'set' . $ucw)) {
                    $object->$method($value);
                } elseif ($property->getDeclaringClass()->hasMethod($method = 'add' . $ucw)) {
                    $object->$method($value);
                }
            }
        }
    }
}
