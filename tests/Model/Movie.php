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

class Movie
{
    /** @DataField(name="title") */
    public $title;

    /** @DataField() */
    public $year;

    /** @DataField(name="title", postTransformer="MintWare\Tests\DMM\Transformer\StrRevTransformer") */
    public $reversedTitle;

    /** @DataField() */
    private $director;

    /** @DataField() */
    private $cast;

    public function __construct($title = "", $year = null)
    {
        $this->title = $title;
        $this->year = $year;
    }

    public function getDirector()
    {
        return $this->director;
    }

    public function setDirector($director)
    {
        $this->director = $director;
    }


    public function addCast($cast)
    {
        $this->cast = $cast;
    }


    public function getCast()
    {
        return $this->cast;
    }
}