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

interface SerializerInterface
{
    /**
     * @param $data
     * @return array The deserialized data (Can be an [key => value] array, an [key => DeserializedField] array or mixed)
     * @throws \Exception
     */
    function deserialize($data);

    function serialize($data);
}