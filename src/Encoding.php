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
            $charList = $contentBlock->getCharacterList();
            $inlineStyleRanges = [];
            $entityRanges = [];

            /*$allStyles = [];
            foreach ($contentBlock->getCharacterList() as $characterMetadata) {
                $allStyles = array_unique(array_merge($allStyles, $characterMetadata->getStyle()));
            }*/

            /**
             * Create an unique array of all styles
             */
            $allStyles = array_reduce(
                array_map(
                    function(CharacterMetadata $characterMetadata) {
                        return $characterMetadata->getStyle();
                    },
                    $contentBlock->getCharacterList()
                ),
                function($allStyles, array $styles) {
                    foreach ($styles as $style) {
                        $allStyles[$style] = $style;
                    }
                    return $allStyles;
                },
                []
            );

            /**
             * Create inlineStyleRanges from CharacterMetadata[] for all styles
             */
            foreach ($allStyles as $style) {
                $currentStyleRanges = [];

                reset($charList);
                $currentRange = null;
                $lastCharacterHadCurrentStyle = false;
                $lastIndex = count($charList) - 1;
                do {
                    $char = current($charList);
                    $hasStyle = in_array($style, $char->getStyle());

                    /** range begins */
                    if ($hasStyle === true && $lastCharacterHadCurrentStyle === false) {
                        $currentRange = [
                            'offset' => key($charList),
                            'length' => null,
                            'style' => $style,
                        ];
                    }

                    /** range ends */
                    if ($hasStyle === false && $lastCharacterHadCurrentStyle === true) {
                        $currentRange['length'] = key($charList) - $currentRange['offset'];
                        $currentStyleRanges[] = $currentRange;
                        $currentRange = null;
                    }

                    /** early finalize range when last character reached */
                    if ($hasStyle === true && $lastIndex === key($charList)) {
                        $currentRange['length'] = key($charList) - $currentRange['offset'] + 1;
                        $currentStyleRanges[] = $currentRange;
                        $currentRange = null;
                    }

                    if ($hasStyle) {
                        $lastCharacterHadCurrentStyle = true;
                    } else {
                        $lastCharacterHadCurrentStyle = false;
                    }

                } while (next($charList) !== false);

                $inlineStyleRanges = array_merge($inlineStyleRanges, $currentStyleRanges);
            }

            /**
             * Create entityRanges from CharacterMetadata[] for all styles
             */

            reset($charList);
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

            /**
             * Build final raw data structure
             */
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

            if (isset($block['depth']) && is_numeric($block['depth']) && $block['depth'] >= 0) {
                $depth = $block['depth'];
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

    /**
     * @param $range
     *
     * @throws InvalidRawException
     */
    private static function assertRange($range)
    {
        if (!isset($range['offset']) || !is_numeric($range['offset']) || $range['offset'] < 0) {
            throw new InvalidRawException('Range offset must be an integer greater or equal 0.');
        }
        if (!isset($range['length']) || !is_numeric($range['length']) || $range['length'] < 1) {
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
                self::assertRange($range);

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
                self::assertRange($range);

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
