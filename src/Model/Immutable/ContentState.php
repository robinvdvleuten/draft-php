<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft\Model\Immutable;

use Draft\Model\Entity\DraftEntity;
use Draft\Util\Keys;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class ContentState
{
    /**
     * @var ContentBlock[]
     */
    private $blockMap;

    /**
     * @var DraftEntity[]
     */
    private $entityMap;

    /**
     * @var int
     */
    private $entityMapCurrentKey = 0;

    /**
     * @var SelectionState
     */
    private $selectionBefore;

    /**
     * @var SelectionState
     */
    private $selectionAfter;

    /**
     * Constructor.
     *
     * @param ContentBlock[] $blockMap
     * @param SelectionState $selectionBefore
     * @param SelectionState $selectionAfter
     */
    public function __construct(array $blockMap = [], SelectionState $selectionBefore = null, SelectionState $selectionAfter = null)
    {
        $this->blockMap = $blockMap;
        $this->selectionBefore = $selectionBefore;
        $this->selectionAfter = $selectionAfter;
    }

    /**
     * @param ContentBlock[] $blocks
     *
     * @return self
     */
    public static function createFromBlockArray(array $blocks)
    {
        $blockMap = array_combine(array_map(function (ContentBlock $block) {
            return $block->getKey();
        }, $blocks), $blocks);

        $selectionState = SelectionState::createEmpty(current($blockMap)->getKey());

        return new self($blockMap, $selectionState, $selectionState);
    }

    /**
     * @param string $text
     * @param string $delimiter
     *
     * @return self
     */
    public static function createFromText($text, $delimiter = '/\r\n?|\n/')
    {
        $blocks = [];
        $characterMetadata = new CharacterMetadata();

        foreach (preg_split($delimiter, $text) as $string) {
            $blockKey = Keys::generateRandomKey();

            $blocks[$blockKey] = new ContentBlock(
                $blockKey,
                'unstyled',
                $string,
                array_fill(0, count($blocks), $characterMetadata)
            );
        }

        return self::createFromBlockArray($blocks);
    }

    /**
     * @return ContentBlock[]
     */
    public function getBlockMap()
    {
        return $this->blockMap;
    }

    /**
     * @return ContentBlock[]
     */
    public function getBlocksAsArray()
    {
        return array_values($this->blockMap);
    }

    /**
     * @return SelectionState
     */
    public function getSelectionBefore()
    {
        return $this->selectionBefore;
    }

    /**
     * @return SelectionState
     */
    public function getSelectionAfter()
    {
        return $this->selectionAfter;
    }

    /**
     * @return ContentBlock
     */
    public function getBlockForKey($key)
    {
        foreach ($this->blockMap as $block) {
            if ($block->getKey() === $key) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @return ContentBlock
     */
    public function getFirstBlock()
    {
        return reset($this->blockMap);
    }

    /**
     * @return ContentBlock
     */
    public function getLastBlock()
    {
        return end($this->blockMap);
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getKeyBefore($key)
    {
        return $this->getRelativeBlock($key, 'prev');
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function getKeyAfter($key)
    {
        return $this->getRelativeBlock($key, 'next');
    }

    /**
     * @param string $key
     *
     * @return ContentBlock|null
     */
    public function getBlockBefore($key)
    {
        return $this->getRelativeBlock($key, 'prev', true);
    }

    /**
     * @param string $key
     *
     * @return ContentBlock|null
     */
    public function getBlockAfter($key)
    {
        return $this->getRelativeBlock($key, 'next', true);
    }

    /**
     * @param string $key
     * @param string $relative
     * @param bool $return_value
     *
     * @return ContentBlock|mixed|null|string
     */
    private function getRelativeBlock($key, $relative, $return_value = false)
    {
        $map = $this->blockMap;
        reset($map);

        do {
            if ($key === key($map)) {
                if ($relative === 'prev') {
                    prev($map);
                } else if ($relative === 'next') {
                    next($map);
                }
                if ($key = key($map)) {
                    if ($return_value === true) {
                        return $map[$key];
                    } else {
                        return $key;
                    }
                } else {
                    return null;
                }
            }
        } while ($next = next($map) !== false);

        return null;
    }

    /**
     * @param string $delimiter
     *
     * @return string
     */
    public function getPlainText($delimiter = PHP_EOL)
    {
        return implode($delimiter, array_map(function (ContentBlock $block) {
            return $block->getText();
        }, $this->blockMap));
    }

    /**
     * @return bool
     */
    public function hasText()
    {
        return !!array_filter($this->blockMap, function (ContentBlock $block) {
            return strlen($block->getText()) > 0;
        });
    }

    /**
     * @param DraftEntity $entity
     *
     * @return string
     */
    public function addEntity(DraftEntity $entity)
    {
        $key = (string) ++$this->entityMapCurrentKey;
        $this->entityMap[$key] = $entity;
        return $key;
    }

    /**
     * @param string $type
     * @param $mutability
     * @param array|null $data
     */
    public function createEntity($type, $mutability, array $data = null)
    {
        $this->addEntity(new DraftEntity($type, $mutability, $data));
    }

    /**
     * @param $key
     * @param array $newData
     */
    public function replaceEntityData($key, array $newData)
    {
        $currentEntity = $this->getEntity($key);
        $newEntity = new DraftEntity($currentEntity->getType(), $currentEntity->getMutability(), $newData);
        $this->entityMap[$key] = $newEntity;
    }

    /**
     * @param string $key
     *
     * @return DraftEntity|null
     */
    public function getEntity($key)
    {
        return $this->entityMap[$key] ?? null;
    }

    /**
     * @return int
     */
    public function getLastCreatedEntityKey()
    {
        return $this->entityMapCurrentKey;
    }

    /**
     * @return array
     */
    public function getEntityMap()
    {
        return $this->entityMap;
    }
}
