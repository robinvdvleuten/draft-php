<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft\Util;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
final class Keys
{
    /**
     * @var int
     */
    private static $multiplier;

    /**
     * @var array
     */
    private static $seenKeys;

    /**
     * @return string
     */
    public static function generateRandomKey()
    {
        if (!self::$seenKeys) {
            self::$seenKeys = [];
        }

        if (!self::$multiplier) {
            self::$multiplier = pow(2, 24);
        }

        $key = null;

        while ($key === null || isset(self::$seenKeys[$key])) {
            $key = base_convert(floor(((float) mt_rand() / (float) mt_getrandmax()) * self::$multiplier), 10, 32);
        }

        self::$seenKeys[$key] = true;

        return $key;
    }
}
