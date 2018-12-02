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
use MintWare\DMM\DateTimeField;
use MintWare\Tests\DMM\UnknownAnnotation;

class Person
{
    /** @DataField(type="int") */
    public $age;

    /** @DataField(type="float") */
    public $height;

    /** @DataField(name="is_cool", type="bool") */
    public $isCool;

    /** @DataField(type="array") */
    public $nicknames;

    /** @DataField(name="motto", transformer="MintWare\Tests\DMM\Transformer\StrRotTransformer") */
    public $motto;

    /** @DataField() */
    public $note;

    /** @DataField(name="first_name", type="string") */
    public $firstName;

    /** @DataField(type="object") */
    public $dictionary;

    /** @DateTimeField(type="datetime", format="d.m.Y H:i:s") */
    public $created;

    /** @DateTimeField(type="datetime", format="Y-m-d H:i:s") */
    public $updated;

    /** @DateTimeField(type="date", format="timestamp") */
    public $deleted;

    /** @DataField(name="first_name", type="string", preTransformer="MintWare\Tests\DMM\Transformer\StrRevTransformer") */
    public $reversedFirstName;

    /** @DataField(type="int|string") */
    public $aNumber;

    /** @DataField(type="int", preTransformer="MintWare\Tests\DMM\Transformer\MultiplyTransformer") */
    public $anotherNumber;

    /** @DataField(type="int", postTransformer="MintWare\Tests\DMM\Transformer\MultiplyTransformer") */
    public $aLastNumber;

    /** @DataField(name="favorite_movies", type="MintWare\Tests\DMM\Model\Movie[]") */
    public $favouriteMovies;

    /**
     * @var Movie
     * @DataField(name="last_seen_movies", type="MintWare\Tests\DMM\Model\Movie")
     */
    public $lastSeenMovie;

    /** @UnknownAnnotation */
    public $nonMappedField;
}