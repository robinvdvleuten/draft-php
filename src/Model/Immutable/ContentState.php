<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draft\Model\Immutable;

use Draft\Exception\DraftException;
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
    private $blockMap = [];

    /**
     * @var DraftEntity[]
     */
    private $entityMap = [];

    /**
     * @var int
     */
    private $entityMapCurrentKey = 0;

    /**
     * Constructor.
     *
     * @param ContentBlock[]|array $blockMap
     * @param array                $entityMap
     */
    public function __construct(array $blockMap = [], array $entityMap = [])
    {
        $this->blockMap = $blockMap;
        $this->entityMap = $entityMap;
    }

    /**
     * @param ContentBlock[] $blocks
     *
     * @return self
     */
    public static function createFromBlockArray(array $blocks)
    {
        $contentState = new self();

        $contentState->setBlockMap($blocks);

        return $contentState;
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

        foreach (preg_split($delimiter, $text) as $string) {
            $contentBlock = ContentBlock::createFromText($string);
            $blocks[$contentBlock->getKey()] = $contentBlock;
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
     * @param ContentBlock[] $blocks
     */
    public function setBlockMap(array $blocks)
    {
        $blockMap = array_combine(array_map(function (ContentBlock $block) {
            return $block->getKey();
        }, $blocks), $blocks);

        $this->blockMap = $blockMap;
    }

    /**
     * @return ContentBlock[]
     */
    public function getBlocksAsArray()
    {
        return array_values($this->blockMap);
    }

    /**
     * @param string $key
     *
     * @return ContentBlock
     */
    public function getBlockForKey($key)
    {
        $key = (string) $key;

        foreach ($this->blockMap as $block) {
            if ($block->getKey() === $key) {
                return $block;
            }
        }
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
     * @param bool   $returnValue
     *
     * @return ContentBlock|mixed|null|string
     */
    private function getRelativeBlock($key, $relative, $returnValue = null)
    {
        $key = (string) $key;

        $returnValue = $returnValue === null ? false : $returnValue;
        $map = $this->blockMap;
        reset($map);

        do {
            if ($key === key($map)) {
                if ($relative === 'prev') {
                    prev($map);
                } elseif ($relative === 'next') {
                    next($map);
                }
                if ($key = key($map)) {
                    if ($returnValue === true) {
                        return $map[$key];
                    } else {
                        return $key;
                    }
                } else {
                    return;
                }
            }
        } while ($next = next($map) !== false);

        return;
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
        return (bool) array_filter($this->blockMap, function (ContentBlock $block) {
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
     * @param $type
     * @param $mutability
     * @param array|null $data
     *
     * @return string
     */
    public function createEntity($type, $mutability, array $data = null)
    {
        return $this->addEntity(new DraftEntity($type, $mutability, $data));
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
        return isset($this->entityMap[$key]) ? $this->entityMap[$key] : null;
    }

    /**
     * @return int
     */
    public function getLastCreatedEntityKey()
    {
        return $this->entityMapCurrentKey;
    }

    /**
     * @return DraftEntity[]
     */
    public function getEntityMap()
    {
        return $this->entityMap;
    }

    /**
     * @param DraftEntity[] $entityMap
     */
    public function setEntityMap(array $entityMap)
    {
        $this->entityMap = $entityMap;
    }

    /**
     * @param $key
     * @param DraftEntity $entity
     */
    public function setEntity($key, DraftEntity $entity)
    {
        $this->entityMap[$key] = $entity;
    }

    /**
     * @param $key
     */
    public function removeEntity($key)
    {
        unset($this->entityMap[$key]);
    }

    /**
     * @Counterpart None
     *
     * @param string       $relativeBlockKey
     * @param ContentBlock $contentBlock
     * @param bool         $before           false
     *
     * @throws DraftException
     */
    public function insertContentBlock($relativeBlockKey, ContentBlock $contentBlock, $before = null)
    {
        $relativeBlockKey = (string) $relativeBlockKey;
        $before = boolval($before);

        $offset = array_search($relativeBlockKey, array_keys($this->blockMap));
        if ($offset === false) {
            throw new DraftException('Content block with given key not found.');
        }
        if ($before === false) {
            ++$offset;
        }
        $this->blockMap = array_merge(
            array_slice($this->blockMap, 0, $offset, true),
            [$contentBlock->getKey() => $contentBlock],
            array_slice($this->blockMap, $offset, null, true)
        );
    }

    /**
     * @Counterpart None
     *
     * @param string $key
     *
     * @throws DraftException
     */
    public function removeContentBlock($key)
    {
        $offset = array_search($key, array_keys($this->blockMap));
        if ($offset === false) {
            throw new DraftException('Content block with given key not found.');
        }
        $this->blockMap = array_merge(
            array_slice($this->blockMap, 0, $offset, true),
            array_slice($this->blockMap, $offset + 1, null, true)
        );
    }

    /**
     * Splits the block at the given offset in two ContentBlock's (above and below block).
     * - The above block remains the key (the object instance stays the same)
     * - The offset character belongs to the following block.
     * - Both blocks remains the type and depth.
     * - The text and characterList will be splitted.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::splitBlock (https://facebook.github.io/draft-js/docs/api-reference-modifier.html#splitblock)
     * Split the selected block into two blocks. This should only be used if the selection is collapsed.
     *
     * Modifier::splitBlock delegates the split to following transaction:
     * https://github.com/facebook/draft-js/blob/master/src/model/transaction/splitBlockInContentState.js
     *
     * This implementation orientates to this transaction.
     *
     * @param string $key
     * @param int    $offset
     *
     * @throws DraftException
     */
    public function __splitBlock($key, $offset)
    {
        $blockToSplit = $this->getBlockForKey($key);

        if ($blockToSplit === null) {
            throw new DraftException('Cannot split block because ContentBlock was not found with given key.');
        }

        $offset = intval($offset);

        $originalTextLength = $textLength = $blockToSplit->getLength();

        if ($offset < 0 || $offset > $originalTextLength) {
            throw new DraftException(
                'Cannot split block because offset must be a number '.
                "between 0 and text length ${originalTextLength}. Given startOffset: ${offset}."
            );
        }

        $originalText = $blockToSplit->getText();
        $originalCharList = $blockToSplit->getCharacterList();

        $aboveBlockText = mb_substr($originalText, 0, $offset);
        $aboveBlockCharList = array_slice($originalCharList, 0, $offset);

        $belowBlockText = mb_substr($originalText, $offset);
        $belowBlockCharList = array_slice($originalCharList, $offset);

        $belowBlock = new ContentBlock(
            Keys::generateRandomKey(),
            $blockToSplit->getType(),
            $belowBlockText,
            $belowBlockCharList,
            $blockToSplit->getDepth()
        );

        $this->insertContentBlock($blockToSplit->getKey(), $belowBlock);

        $blockToSplit->setText($aboveBlockText);
        $blockToSplit->setCharacterList($aboveBlockCharList);
    }
}
