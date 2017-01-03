<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft\Model\Entity;

use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class DraftEntitySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('LINK', 'MUTABLE');
        $this->shouldHaveType('Draft\Model\Entity\DraftEntity');

        $this->getType()->shouldReturn('LINK');
        $this->getMutability()->shouldReturn('MUTABLE');
        $this->getData()->shouldReturn(null);
    }

    public function it_is_initializable_with_custom_data()
    {
        $data = new \stdClass();

        $this->beConstructedWith('LINK', 'MUTABLE', $data);
        $this->shouldHaveType('Draft\Model\Entity\DraftEntity');

        $this->getType()->shouldReturn('LINK');
        $this->getMutability()->shouldReturn('MUTABLE');
        $this->getData()->shouldReturn($data);
    }

    public function it_creates_a_new_draft_entity_instance()
    {
        $this->beConstructedWith('LINK', 'MUTABLE');

        $key = $this::create('LINK', 'MUTABLE');
        $key->shouldBeString();

        $instance = $this::get($key);

        $instance->shouldBeAnInstanceOf('Draft\Model\Entity\DraftEntity');
        $instance->getType()->shouldReturn('LINK');
        $instance->getMutability()->shouldReturn('MUTABLE');
    }

    public function it_adds_a_draft_entity_instance()
    {
        $this->beConstructedWith('LINK', 'MUTABLE');

        $key = $this::add($this);
        $key->shouldBeString();

        $this::get($key)->shouldReturn($this);
    }
}
