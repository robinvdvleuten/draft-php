<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft\Model\Entity;

use Draft\Exception\DraftException;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class DraftEntity
{
    const MUTABILITY_MUTABLE = 'MUTABLE';
    const MUTABILITY_IMMUTABLE = 'IMMUTABLE';
    const MUTABILITY_SEGMENTED = 'SEGMENTED';

    const VALID_MUTABILITY = [
        self::MUTABILITY_MUTABLE,
        self::MUTABILITY_IMMUTABLE,
        self::MUTABILITY_SEGMENTED
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $mutability;

    /**
     * @var mixed
     */
    private $data;

    /**
     * DraftEntity constructor.
     *
     * @param $type
     * @param $mutability
     * @param null $data
     *
     * @throws DraftException
     */
    public function __construct($type, $mutability, $data = null)
    {
        if (!in_array($mutability, self::VALID_MUTABILITY)) {
            throw new DraftException('Invalid mutability for entity.');
        }

        $this->type = $type;
        $this->mutability = $mutability;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMutability()
    {
        return $this->mutability;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
