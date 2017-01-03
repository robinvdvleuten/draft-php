<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft\Model\Immutable;

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
     * @var CharacterMetadata[]
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
     * @param CharacterMetadata[] $characterList
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

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return CharacterMetadata[]
     */
    public function getCharacterList()
    {
        return $this->characterList;
    }

    /**
     * @param CharacterMetadata[] $characterList
     */
    public function setCharacterList(array $characterList)
    {
        $this->characterList = $characterList;
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }
}
