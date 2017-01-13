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
     * @param array $entityMap
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
     * @return ContentBlock
     */
    public function getBlockForKey($key)
    {
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
     * @param string $key
     * @param ContentBlock $contentBlock
     * @param bool $before
     *
     * @throws DraftException
     */
    public function insertContentBlock($key, ContentBlock $contentBlock, bool $before = false)
    {
        $offset = array_search($key, array_keys($this->blockMap));
        if ($offset === false) {
            throw new DraftException('Content block with given key not found.');
        }
        if ($before === false) {
            $offset++;
        }
        $this->blockMap = array_merge
        (
            array_slice($this->blockMap, 0, $offset, true),
            [$contentBlock->getKey() => $contentBlock],
            array_slice($this->blockMap, $offset, null, true)
        );
    }

    /**
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
        $this->blockMap = array_merge
        (
            array_slice($this->blockMap, 0, $offset, true),
            array_slice($this->blockMap, $offset + 1, null, true)
        );
    }
}
