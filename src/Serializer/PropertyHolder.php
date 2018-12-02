<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\DMM\Serializer;

use MintWare\DMM\DataField;

class PropertyHolder
{
    /**
     * Contains the Annotation Data
     * @var DataField
     */
    public $annotation = null;

    /**
     * Contains the property name of the PHP model
     * @var string
     */
    public $propertyName;

    /**
     * Contains the Value
     * @var mixed
     */
    public $value = null;

    public function __construct(DataField $metaData, $propertyName, $value)
    {
        $this->annotation = $metaData;
        $this->propertyName = $propertyName;
        $this->value = $value;
    }
}