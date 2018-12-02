<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\DMM\Serializer;

use MintWare\DMM\Serializer\DeserializedField;
use PHPUnit\Framework\TestCase;

class DeserializedFieldTest extends TestCase
{
    public function testToPlain()
    {
        $field = new DeserializedField('Hello');
        $this->assertEquals('Hello', $field->toPlain());
    }

    public function testToPlainNested()
    {
        $field = new DeserializedField(new DeserializedField('Hello'));
        $this->assertEquals('Hello', $field->toPlain());
    }

    public function testToPlainNestedAdvanced()
    {
        $field = new DeserializedField([
            'foo' => new DeserializedField('bar')
        ]);
        $this->assertEquals(['foo' => 'bar'], $field->toPlain());
    }

    public function testToPlainNestedAndMixedAdvanced()
    {
        $field = new DeserializedField([
            'foo' => new DeserializedField('bar'),
            'baz' => ['foo' => new DeserializedField('Hello'), 'bar' => 'bar']
        ]);
        $this->assertEquals(['foo' => 'bar', 'baz' => ['foo' => 'Hello', 'bar' => 'bar']], $field->toPlain());
    }
}
