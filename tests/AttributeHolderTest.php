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

use MintWare\DMM\AttributeHolder;
use PHPUnit\Framework\TestCase;

class AttributeHolderTest extends TestCase
{
    public function testConstruct()
    {
        $holder = new AttributeHolder('props');
        $this->assertEquals('props', $holder->value);
    }
}
