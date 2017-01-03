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

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class DraftEntity
{
    const MUTABILITY_MUTABLE = 'MUTABLE';

    const MUTABILITY_IMMUTABLE = 'IMMUTABLE';

    const MUTABILITY_SEGMENTED = 'SEGMENTED';

    /**
     * @var int
     */
    private static $instanceKey = 0;

    /**
     * @var array
     */
    private static $instances = [];

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
     * Constructor.
     *
     * @param string $type
     * @param string $mutability
     * @param mixed  $data
     */
    public function __construct($type, $mutability, $data = null)
    {
        $this->type = $type;
        $this->mutability = $mutability;
        $this->data = $data;
    }

    public static function create($type, $mutability, $data = null)
    {
        return self::add(new self($type, $mutability, $data));
    }

    public static function add(DraftEntity $instance)
    {
        $key = (string) ++self::$instanceKey;
        self::$instances[$key] = $instance;

        return $key;
    }

    public static function get($key)
    {
        if (!isset(self::$instances[$key])) {
            throw new \RuntimeException('Unknown DraftEntity key.');
        }

        return self::$instances[$key];
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
