# Data Model Mapper Documentation

## Examples
1. [Simple Mapping](./1_simple_mapping.md)
2. [Mapping with Typehints](./2_mapping_with_typehints.md)
3. [Mapping Alternative Property Names](./3_mapping_alternative_property_names.md)
4. [Using Transformers](./4_using_transformers.md)
5. [Convert an Mapped Object to JSON](./5_convert_an_object_to_json.md)
6. [Multiple Annotations](./6_multiple_annotations.md)

## Basic usage
Since [JOM](https://github.com/mintware-de/json-object-mapper) was especially created for JSON handling,
DMM is more flexible and uses replaceable serializer packages.

In this documentation I use the [mintware-de/dmm-json](https://github.com/mintware-de/dmm-json) package for JSON handling.
(Check the README for installation steps).

## Example
Dataset:
```json
{
    "first_name": "Pete",
    "surname": "Peterson",
    "age": 28,
    "address": {
        "street": "Mainstreet 22a",
        "zip_code": "A-12345",
        "town": "Best Town"
    }
}
```

Data Class:
```php
<?php

use MintWare\DMM\DataField;

class Person
{
    /** @DataField(name="first_name", type="string") */
    public $firstName;
    
    /** @DataField(name="surname", type="string") */
    public $lastname;
    
    /** @DataField() */
    public $age;
    
    /** @DataField(type="Some\Other\DataClass\Address") */
    public $address;
}
```

Map the JSON:
```php
<?php

use Mintware\DMM\ObjectMapper;
use MintWare\DMM\Serializer\JsonSerializer;

$mapper = new ObjectMapper(new JsonSerializer());
$person = $mapper->map(file_get_contents('person.json'), Person::class);
```