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

use MintWare\DMM\Exception\TypeMismatchException;
use PHPUnit\Framework\TestCase;

class TypeMismatchTest extends TestCase
{
    public function testInheritance()
    {
        $this->assertInstanceOf(\Exception::class, new TypeMismatchException("", "", ""));
    }

    /** @throws TypeMismatchException */
    public function testException()
    {
        $this->expectException(TypeMismatchException::class);
        $this->expectExceptionMessage('Wrong Type. Expected pasta got pizza. Property name: dinner');
        $this->expectExceptionCode(0);
        throw new TypeMismatchException("pasta", "pizza", "dinner");
    }
}
