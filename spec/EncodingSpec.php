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

use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class EncodingSpec extends ObjectBehavior
{
    public function it_converts_serialized_state_to_block_array()
    {
        $rawState = json_decode('{"entityMap":{},"blocks":[{"key":"33nh8","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[]}]}', true);

        $blocks = $this::convertFromRaw($rawState);
        $blocks->shouldHaveCount(1);

        $block = $blocks[0];

        $block->shouldBeAnInstanceOf('Draft\Model\Immutable\ContentBlock');
        $block->getKey()->shouldReturn('33nh8');
        $block->getText()->shouldReturn('a');
        $block->getType()->shouldReturn('unstyled');
        $block->getDepth()->shouldReturn(0);

        $characterList = $block->getCharacterList();
        $characterList->shouldHaveCount(1);

        $characterMetadata = $characterList[0];
        $characterMetadata->shouldBeAnInstanceOf('Draft\Model\Immutable\CharacterMetadata');
        $characterMetadata->getStyle()->shouldReturn([]);
        $characterMetadata->getEntity()->shouldReturn(null);
    }

    public function it_converts_serialized_state_with_entities_to_block_array()
    {
        $rawState = json_decode('{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/","rel":null,"title":"hi","extra":"foo"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}', true);

        $blocks = $this::convertFromRaw($rawState);
        $blocks->shouldHaveCount(1);

        $block = $blocks[0];

        $block->shouldBeAnInstanceOf('Draft\Model\Immutable\ContentBlock');
        $block->getKey()->shouldReturn('8r91j');
        $block->getText()->shouldReturn('a');
        $block->getType()->shouldReturn('unstyled');
        $block->getDepth()->shouldReturn(0);

        $characterList = $block->getCharacterList();
        $characterList->shouldHaveCount(1);

        $characterMetadata = $characterList[0];
        $characterMetadata->shouldBeAnInstanceOf('Draft\Model\Immutable\CharacterMetadata');
        $characterMetadata->getStyle()->shouldReturn(['ITALIC']);
        $characterMetadata->getEntity()->shouldReturn('1');
    }

    public function it_creates_character_list()
    {
        $inlineStyles = [['ITALIC']];
        $entities = [0];

        $characterList = $this::createCharacterList($inlineStyles, $entities);
        $characterList->shouldHaveCount(1);

        $characterMetadata = $characterList[0];
        $characterMetadata->shouldBeAnInstanceOf('Draft\Model\Immutable\CharacterMetadata');
        $characterMetadata->getStyle()->shouldReturn(['ITALIC']);
        $characterMetadata->getEntity()->shouldReturn(0);
    }

    public function it_decodes_entity_ranges()
    {
        $entityRanges = $this::decodeEntityRanges(str_repeat(' ', 20));
        $entityRanges->shouldHaveCount(20);
        $entityRanges->shouldContain(null);
    }

    public function it_decodes_when_multiple_entities_are_present()
    {
        $entityRanges = $this::decodeEntityRanges(str_repeat(' ', 8), [
            ['offset' => 2, 'length' => 2, 'key' => '6'],
            ['offset' => 5, 'length' => 2, 'key' => '8'],
        ]);

        $entityRanges->shouldHaveCount(8);
        $entityRanges->shouldReturn([null, null, '6', '6', null, '8', '8', null]);
    }

    public function it_decodes_when_entity_is_present_more_than_once()
    {
        $entityRanges = $this::decodeEntityRanges(str_repeat(' ', 8), [
            ['offset' => 2, 'length' => 2, 'key' => '6'],
            ['offset' => 5, 'length' => 2, 'key' => '6'],
        ]);

        $entityRanges->shouldHaveCount(8);
        $entityRanges->shouldReturn([null, null, '6', '6', null, '6', '6', null]);
    }

    public function it_decodes_inline_style_ranges()
    {
        $inlineStyleRanges = $this::decodeInlineStyleRanges('a');
        $inlineStyleRanges->shouldHaveCount(1);

        $inlineStyleRange = $inlineStyleRanges[0];
        $inlineStyleRange->shouldHaveCount(0);
    }

    public function it_decodes_inline_style_ranges_with_ranges()
    {
        $inlineStyleRanges = $this::decodeInlineStyleRanges('a', [['offset' => 0, 'length' => 1, 'style' => 'ITALIC']]);
        $inlineStyleRanges->shouldHaveCount(1);

        $inlineStyleRange = $inlineStyleRanges[0];
        $inlineStyleRange->shouldHaveCount(1);
        $inlineStyleRange->shouldContain('ITALIC');
    }
}
