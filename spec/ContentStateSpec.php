<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft;

use Draft\ContentBlock;
use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class ContentStateSpec extends ObjectBehavior
{
    public function it_can_be_created_from_block_array(ContentBlock $block)
    {
        $block->getKey()->willReturn('123')->shouldBeCalled();

        $this->beConstructedThrough('createFromBlockArray', [[$block]]);
        $this->shouldHaveType('Draft\ContentState');

        $this->getBlockMap()->shouldHaveCount(1);
        $this->getBlockMap()->shouldHaveKeyWithValue('123', $block);
    }

    public function it_can_be_created_from_text()
    {
        $this->beConstructedThrough('createFromText', ['Hello, World!']);
        $this->shouldHaveType('Draft\ContentState');

        $this->getBlockMap()->shouldHaveCount(1);
    }

    public function it_can_be_created_from_multiline_text()
    {
        $this->beConstructedThrough('createFromText', ["Hello\r\nWorld!"]);
        $this->shouldHaveType('Draft\ContentState');

        $this->getBlockMap()->shouldHaveCount(2);
    }

    public function it_can_be_created_from_multiline_text_with_custom_delimiter()
    {
        $this->beConstructedThrough('createFromText', ["Hello\tWorld!", '/\t/']);
        $this->shouldHaveType('Draft\ContentState');

        $this->getBlockMap()->shouldHaveCount(2);
    }

    public function it_returns_block_map_as_array(ContentBlock $block)
    {
        $this->beConstructedWith([$block]);

        $this->getBlocksAsArray()->shouldHaveCount(1);
        $this->getBlocksAsArray()->shouldContain($block);
    }

    public function it_returns_first_block_from_map()
    {
        $this->beConstructedThrough('createFromText', ["A\r\nB\r\nC"]);

        $block = $this->getFirstBlock();
        $block->shouldHaveType('Draft\ContentBlock');
        $block->getText()->shouldReturn('A');
    }

    public function it_returns_last_block_from_map()
    {
        $this->beConstructedThrough('createFromText', ["A\r\nB\r\nC"]);

        $block = $this->getLastBlock();
        $block->shouldHaveType('Draft\ContentBlock');
        $block->getText()->shouldReturn('C');
    }

    public function it_returns_plain_text_from_blocks()
    {
        $this->beConstructedThrough('createFromText', ["Hello\r\nWorld!"]);

        $this->getPlainText()->shouldReturn("Hello\nWorld!");
    }

    public function it_returns_plain_text_from_blocks_with_custom_delimiter()
    {
        $this->beConstructedThrough('createFromText', ["Hello\r\nWorld!"]);

        $this->getPlainText('|')->shouldReturn('Hello|World!');
    }

    public function it_should_have_text_if_block_has_text(ContentBlock $block)
    {
        $block->getText()->willReturn('text');

        $this->beConstructedWith([$block]);

        $this->shouldHaveText();
    }

    public function it_should_not_have_text_if_block_has_empty_text(ContentBlock $block)
    {
        $block->getText()->willReturn('');

        $this->beConstructedWith([$block]);

        $this->shouldNotHaveText();
    }

    public function it_should_not_have_text_if_state_has_no_blocks()
    {
        $this->beConstructedWith([]);

        $this->shouldNotHaveText();
    }
}
