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

/**
 * This class represents the AttributeHolder Annotation
 *
 * @Annotation
 */
class AttributeHolder
{
    /** @var mixed */
    public $value;

    /**
     * AttributeHolder constructor.
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }
}
