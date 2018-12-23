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
     * Deserialize logic (raw-data -> object)
     *
     * @param string|mixed $data The raw data, in the most cases a string
     * @return array The deserialized data (Can be an [key => value] array, an [key => DeserializedField] array or mixed)
     * @throws \Exception
     */
    public function deserialize($data);

    /**
     * Serialize logic (object -> raw-data)
     *
     * @param PropertyHolder $data The
     * @return mixed The serialized data (in the most cases a string, e.g. json, xml etc)
     */
    public function serialize(PropertyHolder $data);
}
