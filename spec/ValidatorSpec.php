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

use Draft\Exception\InvalidContentStateException;
use Draft\Model\Entity\DraftEntity;
use Draft\Model\Immutable\CharacterMetadata;
use Draft\Model\Immutable\ContentBlock;
use Draft\Model\Immutable\ContentState;
use Draft\ValidatorConfig;
use PhpSpec\ObjectBehavior;

class ValidatorSpec extends ObjectBehavior
{
    public function it_should_throw_exception_when_content_block_text_contains_newline()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', 'this is a re' . PHP_EOL . 'ally interesting', [], 0),
        ]);

        $this::shouldThrow(InvalidContentStateException::class)
            ->duringValidate($contentState);
    }

    public function it_should_remove_not_allowed_entity_from_entity_map()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'NOT_ALLOWED_BLOCK_TYPE', 'a test text', [
                new CharacterMetadata(['BOLD'], 0)
            ], 0),
        ]);

        $contentState->createEntity('NOT_ALLOWED_PHOTO', DraftEntity::MUTABILITY_SEGMENTED);
        $contentState->createEntity('NOT_ALLOWED_VIDEO', DraftEntity::MUTABILITY_SEGMENTED);
        $contentState->createEntity('ALLOWED_LINK', DraftEntity::MUTABILITY_SEGMENTED);

        /** @var ContentState $contentState */
        $contentState = $this::validate($contentState, new ValidatorConfig([
            'entity_types' => ['ALLOWED_LINK'],
        ]));

        $contentState->getEntityMap()->shouldHaveCount(1);
    }

    public function it_should_set_not_allowed_block_types_to_default()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'NOT_ALLOWED_BLOCK_TYPE', 'a test text', [
                new CharacterMetadata(['BOLD'], 0)
            ], 0),
        ]);

        $contentState = $this::validate($contentState, new ValidatorConfig());

        $contentState->getFirstBlock()->getType()->shouldReturn('unstyled');
    }

    public function it_should_set_content_block_depth_to_max_depth_if_bigger_than_max_depth()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', 'a test text', [], 20),
        ]);

        $contentState = $this::validate($contentState, new ValidatorConfig());

        $contentState->getFirstBlock()->getDepth()->shouldReturn(0);
    }

    public function it_should_autofix_content_block_depth()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'ordered-list-item', 'a test text', [], 0),
            new ContentBlock('b', 'ordered-list-item', 'a test text', [], 1),
            new ContentBlock('c', 'ordered-list-item', 'a test text', [], 3),
            new ContentBlock('d', 'ordered-list-item', 'a test text', [], 0),
            new ContentBlock('e', 'ordered-list-item', 'a test text', [], 100),
            new ContentBlock('f', 'ordered-list-item', 'a test text', [], 140),
            new ContentBlock('g', 'ordered-list-item', 'a test text', [], -400),
        ]);

        /** @var ContentState $contentState */
        $contentState = $this::validate($contentState, new ValidatorConfig());

        $contentState->getBlockForKey('a')->getDepth()->shouldReturn(0);
        $contentState->getBlockForKey('b')->getDepth()->shouldReturn(1);
        $contentState->getBlockForKey('c')->getDepth()->shouldReturn(2);
        $contentState->getBlockForKey('d')->getDepth()->shouldReturn(0);
        $contentState->getBlockForKey('e')->getDepth()->shouldReturn(1); // 100 -> 1
        $contentState->getBlockForKey('f')->getDepth()->shouldReturn(2); // 140 -> 2
        $contentState->getBlockForKey('g')->getDepth()->shouldReturn(0); // -400 -> 0
    }

    public function it_should_remove_depth_from_unsupported_block_types()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'ordered-list-item', '', [], 0),
            new ContentBlock('b', 'ordered-list-item', '', [], 1),
            new ContentBlock('c', 'unstyled', '', [], 0),
            new ContentBlock('d', 'header-one', '', [], 0),
            new ContentBlock('e', 'atomic', '', [], 0),
            new ContentBlock('f', 'unordered-list-item', '', [], 0),
            new ContentBlock('g', 'unordered-list-item', '', [], 1),
            new ContentBlock('h', 'NOT_ALLOWED_BLOCK_TYPE', '', [], 0),
        ]);

        /** @var ContentState $contentState */
        $contentState = $this::validate($contentState, new ValidatorConfig());

        $contentState->getBlockForKey('a')->getDepth()->shouldReturn(0); // removed depth
        $contentState->getBlockForKey('b')->getDepth()->shouldReturn(1);
        $contentState->getBlockForKey('c')->getDepth()->shouldReturn(0); // removed depth
        $contentState->getBlockForKey('d')->getDepth()->shouldReturn(0); // removed depth
        $contentState->getBlockForKey('e')->getDepth()->shouldReturn(0); // removed depth
        $contentState->getBlockForKey('f')->getDepth()->shouldReturn(0); // removed depth
        $contentState->getBlockForKey('g')->getDepth()->shouldReturn(1);
        $contentState->getBlockForKey('h')->getDepth()->shouldReturn(0); // removed depth
    }

    public function it_should_not_allow_start_list_item_with_depth_1_before_0_exists()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'ordered-list-item', '', [], 1),
        ]);

        $this::shouldThrow(InvalidContentStateException::class)
            ->duringValidate($contentState, new ValidatorConfig(), false);
    }

    public function it_should_remove_not_allowed_styles_from_character_meta_data()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', 'a test text', [
                new CharacterMetadata(['BOLD', 'NOT_ALLOWED', 'ITALIC', 'NOT_ALLWED_2'])
            ], 0),
        ]);

        /** @var ContentState $contentState */
        $contentState = $this::validate($contentState, new ValidatorConfig());

        $contentState->getFirstBlock()->getCharacterList()[0]->getStyle()->shouldHaveCount(2);
    }

    public function it_should_remove_not_existing_entity_from_character_meta_data()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', 'a test text', [
                new CharacterMetadata([], 999)
            ], 0),
        ]);

        /** @var ContentState $contentState */
        $contentState = $this::validate($contentState, new ValidatorConfig());

        $contentState->getFirstBlock()->getCharacterList()[0]->getEntity()->shouldBeNull();
    }

    public function it_should_throw_exception_if_entity_mutability_not_exists()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', 'a test text', [
                new CharacterMetadata([], 999)
            ], 0),
        ]);

        $contentState->__setEntity(999, new DraftEntity('LINK', 'NOT_EXISTING_MUTABILITY'));

        $this::shouldThrow(InvalidContentStateException::class)
            ->duringValidate($contentState, new ValidatorConfig());
    }

    public function it_should_throw_exception_when_exceed_max_line_count()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', '', [], 0),
            new ContentBlock('b', 'unstyled', '', [], 0),
            new ContentBlock('c', 'unstyled', '', [], 0),
        ]);

        $this::shouldThrow(InvalidContentStateException::class)
            ->duringValidate($contentState, new ValidatorConfig(['max_line_count' => 2]));
    }

    public function it_should_throw_exception_when_exceed_max_character_count()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', '1234567', [], 0),
            new ContentBlock('b', 'unstyled', '890', [], 0),
        ]);

        $this::shouldThrow(InvalidContentStateException::class)
            ->duringValidate($contentState, new ValidatorConfig(['max_character_count' => 10]));
    }

    public function it_should_throw_exception_when_exceed_max_word_count()
    {
        $contentState = ContentState::createFromBlockArray([
            new ContentBlock('a', 'unstyled', 'this is a really interesting', [], 0),
            new ContentBlock('b', 'unstyled', 'part of the world', [], 0),
        ]);

        $this::shouldThrow(InvalidContentStateException::class)
            ->duringValidate($contentState, new ValidatorConfig(['max_word_count' => 8]));
    }
}
