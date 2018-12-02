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

use MintWare\DMM\Serializer\PropertyHolder;
use MintWare\DMM\Serializer\SerializerInterface;

class DummySerializer implements SerializerInterface
{
    /** @inheritdoc */
    function deserialize($data)
    {
        return unserialize($data);
    }

    /** @inheritdoc */
    function serialize($data)
    {
        $rawData = $this->removeMetaDataEntries($data);
        return serialize($rawData);
    }

    public function removeMetaDataEntries($data)
    {
        $res = [];
        foreach ($data as $k => $v) {
            if ($v instanceof PropertyHolder) {
                if (is_array($v->value) || is_object($v->value)) {
                    $res[$k] = $this->removeMetaDataEntries($v->value);
                } else {
                    $res[$k] = $v->value;
                }
            } elseif (is_array($v) || is_object($v)) {
                $res[$k] = $this->removeMetaDataEntries($v);
            } else {
                $res[$k] = $v;
            }
        }
        return $res;
    }
}