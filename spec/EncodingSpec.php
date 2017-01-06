<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft;

use Draft\Exception\DraftException;
use Draft\Exception\InvalidRawException;
use Draft\Model\Entity\DraftEntity;
use Draft\Model\Immutable\CharacterMetadata;
use Draft\Model\Immutable\ContentBlock;
use Draft\Model\Immutable\ContentState;
use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class EncodingSpec extends ObjectBehavior
{
    public function it_converts_content_state_to_raw()
    {
        $contentState = new ContentState([
            new ContentBlock('a', 'unstyled', 'This is a very proud test.', [
                new CharacterMetadata(['BOLD'], 0),                 // 1 T / ENTITY 0 / recognize character in first character
                new CharacterMetadata([], 0),                       // 2 h / ENTITY 0
                new CharacterMetadata([], 0),                       // 3 i / ENTITY 0
                new CharacterMetadata([], 0),                       // 4 s / ENTITY 0
                new CharacterMetadata([], null),                    // 5' '
                new CharacterMetadata(['BOLD'], null),              // 6 i
                new CharacterMetadata(['BOLD'], null),              // 7 s
                new CharacterMetadata([], null),                    // 8' '
                new CharacterMetadata(['BOLD'], null),              // 9 a
                new CharacterMetadata([], null),                    // 10' '
                new CharacterMetadata(['BOLD', 'ITALIC'], null),    // 11 v
                new CharacterMetadata(['BOLD', 'ITALIC'], null),    // 12 e
                new CharacterMetadata(['ITALIC', 'BOLD'], null),    // 13 r
                new CharacterMetadata(['ITALIC', 'BOLD'], null),    // 14 y
                new CharacterMetadata([], null),                    // 15' '
                new CharacterMetadata(['BOLD'], null),              // 16 p
                new CharacterMetadata(['BOLD', 'ITALIC'], null),    // 17 r
                new CharacterMetadata(['BOLD', 'ITALIC', 'UNDERLINE'], null), // 18 o
                new CharacterMetadata([], null),                    // 19 u
                new CharacterMetadata([], null),                    // 20 d
                new CharacterMetadata([], null),                    // 21' '
                new CharacterMetadata([], 0),                       // 22 t / ENTITY 0
                new CharacterMetadata([], 0),                       // 23 e / ENTITY 0
                new CharacterMetadata([], 0),                       // 24 s / ENTITY 0
                new CharacterMetadata([], 0),                       // 25 t / ENTITY 0
                new CharacterMetadata(['BOLD'], null), // 26 . - recognize style in last character
            ], 0),
            new ContentBlock('b', 'atomic', ' ', [
                new CharacterMetadata([], 1),                       // 25' ' / ENTITY 1
            ], 0),
        ]);

        $contentState->__setEntity(0, new DraftEntity('IMAGE', DraftEntity::MUTABILITY_IMMUTABLE, [
            'src' => 'http://google.de/image.png',
        ]));

        $contentState->__setEntity(1, new DraftEntity('LINK', DraftEntity::MUTABILITY_IMMUTABLE, [
            'url' => 'http://google.de',
        ]));

        $exceptedRawData = [
            'entityMap' => [
                0 => [
                    'type' => 'IMAGE',
                    'mutability' => 'IMMUTABLE',
                    'data' => [
                        'src' => 'http://google.de/image.png',
                    ],
                ],
                1 => [
                    'type' => 'LINK',
                    'mutability' => 'IMMUTABLE',
                    'data' => [
                        'url' => 'http://google.de',
                    ],
                ],
            ],
            'blocks' => [
                [
                    'key' => 'a',
                    'type' => 'unstyled',
                    'text' => 'This is a very proud test.',
                    'depth' => 0,
                    'inlineStyleRanges' => [
                        [
                            'offset' => 0,
                            'length' => 1,
                            'style' => 'BOLD',
                        ],
                        [
                            'offset' => 5,
                            'length' => 2,
                            'style' => 'BOLD',
                        ],
                        [
                            'offset' => 8,
                            'length' => 1,
                            'style' => 'BOLD',
                        ],
                        [
                            'offset' => 10,
                            'length' => 4,
                            'style' => 'BOLD',
                        ],
                        [
                            'offset' => 15,
                            'length' => 3,
                            'style' => 'BOLD',
                        ],
                        [
                            'offset' => 25,
                            'length' => 1,
                            'style' => 'BOLD',
                        ],
                        [
                            'offset' => 10,
                            'length' => 4,
                            'style' => 'ITALIC',
                        ],
                        [
                            'offset' => 16,
                            'length' => 2,
                            'style' => 'ITALIC',
                        ],
                        [
                            'offset' => 17,
                            'length' => 1,
                            'style' => 'UNDERLINE',
                        ],
                    ],
                    'entityRanges' => [
                        0 => [
                            'offset' => 0,
                            'length' => 4,
                            'key' => 0,
                        ],
                        1 => [
                            'offset' => 21,
                            'length' => 4,
                            'key' => 0,
                        ],
                    ],
                ],
                [
                    'key' => 'b',
                    'type' => 'atomic',
                    'text' => ' ',
                    'depth' => 0,
                    'inlineStyleRanges' => [],
                    'entityRanges' => [
                        0 => [
                            'offset' => 0,
                            'length' => 1,
                            'key' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $raw = $this::convertToRaw($contentState);

        $theRaw = json_encode($raw->getWrappedObject());
        $exceptedRaw = json_encode($exceptedRawData);

        if ($theRaw !== $exceptedRaw) {
            throw new \Exception('Comparison failed.');
        }
    }

    public function it_throws_exception_on_invalid_raw_if_block_is_string()
    {
        $contentStateRaw = [
            'entityMap' => [],
            'blocks' => '', // <---
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_mutability_not_exists()
    {
        $contentStateRaw = [
            'entityMap' => [
                [
                    'type' => 'LINK',
                    'mutability' => 'NOT_EXIST', // <---
                    'data' => [],
                ],
            ],
            'blocks' => [],
        ];

        $this::shouldThrow(DraftException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_entity_type_is_empty_string()
    {
        $contentStateRaw = [
            'entityMap' => [
                [
                    'type' => '', // <---
                    'mutability' => 'MUTABLE',
                    'data' => [],
                ],
            ],
            'blocks' => [],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_block_text_is_number()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => 'unstyled',
                    'text' => 123, // <---
                    'inlineStyleRanges' => [],
                    'entityRanges' => [],
                ],
            ],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_block_type_is_empty_string()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => '', // <---
                    'text' => 'my text...',
                    'inlineStyleRanges' => [],
                    'entityRanges' => [],
                ],
            ],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_inline_style_range_has_no_style_property()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => 'unstyled',
                    'text' => ' ',
                    'inlineStyleRanges' => [
                        [
                            'offset' => 0,
                            'length' => 1,
                            // no style // <---
                        ],
                    ],
                    'entityRanges' => [],
                ],
            ],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_inline_style_range_has_length_zero()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => 'unstyled',
                    'text' => ' ',
                    'inlineStyleRanges' => [
                        [
                            'offset' => 0,
                            'length' => 0, // <---
                            'style' => 'BOLD',
                        ],
                    ],
                    'entityRanges' => [],
                ],
            ],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_inline_style_range_has_offset_sub_zero()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => 'unstyled',
                    'text' => ' ',
                    'inlineStyleRanges' => [
                        [
                            'offset' => -1, // <---
                            'length' => 1,
                            'style' => 'BOLD',
                        ],
                    ],
                    'entityRanges' => [],
                ],
            ],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_entity_range_has_key_of_type_string()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => 'unstyled',
                    'text' => ' ',
                    'inlineStyleRanges' => [],
                    'entityRanges' => [
                        [
                            'offset' => -1,
                            'length' => 1,
                            'key' => 'abc', // <---
                        ],
                    ],
                ],
            ],
            'entityMap' => [
                1 => [
                    'type' => 'LINK',
                    'mutability' => 'MUTABLE',
                    'data' => [],
                ],
            ],
        ];
        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_throws_exception_on_invalid_raw_if_entity_range_has_key_without_entity_map_counterpart()
    {
        $contentStateRaw = [
            'blocks' => [
                [
                    'key' => 'abc',
                    'type' => 'unstyled',
                    'text' => ' ',
                    'inlineStyleRanges' => [],
                    'entityRanges' => [
                        [
                            'offset' => 0,
                            'length' => 1,
                            'key' => 2, // <---
                        ],
                    ],
                ],
            ],
            'entityMap' => [
                1 => [
                    'type' => 'LINK',
                    'mutability' => 'MUTABLE',
                    'data' => [],
                ],
            ],
        ];

        $this::shouldThrow(InvalidRawException::class)
            ->duringConvertFromRaw($contentStateRaw);
    }

    public function it_convert_raw_to_content_state_and_recognizes_multi_byte_strings()
    {
        $rawState = json_decode('{"entityMap":{},"blocks":[{"key":"33nh8","text":"aà👐","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[]}]}', true);

        /** @var ContentState $contentState */
        $contentState = $this::convertFromRaw($rawState);

        $contentState->getBlocksAsArray()[0]->getCharacterList()->shouldHaveCount(3);
        $contentState->getBlocksAsArray()[0]->getText()->shouldReturn('aà👐');
    }

    public function it_converts_serialized_state_to_content_state()
    {
        $rawState = json_decode('{"entityMap":{},"blocks":[{"key":"33nh8","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[],"entityRanges":[]}]}', true);

        /** @var ContentState $contentState */
        $contentState = $this::convertFromRaw($rawState);

        $blocks = $contentState->getBlocksAsArray();
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

    public function it_converts_serialized_state_with_entities_to_content_state()
    {
        $rawState = json_decode('{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/","rel":null,"title":"hi","extra":"foo"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}', true);

        /** @var ContentState $contentState */
        $contentState = $this::convertFromRaw($rawState);

        $blocks = $contentState->getBlocksAsArray();
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
