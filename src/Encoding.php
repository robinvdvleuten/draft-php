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

use Draft\Exception\InvalidRawException;
use Draft\Model\Immutable\CharacterMetadata;
use Draft\Model\Immutable\ContentBlock;
use Draft\Model\Immutable\ContentState;
use Draft\Util\Keys;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class Encoding
{
    public static function convertToRaw(ContentState $contentState)
    {
        $raw = [];

        $entityMap = [];

        foreach ($contentState->getEntityMap() as $key => $entity) {
            $entityMap[$key] = [
                'type' => $entity->getType(),
                'mutability' => $entity->getMutability(),
                'data' => $entity->getData(),
            ];
        }
        $raw['entityMap'] = $entityMap;

        $raw['blocks'] = array_map(function(ContentBlock $contentBlock) {
            $inlineStyleRanges = [];
            $entityRanges = [];

            $allStyles = [];
            foreach ($contentBlock->getCharacterList() as $characterMetadata) {
                $allStyles = array_unique(array_merge($allStyles, $characterMetadata->getStyle()));
            }

            $charList = $contentBlock->getCharacterList();

            foreach ($allStyles as $style) {
                $currentStyleRanges = [];

                reset($charList);
                do {
                    $char = current($charList);
                    $hasStyle = in_array($style, $char->getStyle());

                    if ($hasStyle === false) continue;

                    $styleRange = [
                        'offset' => key($charList),
                        'length' => null,
                        'style' => $style,
                    ];

                    $styleLength = 0;
                    do {
                        $char = current($charList);
                        $hasStyle = in_array($style, $char->getStyle());
                        if ($hasStyle === false) break;
                        $styleLength++;
                    } while (next($charList) !== false);

                    $styleRange['length'] = $styleLength;
                    $currentStyleRanges[] = $styleRange;
                } while (next($charList) !== false);

                $inlineStyleRanges = array_merge($inlineStyleRanges, $currentStyleRanges);
            }

            do {
                $char = current($charList);
                if ($char === false) continue;

                $entity = $char->getEntity();

                if ($entity === null) continue;

                $range = [
                    'offset' => key($charList),
                    'length' => null,
                    'key' => intval($entity),
                ];

                $length = 0;
                do {
                    $char = current($charList);
                    if (($char->getEntity() === $entity) === false) break;
                    $length++;
                } while (next($charList) !== false);

                $range['length'] = $length;
                $entityRanges[] = $range;
            } while (next($charList) !== false);

            return [
                'key' => $contentBlock->getKey(),
                'type' => $contentBlock->getType(),
                'text' => $contentBlock->getText(),
                'depth' => $contentBlock->getDepth(),
                'inlineStyleRanges' => $inlineStyleRanges,
                'entityRanges' => $entityRanges,
            ];
        }, $contentState->getBlocksAsArray());

        return $raw;
    }

    /**
     * @param array $rawState
     *
     * @return ContentState
     * @throws InvalidRawException
     */
    public static function convertFromRaw(array $rawState)
    {
        $fromStorageToLocal = [];
        $contentState = new ContentState();

        if (isset($rawState['entityMap']) && is_array($rawState['entityMap'])) {
            foreach ($rawState['entityMap'] as $storageKey => $encodedEntity) {
                if (!isset($encodedEntity['type']) || !is_string($encodedEntity['type']) || strlen($encodedEntity['type']) < 1) {
                    throw new InvalidRawException('Entity type must be a string');
                }

                if (!isset($encodedEntity['mutability']) || !is_string($encodedEntity['mutability'])) {
                    throw new InvalidRawException('Entity mutability must be a string');
                }

                if (!isset($encodedEntity['data']) || !is_array($encodedEntity['data'])) {
                    $encodedEntity['data'] = [];
                }

                $entityKey = $contentState->createEntity(
                    $encodedEntity['type'],
                    $encodedEntity['mutability'],
                    $encodedEntity['data']
                );
                $fromStorageToLocal[$storageKey] = $entityKey;
            }
        }

        if (!isset($rawState['blocks']) || !is_array($rawState['blocks'])) {
            throw new InvalidRawException('Raw blocks must be an array.');
        }

        $contentBlocks = array_map(function ($block) use ($fromStorageToLocal) {
            $key = null;
            $type = null;
            $text = null;
            $depth = 0;
            $inlineStyleRanges = [];
            $entityRanges = [];

            if (isset($block['key']) && is_string($block['key']) && strlen($block['type']) > 0) {
                $key = $block['key'];
            } else {
                $key = Keys::generateRandomKey();
            }

            if (isset($block['type']) && is_string($block['type']) && strlen($block['type']) > 0) {
                $type = $block['type'];
            } else {
                throw new InvalidRawException('Content block type must be a string.');
            }

            if (isset($block['text']) && is_string($block['text'])) {
                $text = $block['text'];
            } else {
                throw new InvalidRawException('Content block text must be a string.');
            }

            if (isset($block['depth']) && is_integer($block['depth']) && isset($block['depth']) >= 0) {
                $depth = intval($block['depth']);
            }

            if (isset($block['inlineStyleRanges']) && is_array($block['inlineStyleRanges'])) {
                $inlineStyleRanges = $block['inlineStyleRanges'];
            }

            if (isset($block['entityRanges']) && is_array($block['entityRanges'])) {
                $entityRanges = $block['entityRanges'];
            }

            $inlineStyles = Encoding::decodeInlineStyleRanges($block['text'], $inlineStyleRanges);

            $filteredEntityRanges = array_map(function ($entityRange) use ($fromStorageToLocal) {
                return array_merge($entityRange, ['key' => $fromStorageToLocal[$entityRange['key']]]);
            }, array_filter($entityRanges, function ($entityRange) use ($fromStorageToLocal) {
                if (!isset($entityRange['key']) || !is_numeric($entityRange['key'])) {
                    throw new InvalidRawException('Entity range key must be an integer greater than zero.');
                }
                if (!isset($fromStorageToLocal[$entityRange['key']])) {
                    throw new InvalidRawException('Entity range key reference to entity map is invalid.');
                }
                return isset($fromStorageToLocal[$entityRange['key']]);
            }));

            $entities = Encoding::decodeEntityRanges($block['text'], $filteredEntityRanges);
            $characterList = Encoding::createCharacterList($inlineStyles, $entities);

            return new ContentBlock($key, $type, $text, $characterList, $depth);
        }, $rawState['blocks']);

        $contentState->setBlockMap($contentBlocks);

        return $contentState;
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

    private static function validateRange($ranges)
    {
        if (!isset($ranges['offset']) || !is_numeric($ranges['offset']) || $ranges['offset'] < 0) {
            sd($ranges['offset']);
            throw new InvalidRawException('Range offset must be an integer greater or equal 0.');
        }
        if (!isset($ranges['length']) || !is_numeric($ranges['length']) || $ranges['length'] < 1) {
            throw new InvalidRawException('Range length must be an integer greater or equal 1.');
        }
    }

    /**
     * @param $text
     * @param array|null $ranges
     *
     * @return array
     * @throws InvalidRawException
     */
    public static function decodeEntityRanges($text, array $ranges = null)
    {
        // @TODO Make sure that strlen respects characters like emoji.
        $entities = array_fill(0, strlen($text), null);

        if ($ranges) {
            foreach ($ranges as $range) {
                self::validateRange($range);

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
     * @throws InvalidRawException
     */
    public static function decodeInlineStyleRanges($text, array $ranges = null)
    {
        $styles = array_fill(0, strlen($text), []);

        if ($ranges) {
            foreach ($ranges as $range) {
                self::validateRange($range);

                if (!isset($range['style']) || !is_string($range['style']) || strlen($range['style']) < 1) {
                    throw new InvalidRawException('Range style must be a not empty string.');
                }

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
