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

use MintWare\DMM\AttributeHolder;

class DeserializedField
{
    /**
     * This value will be filled in the model property with the @AttributeHolder annotation
     * @var mixed
     */
    public $attributes;

    /** @var mixed value of the property can also be an instance of DeserializedProperty */
    public $value;

    public function __construct($value = null, $attributes = null)
    {
        $this->value = $value;
        $this->attributes = $attributes;
    }

    public function toPlain($entry = null)
    {
        if ($entry == null) {
            $entry = $this->value;
        }

        if (is_array($entry)) {
            $res = [];
            foreach ($entry as $k => $item) {
                if (is_array($item)) {
                    $res[$k] = $this->toPlain($item);
                } elseif ($item instanceof DeserializedField) {
                    $res[$k] = $item->toPlain();
                } else {
                    $res[$k] = $item;
                }
            }
            return $res;
        } elseif ($entry instanceof DeserializedField) {
            return $entry->toPlain();
        } else {
            return $entry;
        }
    }
}
