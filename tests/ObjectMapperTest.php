<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\DMM;

use MintWare\DMM\Exception\ClassNotFoundException;
use MintWare\DMM\Exception\PropertyNotAccessibleException;
use MintWare\DMM\Exception\SerializerException;
use MintWare\DMM\Exception\TypeMismatchException;
use MintWare\DMM\ObjectMapper;
use MintWare\Tests\DMM\Model\FailPerson;
use MintWare\Tests\DMM\Model\Movie;
use MintWare\Tests\DMM\Model\Person;
use MintWare\Tests\DMM\Serializer\DummySerializer;
use PHPUnit\Framework\TestCase;

class ObjectMapperTest extends TestCase
{
    public function testConstruct()
    {
        $mapper = $this->getObjectMapper();
        $this->assertTrue($mapper instanceof ObjectMapper);
    }

    public function testGetSetSerializer()
    {
        $mapper = $this->getObjectMapper();
        $this->assertNull($mapper->getSerializer());

        $serializer = new DummySerializer();
        $mapper->setSerializer($serializer);
        $this->assertSame($serializer, $mapper->getSerializer());
    }

    public function testMapDataToObjectFailsClassNotFound()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('The class Foo\Bar was not found.');
        $mapper->mapDataToObject([], 'Foo\\Bar');
    }

    public function testMapDataToObjectFailsPropertyNotAccessible()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(PropertyNotAccessibleException::class);
        $this->expectExceptionMessage('Neither the property "name" nor one of the methods "setName", "addName" (or getter) have public access.');
        $mapper->mapDataToObject(['foo' => 1], FailPerson::class);
    }

    public function testMapDataToObjectFailsTypeMismatchInteger()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected int got boolean. Property name: age');
        $mapper->mapDataToObject(['age' => false], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchFloat()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected float got integer. Property name: height');
        $mapper->mapDataToObject(['height' => 1], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchBoolean()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected bool got string. Property name: is_cool');
        $mapper->mapDataToObject(['is_cool' => 'red'], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchArray()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected array got object. Property name: nicknames');
        $mapper->mapDataToObject(['nicknames' => (object)[]], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchString()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected string got array. Property name: first_name');
        $mapper->mapDataToObject(['first_name' => []], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchObject()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected object got boolean. Property name: dictionary');
        $mapper->mapDataToObject(['dictionary' => false], Person::class);
    }

    public function testMapDataToObjectFailsTypeMismatchDatetime()
    {
        $mapper = $this->getObjectMapper();
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected datetime got string. Property name: created');
        $mapper->mapDataToObject(['created' => "Hello World"], Person::class);
    }

    public function testPreTransformer()
    {
        $mapper = $this->getObjectMapper();
        /** @var Person $person */
        $person = $mapper->mapDataToObject(['first_name' => "Hans"], Person::class);
        $this->assertEquals("Hans", $person->firstName);
        $this->assertEquals("snaH", $person->reversedFirstName);
    }

    public function testTransformer()
    {
        $mapper = $this->getObjectMapper();
        /** @var Person $person */
        $person = $mapper->mapDataToObject(['motto' => "Unxhan Zngngn"], Person::class);
        $this->assertEquals("Hakuna Matata", $person->motto);
    }

    public function testDirectMappingWithoutTypeCheck()
    {
        $mapper = $this->getObjectMapper();

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['note' => "Hello"], Person::class);
        $this->assertEquals("Hello", $person->note);

        $person = $mapper->mapDataToObject(['note' => ["test"]], Person::class);
        $this->assertEquals(["test"], $person->note);
    }

    public function testMapWithMultipleTypes()
    {
        $mapper = $this->getObjectMapper();

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['aNumber' => "33"], Person::class);
        $this->assertSame("33", $person->aNumber);

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['aNumber' => 44], Person::class);
        $this->assertSame(44, $person->aNumber);
    }

    public function testMapNestedObject()
    {
        $mapper = $this->getObjectMapper();

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['favorite_movies' => [["title" => "Matrix", "year" => 1999]]], Person::class);
        $this->assertEquals($person->favouriteMovies[0]->title, "Matrix");
        $this->assertEquals($person->favouriteMovies[0]->year, 1999);

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['last_seen_movies' => ["title" => "Back to the Future", "year" => 1985]], Person::class);
        $this->assertEquals($person->lastSeenMovie->title, "Back to the Future");
        $this->assertEquals($person->lastSeenMovie->year, 1985);
    }

    public function testPostTransformer()
    {
        $mapper = $this->getObjectMapper();

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['last_seen_movies' => ["title" => "Back to the Future", "year" => 1985]], Person::class);
        $this->assertEquals($person->lastSeenMovie->reversedTitle, "erutuF eht ot kcaB");
    }

    public function testSetPrivateField()
    {
        $mapper = $this->getObjectMapper();

        /** @var Person $person */
        $person = $mapper->mapDataToObject(['last_seen_movies' => ["title" => "Back to the Future", "year" => 1985, 'director' => 'Robert Zemeckis', 'cast' => ['Michael J. Fox', 'Christopher Lloyd', 'Claudia Wells']]], Person::class);
        $this->assertEquals($person->lastSeenMovie->getDirector(), "Robert Zemeckis");
        $this->assertEquals($person->lastSeenMovie->getCast(), ['Michael J. Fox', 'Christopher Lloyd', 'Claudia Wells']);
    }

    public function testSerializeFails()
    {
        $mapper = $this->getObjectMapper();

        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage("You've to specify a serializer with the setSerializer() method.");
        $mapper->serialize(new \stdClass());
    }

    public function testSerializeFailsNotAccessibleProperty()
    {
        $mapper = $this->getObjectMapper(true);

        $this->expectException(PropertyNotAccessibleException::class);
        $this->expectExceptionMessage("Neither the property \"name\" nor one of the methods \"setName\", \"addName\" (or getter) have public access.");
        $mapper->serialize(new FailPerson());
    }

    public function testSerializeFailsNot()
    {
        $mapper = $this->getObjectMapper();
        $res = $mapper->serialize(new \stdClass(), false);
        $this->assertEmpty($res);
    }

    public function testSerialize()
    {
        $mapper = $this->getObjectMapper(true);
        $person = $this->createPerson();

        $serialized = $mapper->serialize($person);
        $this->assertContains('02.12.2018 00:00:00', $serialized);
        $this->assertContains('2018-12-01 00:00:00', $serialized);
        $person2 = $mapper->map($serialized, Person::class);

        $this->assertEquals($person->motto, $person2->motto);
        $this->assertEquals($person->firstName, $person2->firstName);
        $this->assertEquals($person->age, $person2->age);
        $this->assertEquals($person->aNumber, $person2->aNumber);
        $this->assertEquals($person->dictionary, $person2->dictionary);
        $this->assertEquals($person->created, $person2->created);
        $this->assertEquals($person->updated, $person2->updated);
        $this->assertEquals($person->deleted, $person2->deleted);
        $this->assertEquals($person->height, $person2->height);
        $this->assertEquals($person->isCool, $person2->isCool);
        $this->assertEquals($person->nicknames, $person2->nicknames);
        $this->assertEquals($person->favouriteMovies, $person2->favouriteMovies);
        $this->assertEquals($person->lastSeenMovie, $person2->lastSeenMovie);
        $this->assertEquals($person->anotherNumber, $person2->anotherNumber);
        $this->assertEquals($person->aLastNumber, $person2->aLastNumber);
        $this->assertNull($person2->nonMappedField);
    }

    public function testMapFails()
    {
        $mapper = $this->getObjectMapper();

        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage("You've to specify a serializer with the setSerializer() method.");
        $mapper->map('', \stdClass::class);
    }

    public function testMapArrayOfPrimitives()
    {
        $mapper = $this->getObjectMapper(true);
        $res = $mapper->map('a:2:{i:0;O:8:"stdClass":0:{}i:1;O:8:"stdClass":0:{}}', '\\stdClass[]');
        $this->assertEquals([new \stdClass(), new \stdClass()], $res);

    }

    /**
     * @return ObjectMapper
     */
    protected function getObjectMapper($setSerializer = false)
    {
        try {
            $mapper = new ObjectMapper();
            if ($setSerializer) {
                $mapper->setSerializer(new DummySerializer());
            }
            return $mapper;
        } catch (\Exception $e) {
        }
        return null;
    }

    protected function createPerson()
    {
        $person = new Person();
        $person->motto = 'Hakuna Matata';
        $person->favouriteMovies = [new Movie('Matrix', 1999), new Movie('Back to the Future', 1985)];
        $person->favouriteMovies[0]->reversedTitle = 'xirtaM';
        $person->favouriteMovies[1]->reversedTitle = 'erutuF eht ot kcaB';
        $person->lastSeenMovie = new Movie("Back to the Future", 1985);
        $person->lastSeenMovie->reversedTitle = 'erutuF eht ot kcaB';
        $person->firstName = 'Hans';
        $person->age = 68;
        $person->aNumber = 44;
        $person->nonMappedField = 'Pizza is tasty';
        $person->dictionary = (object)['foo' => 'bar', 'baz' => ['foo' => 'bar']];
        $person->created = new \DateTime('2018-12-02 00:00:00');
        $person->updated = new \DateTime('2018-12-01 00:00:00');
        $person->deleted = new \DateTime('2019-12-05 00:00:00');
        $person->height = 1.78;
        $person->isCool = true;
        $person->anotherNumber = 40;
        $person->aLastNumber = 80;
        $person->nicknames = ['Devtronic'];
        return $person;
    }
}
