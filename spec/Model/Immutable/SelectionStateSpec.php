<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft\Model\Immutable;

use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class SelectionStateSpec extends ObjectBehavior
{
    public function it_can_be_created_with_empty_data()
    {
        $this->beConstructedThrough('createEmpty', ['123']);
        $this->shouldHaveType('Draft\Model\Immutable\SelectionState');

        $this->getAnchorKey()->shouldReturn('123');
    }
}
