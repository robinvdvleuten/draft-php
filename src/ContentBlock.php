<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class ContentBlock
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $text;

    /**
     * @var array
     */
    private $characterList;

    /**
     * @var int
     */
    private $depth;

    /**
     * Constructor.
     *
     * @param string $key
     * @param string $type
     * @param string $text
     * @param array  $characterList
     * @param int    $depth
     */
    public function __construct($key, $type, $text = '', array $characterList = [], $depth = 0)
    {
        $this->key = $key;
        $this->type = $type;
        $this->text = $text;
        $this->characterList = $characterList;
        $this->depth = $depth;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getCharacterList()
    {
        return $this->characterList;
    }

    public function getDepth()
    {
        return $this->depth;
    }
}
