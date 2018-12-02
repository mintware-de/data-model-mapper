<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\DMM\Model;

use MintWare\DMM\DataField;

/**
 * A simple dataholder for tests
 *
 * @package MintWare\Tests
 */
class FailPerson
{
    /** @DataField(name="foo", type="integer") */
    protected $name;
}
