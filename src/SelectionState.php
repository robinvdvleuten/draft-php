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
class SelectionState
{
    /**
     * @var string
     */
    private $anchorKey;

    /**
     * Constructor.
     *
     * @param string $anchorKey
     */
    public function __construct($anchorKey)
    {
        $this->anchorKey = $anchorKey;
    }

    /**
     * @param string $anchorKey
     *
     * @return self
     */
    public static function createEmpty($anchorKey)
    {
        return new self($anchorKey);
    }

    public function getAnchorKey()
    {
        return $this->anchorKey;
    }
}
