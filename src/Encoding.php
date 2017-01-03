<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft;

use Draft\Model\Entity\DraftEntity;
use Draft\Model\Immutable\CharacterMetadata;
use Draft\Model\Immutable\ContentBlock;
use Draft\Model\Immutable\ContentState;
use Draft\Util\Keys;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class Encoding
{
    /**
     * @param array $rawState
     *
     * @return ContentState
     */
    public static function convertFromRaw(array $rawState)
    {
        $fromStorageToLocal = [];

        if (isset($rawState['entityMap'])) {
            foreach ($rawState['entityMap'] as $storageKey => $encodedEntity) {
                $newKey = DraftEntity::create(
                    $encodedEntity['type'],
                    $encodedEntity['mutability'],
                    isset($encodedEntity['data']) ? $encodedEntity['data'] : null
                );

                $fromStorageToLocal[$storageKey] = $newKey;
            }
        }

        $contentBlocks = array_map(function ($block) use ($fromStorageToLocal) {
            $key = isset($block['key']) ? $block['key'] : Keys::generateRandomKey();
            $depth = isset($block['depth']) ? intval($block['depth']) : 0;
            $inlineStyleRanges = isset($block['inlineStyleRanges']) ? $block['inlineStyleRanges'] : [];
            $entityRanges = isset($block['entityRanges']) ? $block['entityRanges'] : [];

            $inlineStyles = Encoding::decodeInlineStyleRanges($block['text'], $inlineStyleRanges);

            $filteredEntityRanges = array_map(function ($entityRange) use ($fromStorageToLocal) {
                return array_merge($entityRange, ['key' => $fromStorageToLocal[$entityRange['key']]]);
            }, array_filter($entityRanges, function ($entityRange) use ($fromStorageToLocal) {
                return isset($fromStorageToLocal[$entityRange['key']]);
            }));

            $entities = Encoding::decodeEntityRanges($block['text'], $filteredEntityRanges);
            $characterList = Encoding::createCharacterList($inlineStyles, $entities);

            return new ContentBlock($key, $block['type'], $block['text'], $characterList, $depth);
        }, $rawState['blocks']);

        return new ContentState($contentBlocks);
    }

    /**
     * @param array $inlineStyles
     * @param array $entities
     *
     * @return CharacterMetadata[]
     */
    public static function createCharacterList(array $inlineStyles, array $entities)
    {
        return array_map(function ($style, $index) use ($entities) {
            return new CharacterMetadata($style, $entities[$index]);
        }, $inlineStyles, array_keys($inlineStyles));
    }

    /**
     * @param $text
     * @param array|null $ranges
     *
     * @return array
     */
    public static function decodeEntityRanges($text, array $ranges = null)
    {
        // @TODO Make sure that strlen respects characters like emoji.
        $entities = array_fill(0, strlen($text), null);

        if ($ranges) {
            foreach ($ranges as $range) {
                $cursor = strlen(substr($text, 0, $range['offset']));
                $end = $cursor + strlen(substr($text, $range['offset'], $range['length']));

                while ($cursor < $end) {
                    $entities[$cursor] = $range['key'];
                    ++$cursor;
                }
            }
        }

        return $entities;
    }

    /**
     * @param $text
     * @param array|null $ranges
     *
     * @return array
     */
    public static function decodeInlineStyleRanges($text, array $ranges = null)
    {
        $styles = array_fill(0, strlen($text), []);

        if ($ranges) {
            foreach ($ranges as $range) {
                $cursor = strlen(substr($text, 0, $range['offset']));
                $end = $cursor + strlen(substr($text, $range['offset'], $range['length']));

                while ($cursor < $end) {
                    $styles[$cursor][] = $range['style'];
                    ++$cursor;
                }
            }
        }

        return $styles;
    }
}
