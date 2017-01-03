<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft\Util;

use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class KeysSpec extends ObjectBehavior
{
    public function it_generates_a_random_key()
    {
        $randomKey = $this::generateRandomKey()->shouldBeString();

        $this::generateRandomKey()->shouldNotReturn($randomKey);
    }
}
