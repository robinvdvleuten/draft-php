<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft;

use Draft\Util\Keys;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class Encoding
{
    public static function convertFromRaw(array $rawState)
    {
        $fromStorageToLocal = [];

        foreach ($rawState['entityMap'] as $storageKey => $encodedEntity) {
            $newKey = DraftEntity::create(
                $encodedEntity['type'],
                $encodedEntity['mutability'],
                isset($encodedEntity['data']) ? $encodedEntity['data'] : null
            );

            $fromStorageToLocal[$storageKey] = $newKey;
        }

        return array_map(function ($block) use ($fromStorageToLocal) {
            $key = isset($block['key']) ? $block['key'] : Keys::generateRandomKey();
            $depth = isset($block['depth']) ? $block['depth'] : 0;
            $inlineStyleRanges = isset($block['inlineStyleRanges']) ? $block['inlineStyleRanges'] : [];
            $entityRanges = isset($block['entityRanges']) ? $block['entityRanges'] : [];

            $inlineStyles = Encoding::decodeInlineStyleRanges($block['text'], $inlineStyleRanges);

            $filteredEntityRanges = array_map(function ($entityRange) use ($fromStorageToLocal) {
                return array_merge($entityRange, ['key' => $fromStorageToLocal[$entityRange['key']]]);
            }, array_filter($entityRanges, function ($entityRange) use ($fromStorageToLocal) {
                return isset($fromStorageToLocal[$entityRange['key']]);
            }));

            $entities = Encoding::decodeEntityRanges($block['text'], $filteredEntityRanges);;
            $characterList = Encoding::createCharacterList($inlineStyles, $entities);

            return new ContentBlock($key, $block['type'], $block['text'], $characterList, $depth);
        }, $rawState['blocks']);
    }

    public static function createCharacterList(array $inlineStyles, array $entities)
    {
        return array_map(function ($style, $index) use ($entities) {
            return new CharacterMetadata($style, $entities[$index]);
        }, $inlineStyles, array_keys($inlineStyles));
    }

    public static function decodeEntityRanges($text, array $ranges = null)
    {
        $entities = array_fill(0, strlen($text), null);

        if ($ranges) {
            foreach ($ranges as $range) {
                $cursor = strlen(substr($text, 0, $range['offset']));
                $end = $cursor + strlen(substr($text, $range['offset'], $range['length']));

                while ($cursor < $end) {
                    $entities[$cursor] = $range['key'];
                    $cursor++;
                }
            }
        }

        return $entities;
    }

    public static function decodeInlineStyleRanges($text, array $ranges = null)
    {
        $styles = array_fill(0, strlen($text), []);

        if ($ranges) {
            foreach ($ranges as $range) {
                $cursor = strlen(substr($text, 0, $range['offset']));
                $end = $cursor + strlen(substr($text, $range['offset'], $range['length']));

                while ($cursor < $end) {
                    $styles[$cursor][] = $range['style'];
                    $cursor++;
                }
            }
        }

        return $styles;
    }
}
