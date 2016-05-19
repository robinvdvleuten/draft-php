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
class ContentState
{
    /**
     * @var ContentBlock[]
     */
    private $blockMap;

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
}
