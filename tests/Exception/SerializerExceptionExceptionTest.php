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

use MintWare\DMM\Exception\ClassNotFoundException;
use MintWare\DMM\Exception\SerializerException;
use PHPUnit\Framework\TestCase;

class SerializerExceptionTest extends TestCase
{
    public function testInheritance()
    {
        $this->assertInstanceOf(\Exception::class, new SerializerException(""));
    }

    /** @throws ClassNotFoundException */
    public function testException()
    {
        $this->expectException(ClassNotFoundException::class);
        $this->expectExceptionMessage('The class Pizza was not found.');
        $this->expectExceptionCode(1234);
        throw new ClassNotFoundException("Pizza", 1234);
    }
}
