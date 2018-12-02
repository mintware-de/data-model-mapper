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

use MintWare\DMM\DataField;
use MintWare\DMM\Serializer\PropertyHolder;
use PHPUnit\Framework\TestCase;

class PropertyHolderTest extends TestCase
{
    public function testConstructor()
    {
        $metaData = new DataField();
        $metaData->type = 'string';
        $name = 'dinner';
        $value = 'pizza';

        $metaDataValuePair = new PropertyHolder($metaData, $name, $value);

        $this->assertSame($metaData, $metaDataValuePair->annotation);
        $this->assertSame($name, $metaDataValuePair->propertyName);
        $this->assertSame($value, $metaDataValuePair->value);
    }
}
