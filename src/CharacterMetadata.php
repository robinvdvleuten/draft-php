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

class CharacterMetadata
{
    /**
     * @var array
     */
    private $style;

    /**
     * @var string
     */
    private $entity;

    /**
     * Constructor.
     *
     * @param array  $style
     * @param string $entity
     */
    public function __construct(array $style = [], $entity  = null)
    {
        $this->style = $style;
        $this->entity = $entity;
    }

    /**
     * @return array
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
