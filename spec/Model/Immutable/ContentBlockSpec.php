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
class ContentBlockSpec extends ObjectBehavior
{
    public function it_is_initializable_with_defaults()
    {
        $this->beConstructedWith('123', 'unstyled');

        $this->shouldHaveType('Draft\Model\Immutable\ContentBlock');

        $this->getKey()->shouldReturn('123');
        $this->getType()->shouldReturn('unstyled');
        $this->getText()->shouldReturn('');
        $this->getCharacterList()->shouldReturn([]);
        $this->getDepth()->shouldReturn(0);
    }

    public function it_is_initializable_with_custom_values()
    {
        $this->beConstructedWith('123', 'unstyled', 'Hello, Block!', ['a'], 2);

        $this->shouldHaveType('Draft\Model\Immutable\ContentBlock');

        $this->getKey()->shouldReturn('123');
        $this->getType()->shouldReturn('unstyled');
        $this->getText()->shouldReturn('Hello, Block!');
        $this->getCharacterList()->shouldReturn(['a']);
        $this->getDepth()->shouldReturn(2);
    }
}
