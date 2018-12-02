<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\DMM\Exception;

use MintWare\DMM\Exception\PropertyNotAccessibleException;
use PHPUnit\Framework\TestCase;

class PropertyNotAccessibleExceptionTest extends TestCase
{
    public function testInheritance()
    {
        $this->assertInstanceOf(\Exception::class, new PropertyNotAccessibleException(""));
    }
}
