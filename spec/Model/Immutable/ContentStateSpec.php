<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft\Model\Immutable;

use Draft\Exception\DraftException;
use Draft\Model\Immutable\CharacterMetadata;
use Draft\Model\Immutable\ContentBlock;
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
        $this->shouldHaveType('Draft\Model\Immutable\ContentState');

        $this->getBlockMap()->shouldHaveCount(1);
        $this->getBlockMap()->shouldHaveKeyWithValue('123', $block);
    }

    public function it_can_be_created_from_text()
    {
        $this->beConstructedThrough('createFromText', ['Hello, World!']);
        $this->shouldHaveType('Draft\Model\Immutable\ContentState');

        $this->getBlockMap()->shouldHaveCount(1);
    }

    public function it_can_be_created_from_multiline_text()
    {
        $this->beConstructedThrough('createFromText', ["Hello\r\nWorld!"]);
        $this->shouldHaveType('Draft\Model\Immutable\ContentState');

        $this->getBlockMap()->shouldHaveCount(2);
    }

    public function it_can_be_created_from_multiline_text_with_custom_delimiter()
    {
        $this->beConstructedThrough('createFromText', ["Hello\tWorld!", '/\t/']);
        $this->shouldHaveType('Draft\Model\Immutable\ContentState');

        $this->getBlockMap()->shouldHaveCount(2);
    }

    public function it_returns_block_map_as_array(ContentBlock $block)
    {
        $this->beConstructedWith([$block]);

        $this->getBlocksAsArray()->shouldHaveCount(1);
        $this->getBlocksAsArray()->shouldContain($block);
    }

    public function it_returns_block_for_key(ContentBlock $block)
    {
        $block->getKey()->willReturn('key');

        $this->beConstructedWith([$block]);

        $this->getBlockForKey('key')->shouldReturn($block);
        $this->getBlockForKey('unknown')->shouldReturn(null);
    }

    public function it_returns_first_block_from_map()
    {
        $this->beConstructedThrough('createFromText', ["A\r\nB\r\nC"]);

        $block = $this->getFirstBlock();
        $block->shouldHaveType('Draft\Model\Immutable\ContentBlock');
        $block->getText()->shouldReturn('A');
    }

    public function it_returns_last_block_from_map()
    {
        $this->beConstructedThrough('createFromText', ["A\r\nB\r\nC"]);

        $block = $this->getLastBlock();
        $block->shouldHaveType('Draft\Model\Immutable\ContentBlock');
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

    public function it_can_insert_and_remove_blocks()
    {
        $this->beConstructedThrough('createFromBlockArray', [
            [
                new ContentBlock('A', 'unstyled'),
                new ContentBlock('B', 'unstyled'),
                new ContentBlock('C', 'unstyled'),
                new ContentBlock('D', 'unstyled'),
                new ContentBlock('E', 'unstyled'),
                new ContentBlock('F', 'unstyled'),
            ],
        ]);

        // Insert X after C
        $this->insertContentBlock('C', new ContentBlock('X', 'unstyled'));
        $this->getBlockBefore('X')->getKey()->shouldReturn('C');
        $this->getBlockAfter('X')->getKey()->shouldReturn('D');

        // Insert Y after F (last block)
        $this->insertContentBlock('F', new ContentBlock('Y', 'unstyled'));
        $this->getBlockBefore('Y')->getKey()->shouldReturn('F');
        $this->getBlockAfter('Y')->shouldReturn(null);

        // Insert Z BEFORE A (first block)
        $this->insertContentBlock('A', new ContentBlock('Z', 'unstyled'), true);
        $this->getBlockBefore('Z')->shouldReturn(null);
        $this->getBlockAfter('Z')->getKey()->shouldReturn('A');

        // Remove X (middle block)
        $this->removeContentBlock('X');
        $this->getBlockAfter('C')->getKey()->shouldReturn('D');

        // Remove Y (last block)
        $this->removeContentBlock('Y');
        $this->getBlockAfter('F')->shouldReturn(null);

        // Remove Z (first block)
        $this->removeContentBlock('Z');
        $this->getBlockBefore('A')->shouldReturn(null);

        // Try to insert after not existing block key
        $this->shouldThrow(DraftException::class)->duringInsertContentBlock(
            '?',
            new ContentBlock('T', 'unstyled')
        );

        // Try to remove not existing block key
        $this->shouldThrow(DraftException::class)->duringRemoveContentBlock(
            '?'
        );
    }

    public function it_can_split_a_block()
    {
        // We use some emoji for very rudimentary unicode testing

        $e1 = "\xF0\x9F\x98\x83"; // 😃
        $e2 = "\xF0\x9F\x98\x82"; // 😂

        $text = "This is a${e1}${e2}great text!";
        $charList = [
            new CharacterMetadata(['UNDERLINE'], 0), // 'T' 0
            new CharacterMetadata(['UNDERLINE'], 0), // 'h' 1
            new CharacterMetadata(['UNDERLINE'], 0), // 'i' 2
            new CharacterMetadata(['UNDERLINE'], 0), // 's' 3
            new CharacterMetadata([], 0),            // ' ' 4
            new CharacterMetadata(['ITALIC'], 0),    // 'i' 5
            new CharacterMetadata(['ITALIC'], 0),    // 's' 6
            new CharacterMetadata([], 0),            // ' ' 7
            new CharacterMetadata(['ITALIC'], 0),    // 'a' 8
            new CharacterMetadata([], 0),            // ' ' 9 <- emoji 1
            new CharacterMetadata([], 0),            // ' ' 10 <- emoji 2 - targeted split point
            new CharacterMetadata(['BOLD'], 0),      // 'g' 11
            new CharacterMetadata(['BOLD'], 0),      // 'r' 12
            new CharacterMetadata(['BOLD'], 0),      // 'e' 13
            new CharacterMetadata(['BOLD'], 0),      // 'a' 14
            new CharacterMetadata(['BOLD'], 0),      // 'a' 15
            new CharacterMetadata([], 0),            // ' ' 16
            new CharacterMetadata([], 0),            // 't' 17
            new CharacterMetadata([], 0),            // 'e' 18
            new CharacterMetadata([''], 0),          // 'x' 19
            new CharacterMetadata([], 0),            // 't' 20
            new CharacterMetadata(['BOLD'], 0),      // '!' 21
        ];

        $contentBlockA = new ContentBlock('A', 'paragraph', '', [], 3);
        $contentBlockB = new ContentBlock(
            'B',
            'header-one',
            $text,
            $charList,
            3
        );
        $contentBlockC = new ContentBlock('C', 'unstyled');

        $this->beConstructedThrough('createFromBlockArray', [
            [
                $contentBlockA, // <- test to split an empty block
                $contentBlockB,
                $contentBlockC,
            ],
        ]);

        $this->__splitBlock('B', 10);

        $this->getBlockMap()->shouldHaveCount(4);

        // Test the splitted block
        $this->getBlockAfter('A')->shouldReturn($contentBlockB);
        $this->getBlockAfter('A')->getKey()->shouldReturn('B');

        $this->getBlockForKey('B')->getText()->shouldReturn("This is a${e1}");
        $this->getBlockForKey('B')->getCharacterList()->shouldReturn(array_slice($charList, 0, 10));
        $this->getBlockForKey('B')->getDepth()->shouldReturn(3);
        $this->getBlockForKey('B')->getType()->shouldReturn('header-one');

        // The newly created block (the key is however generated so nothing to test there)
        $this->getBlockAfter('B')->getText()->shouldReturn("${e2}great text!");
        $this->getBlockAfter('B')->getCharacterList()->shouldReturn(array_slice($charList, 10));
        $this->getBlockAfter('B')->getDepth()->shouldReturn(3);
        $this->getBlockAfter('B')->getType()->shouldReturn('header-one');

        // Try to split a not existing block
        $this->shouldThrow(DraftException::class)->during__splitBlock('?', 0);

        // Try to split on a not existing offset (content block "A" have 0 characters)
        $this->shouldThrow(DraftException::class)->during__splitBlock('A', -1);
        $this->shouldThrow(DraftException::class)->during__splitBlock('A', 1);
        $this->shouldThrow(DraftException::class)->during__splitBlock('A', 999);

        // Try to split an empty content block (this should work on offset 0)
        $this->__splitBlock('A', 0);
        $this->getBlockMap()->shouldHaveCount(5);

        // Empty block A
        $this->getBlockForKey('A')->shouldReturn($contentBlockA);
        $this->getBlockForKey('A')->getDepth()->shouldReturn(3);
        $this->getBlockForKey('A')->getType()->shouldReturn('paragraph');
        $this->getBlockForKey('A')->getText()->shouldReturn('');
        $this->getBlockForKey('A')->getCharacterList()->shouldReturn([]);

        // Created splitted block after A
        $this->getBlockAfter('A')->getDepth()->shouldReturn(3);
        $this->getBlockAfter('A')->getType()->shouldReturn('paragraph');
        $this->getBlockAfter('A')->getText()->shouldReturn('');
        $this->getBlockAfter('A')->getCharacterList()->shouldReturn([]);
        $this->getBlockBefore('B')->shouldReturn(
            $this->getBlockAfter('A')
        );

        // Quick test to split the last content block
        $this->__splitBlock('C', 0);
        $this->getBlockMap()->shouldHaveCount(6);
        $this->getBlockForKey('C')->shouldReturn($contentBlockC);
    }
}
