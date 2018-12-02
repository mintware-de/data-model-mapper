<?php
/**
 * This file is part of the Data Model Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\Tests\DMM\Transformer;

use MintWare\DMM\TransformerInterface;

class StrRevTransformer implements TransformerInterface
{

    /** @inheritdoc */
    public static function transform($data)
    {
        return strrev($data);
    }

    /** @inheritdoc */
    public static function reverseTransform($data)
    {
        return strrev($data);
    }
}