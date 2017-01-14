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
use Draft\Helper;
use Draft\Util\Keys;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 *
 * @CounterpartURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html
 *
 * Not implemented functions:
 * - ContentBlock::getData(): Map<any, any>
 *   Currently this package not support block data, they are not serialized in the content raw
 *
 * @TODO:
 * Checkout if make sense to implement:
 * draft-js/src/model/modifier/getCharacterRemovalRange.js
 *
 * @TODO:
 * implement magic getter?
 * https://facebook.github.io/draft-js/docs/api-reference-content-block.html#properties
 */
class ContentBlock
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $text;

    /**
     * @var CharacterMetadata[]
     */
    private $characterList;

    /**
     * @var int
     */
    private $depth;

    /**
     * The $characterList array size must equal the $text length.
     *
     * @param string              $key
     * @param string              $type
     * @param string              $text
     * @param CharacterMetadata[] $characterList
     * @param int                 $depth
     *
     * @throws DraftException
     */
    public function __construct($key, $type, $text = '', array $characterList = [], $depth = 0)
    {
        if (($a = count($characterList)) !== ($b = mb_strlen($text))) {
            throw new DraftException(
                "Cannot create ContentBlock with char list size ${a} and text length ${b}. ".
                'It must be identical.'
            );
        }

        foreach ($characterList as $characterMetadata) {
            if (!$characterMetadata instanceof CharacterMetadata) {
                throw new DraftException(
                    'Cannot create ContentBlock because characterList must contains only items '.
                    'of type CharacterMetadata.'
                );
            }
        }

        $this->key = (string) $key;
        $this->type = (string) $type;
        $this->text = (string) $text;
        $this->characterList = $characterList;
        $this->setDepth($depth);
    }

    /**
     * @param string $text
     *
     * @return ContentBlock
     */
    public static function createFromText($text)
    {
        $text = (string) $text;

        $blockKey = Keys::generateRandomKey();

        return new self(
            $blockKey,
            'unstyled',
            $text,
            array_fill(0, mb_strlen($text), new CharacterMetadata())
        );
    }

    /**
     * @Counterpart ContentBlock::getKey(): string
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L51
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#getkey
     * @OfficialDocumentation
     * Returns the string key for this ContentBlock.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @Counterpart None
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @Counterpart ContentBlock::getType(): DraftBlockType
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L55
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#gettype
     * @OfficialDocumentation
     * Returns the string key for this ContentBlock.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @Counterpart None
     *
     * @Notes
     * Have the function as Modifier::setBlockType but on ContentBlock level.
     * https://github.com/facebook/draft-js/blob/master/src/model/modifier/DraftModifier.js#L206
     * https://facebook.github.io/draft-js/docs/api-reference-modifier.html#setblocktype
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @Counterpart ContentBlock::getText(): string
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L59
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#gettext
     * @OfficialDocumentation
     * Returns the full plaintext for this ContentBlock.
     * This value does not contain any styling, decoration, or HTML information.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @deprecated
     *
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @Counterpart ContentBlock::getCharacterList(): List<CharacterMetadata>
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L63
     *
     * @return CharacterMetadata[]
     */
    public function getCharacterList()
    {
        return $this->characterList;
    }

    /**
     * @deprecated
     *
     * @param CharacterMetadata[] $characterList
     */
    public function setCharacterList(array $characterList)
    {
        $this->characterList = $characterList;
    }

    /**
     * Use this function to get the real text length of the block which equals the last offset.
     * Don't use strlen() on $contentBlock->getText() to determinate the character count!
     * strlen counts multi byte charset characters (like UTF-8, etc.) as multiple characters!
     * This returns the real text length - every character whether ASCII, UTF-8/Unicode counts as a single character.
     *
     * @Counterpart getLength(): number
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L67
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#getlength
     * @OfficialDocumentation
     * Returns the length of the plaintext for the ContentBlock.
     * This value uses the standard JavaScript length property for the string, and is therefore not Unicode-aware --
     * surrogate pairs will be counted as two characters.
     *
     * @Notes
     * The implementation in draft-js however is not MultiByte aware - this method is.
     *
     * @return int
     */
    public function getLength()
    {
        return mb_strlen($this->getText());
    }

    /**
     * @Counterpart ContentBlock::getDepth(): number
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L71
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#getdepth
     * @OfficialDocumentation
     * Returns the depth value for this block, if any. This is currently used only for list items.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @Counterpart None
     *
     * @param int $depth Must equal or greater than zero integer
     */
    public function setDepth($depth)
    {
        $depth = intval($depth);
        $this->depth = $depth < 0 ? 0 : $depth;
    }

    /**
     * @Counterpart ContentBlock::getInlineStyleAt(offset: number): DraftInlineStyle
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L79
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#getinlinestyleat
     * @OfficialDocumentation
     * Returns the DraftInlineStyle value (an OrderedSet<string>) at a given offset within this ContentBlock.
     *
     * @param $offset
     *
     * @return array
     */
    public function getInlineStyleAt($offset)
    {
        if (!isset($this->characterList[$offset])) {
            return [];
        }

        return $this->characterList[$offset]->getStyle();
    }

    /**
     * Returns the entity key value (or null if none) at a given offset within this ContentBlock.
     *
     * @Counterpart ContentBlock::getEntityAt(offset: number): ?string
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L84
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#getentityat
     * @OfficialDocumentation
     * Returns the entity key value (or null if none) at a given offset within this ContentBlock.
     *
     * @param $offset
     *
     * @return null|string
     */
    public function getEntityAt($offset)
    {
        if (!isset($this->characterList[$offset])) {
            return;
        }

        return $this->characterList[$offset]->getEntity();
    }

    /**
     * @Counterpart ContentBlock::findStyleRanges(filterFn: (value: CharacterMetadata) => boolean, callback: (start: number, end: number) => void): void
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L92
     * @CounterpartDocumentation
     * Execute a callback for every contiguous range of styles within the block.
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#findstyleranges
     * @OfficialDocumentation
     * Executes a callback for each contiguous range of styles within this ContentBlock.
     *
     * @Notes
     * Similarity to BlockTree.js::generateLeaves
     * (https://github.com/facebook/draft-js/blob/master/src/model/immutable/BlockTree.js#L132)
     * The generateLeaves function in BlockTree is different...
     * @TODO explain why
     *
     * @param $filterFn (value: CharacterMetadata) : boolean
     * @param $callback (start: number, end: number) : void
     */
    public function findStyleRanges($filterFn, $callback)
    {
        $this->findRanges(
            $this->getCharacterList(),
            function (CharacterMetadata $a, CharacterMetadata $b) {
                // Two array have same values (order not relevant)
                // Alternative to: Immutable.List === Immutable.List
                return $a->haveEqualStyle($b);
            },
            $filterFn,
            $callback
        );
    }

    /**
     * @Counterpart ContentBlock::findEntityRanges(filterFn: (value: CharacterMetadata) => boolean, callback: (start: number, end: number) => void): void
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/ContentBlock.js#L107
     * @CounterpartDocumentation
     * Execute a callback for every contiguous range of entities within the block.
     *
     * @OfficialDocumentationURL https://facebook.github.io/draft-js/docs/api-reference-content-block.html#findentityranges
     * @OfficialDocumentation
     * Executes a callback for each contiguous range of entities within this ContentBlock.
     *
     * @Notes
     * Similar to getRangesForDraftEntity.js
     * https://github.com/facebook/draft-js/blob/master/src/model/modifier/getRangesForDraftEntity.js
     * The getRangesForDraftEntity.js function uses only this method but returns Array<DraftRange> instead of
     * calling the callback function for every result.
     * Additionally it throws an exception when when no ranges with the given entity key are found.
     *
     * @param $filterFn (value: CharacterMetadata) : boolean
     * @param $callback (start: number, end: number) : void
     */
    public function findEntityRanges($filterFn, $callback)
    {
        $this->findRanges(
            $this->getCharacterList(),
            function (CharacterMetadata $a, CharacterMetadata $b) {
                return $a->getEntity() === $b->getEntity();
            },
            $filterFn,
            $callback
        );
    }

    /**
     * Implements the same algorithm to find ranges like draft.js
     * This is used by ContentBlock methods findStyleRanges and findEntityRanges but can be used
     * for other purposes with other parameters too (ex: find leafs for rendering)
     * (This function is however not exposed by the draft.js package directly).
     *
     * @Counterpart draft-js/src/model/immutable/findRangesImmutable.js
     * @CounterpartURL https://github.com/facebook/draft-js/blob/master/src/model/immutable/findRangesImmutable.js
     * @CounterpartDocumentation
     * Search through an array to find contiguous stretches of elements that
     * match a specified filter function.
     *
     * When ranges are found, execute a specified `found` function to supply
     * the values to the caller.
     *
     * @OfficialDocumentation
     * None
     *
     * @Notes
     * Used elsewhere in draft-js:
     * https://github.com/facebook/draft-js/blob/master/src/model/immutable/BlockTree.js#L132 (find ranges by style)
     * https://github.com/facebook/draft-js/blob/master/src/model/immutable/BlockTree.js#L89 (find ranges by decorator)
     * https://github.com/facebook/draft-js/blob/master/src/model/encoding/encodeInlineStyleRanges.js#L44 (find ranges by one style)
     * https://github.com/facebook/draft-js/blob/master/src/model/modifier/getRangesForDraftEntity.js (find ranges by entity)
     * https://github.com/facebook/draft-js/blob/master/src/model/transaction/removeEntitiesAtEdges.js#L73
     *
     * @param array    $array
     * @param callable $areEqualFn
     * @param callable $filterFn
     * @param callable $foundFn
     */
    private function findRanges($array, callable $areEqualFn, callable $filterFn, callable $foundFn)
    {
        if (count($array) < 1) {
            return;
        }

        $cursor = 0;

        // array_reduce function don't pass the current index as argument like Immutable.List.reduce
        // any many other languages does - this is a workaround to get the key by an object
        $getIndexByValue = function ($obj) use ($array) {
            foreach ($array as $index => $item) {
                if ($item === $obj) {
                    return $index;
                }
            }

            return;
        };

        array_reduce($array, function ($value, $nextValue) use (&$cursor, $areEqualFn, $filterFn, $foundFn, $getIndexByValue) {
            $currentIndex = $getIndexByValue($nextValue);
            if (!$areEqualFn($value, $nextValue)) {
                if ($filterFn($value)) {
                    $foundFn($cursor, $currentIndex - 1);
                }
                $cursor = $currentIndex;
            }

            return $nextValue;
        }, $array[0]);

        $lastItem = $array[count($array) - 1];
        if ($filterFn($lastItem)) {
            $foundFn($cursor, count($array) - 1);
        }
    }

    /**
     * Replace the text with the given text on ContentBlock level.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::replaceText but on ContentBlock level only instead of ContentState
     *
     * Modifier::replaceText (https://facebook.github.io/draft-js/docs/api-reference-modifier.html#replacetext)
     * Replaces the specified range of this ContentState with the supplied string,
     * with the inline style and entity key applied to the entire inserted string.
     * Example: On Facebook, when replacing @abraham lincoln with a mention of Abraham Lincoln,
     * the entire old range is the target to replace and the mention entity should be applied to the inserted string.
     *
     * @param int    $startOffset
     * @param int    $endOffset
     * @param string $insertText
     * @param array  $inlineStyle
     * @param null   $entityKey
     *
     * @throws DraftException
     */
    public function __replaceText(
        $startOffset,
        $endOffset,
        $insertText,
        array $inlineStyle = [],
        $entityKey = null
    ) {
        $startOffset = intval($startOffset);
        $endOffset = intval($endOffset);
        $insertText = (string) $insertText;
        $this->assertOffsets($startOffset, $endOffset);

        $text = $this->getText();

        $newCharList = $this->getCharacterList();

        $insertTextLength = mb_strlen($insertText);
        $replacementTextLength = $endOffset - $startOffset;

        $newChars = array_map(function () use ($inlineStyle, $entityKey) {
            return new CharacterMetadata($inlineStyle, $entityKey);
        }, array_fill(0, $insertTextLength, null));

        // 0 offset difference = 1 character etc.
        array_splice($newCharList, $startOffset, $replacementTextLength, $newChars);

        $newText = Helper::replaceOffsetMultiByte($text, $startOffset, $replacementTextLength, $insertText);

        /*dump([
            'text' => $text,
            'newText' => $newText,
            'startOffset' => $startOffset,
            'replacementTextLength' => $replacementTextLength,
            'insertText' => $insertText,
            'endOffset' => $endOffset,
        ]);*/

        $this->text = $newText;
        $this->characterList = $newCharList;
    }

    /**
     * Insert a text to the given offset.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::insertText but on ContentBlock level only instead of ContentState
     *
     * Modifier::insertText (https://facebook.github.io/draft-js/docs/api-reference-modifier.html#inserttext)
     * Identical to replaceText, but enforces that the target range is collapsed so that no characters are replaced.
     * This is just for convenience, since text edits are so often insertions rather than replacements.
     *
     * @param int         $offset
     * @param string      $insertText
     * @param array       $inlineStyle
     * @param string|null $entityKey
     *
     * @throws DraftException
     */
    public function __insertText(
        $offset,
        $insertText,
        array $inlineStyle = [],
        $entityKey = null
    ) {
        $offset = intval($offset);
        $insertText = (string) $insertText;
        $entityKey = (string) $entityKey;
        $this->__replaceText($offset, $offset, $insertText, $inlineStyle, $entityKey);
    }

    /**
     * Removes the text from the given range.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::removeRange but on ContentBlock level only instead of Content State
     *
     * Modifier::removeRange (https://github.com/facebook/draft-js/blob/master/src/model/modifier/DraftModifier.js#L136)
     * Remove an entire range of text from the editor. The removal direction is important for proper entity deletion behavior.
     *
     * @param int $startOffset
     * @param int $endOffset
     *
     * @throws DraftException
     */
    public function __removeText(
        $startOffset,
        $endOffset
    ) {
        $startOffset = intval($startOffset);
        $endOffset = intval($endOffset);
        $this->assertOffsets($startOffset, $endOffset);

        $text = $this->getText();
        $newCharList = $this->getCharacterList();

        $length = $endOffset - $startOffset + 1;
        array_splice($newCharList, $startOffset, $length);
        $newText = Helper::replaceOffsetMultiByte($text, $startOffset, $length, '');

        $this->text = $newText;
        $this->characterList = $newCharList;
    }

    /**
     * Removes the entity from the given text range.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::applyEntity but on ContentBlock level only instead of Content State
     *
     * Modifier::applyEntity (https://github.com/facebook/draft-js/blob/master/src/model/modifier/DraftModifier.js#L243)
     * Apply an entity to the entire selected range, or remove all entities from the range if entityKey is null.
     *
     * @param int         $start
     * @param int         $length
     * @param string|null $entity
     */
    public function __applyEntityToRange(int $start, int $length, string $entity = null)
    {
        $offsetStart = $start;
        $offsetEnd = $start + $length;

        foreach ($this->getCharacterList() as $key => $char) {
            if ($key > $offsetStart && $key < $offsetEnd) {
                $char->setEntity($entity);
            }
        }
    }

    /**
     * Adds the given style from the given text range.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::applyInlineStyle but on ContentBlock level only instead of Content State
     *
     * Modifier::applyInlineStyle (https://facebook.github.io/draft-js/docs/api-reference-modifier.html#applyinlinestyle)
     * Apply the specified inline style to the entire selected range.
     *
     * @param int         $start
     * @param int         $length
     * @param string|null $addStyle
     */
    public function __addStyleToRange(int $start, int $length, string $addStyle = null)
    {
        $offsetStart = $start;
        $offsetEnd = $start + $length;

        foreach ($this->getCharacterList() as $key => $char) {
            if ($key > $offsetStart && $key < $offsetEnd) {
                $char->applyStyle($addStyle);
            }
        }
    }

    /**
     * Removes the given style from the given text range.
     *
     * @Counterpart None
     *
     * @Notes
     * Similar to draft.js Modifier::removeInlineStyle (https://facebook.github.io/draft-js/docs/api-reference-modifier.html#removeinlinestyle)
     * Remove the specified inline style from the entire selected range.
     *
     * @param int         $start
     * @param int         $length
     * @param string|null $removeStyle
     */
    public function __removeStyleFromRange(int $start, int $length, string $removeStyle = null)
    {
        $offsetStart = $start;
        $offsetEnd = $start + $length;

        foreach ($this->getCharacterList() as $key => $char) {
            if ($key > $offsetStart && $key < $offsetEnd) {
                $char->removeStyle($removeStyle);
            }
        }
    }

    /**
     * This method returns found ranges with the correct offset (MultiByte charset aware) by the given regex.
     * Don't use preg_* functions on ->getText() when you need the offset to manipulate the ContentBlock because
     * the offset returned by this functions is always the byte offset not the character offset!
     *
     * @Counterpart None
     *
     * @param string $pattern
     *
     * @return array
     */
    public function __getRangesByRegex($pattern)
    {
        $pattern = (string) $pattern;
        $ranges = [];
        $text = $this->getText();

        $getRealOffset = function ($byteOffset) use ($text) {
            return mb_strlen(substr($text, 0, $byteOffset));
        };

        $matchCollection = null;
        $foundAmount = preg_match_all('/'.$pattern.'/mu', $text, $matchCollection, PREG_OFFSET_CAPTURE);
        $matches = $matchCollection[0];

        for ($i = 0; $i < $foundAmount; ++$i) {
            $match = $matches[$i];
            $startOffsetMatch = $getRealOffset($match[1]);
            $realMatchLength = mb_strlen($match[0]);
            $endOffsetMatch = $realMatchLength + $startOffsetMatch;

            /*dump([
                'pattern' => $pattern,
                'match' => $match,
                'realMatchLength' => $realMatchLength,
                'strlenMatch' => strlen($match[0]),
            ]);*/

            $ranges[] = [$startOffsetMatch, $endOffsetMatch];
        }

        return $ranges;
    }

    /**
     * Because the debugging of the ContentBlock can be really difficult this magic function helps a lot.
     * It returns the CharacterMetadata[] mapped to the character in text (MultiByte aware).
     *
     * Additionally:
     * - Text
     * - Characters
     *   - Pad the output for better readability
     *   - Get character encoding and byte size
     *   - Character in formatted hex, bit map and decimal format
     *   - The characters entity, styles from it CharacterMetadata
     *
     * @return array
     */
    public function __debugInfo()
    {
        $text = $this->getText();
        $textRealLength = $this->getLength();

        $charList = $this->getCharacterList();
        $charListCount = count($charList);

        $charsDebugData = [];
        $iterations = max($charListCount, $textRealLength);

        // reuse in the other functions
        $getHexByteArray = function ($str) {
            return str_split(unpack('H*', $str)[1], 2);
        };

        // get array of bytes represented as hex values
        $strToHexFormatted = function ($str) use ($getHexByteArray) {
            return implode(' ', $getHexByteArray($str));
        };

        // get array of bytes represented as binary values
        $strToBinFormatted = function ($str) use ($getHexByteArray) {
            $hexes = $getHexByteArray($str);
            $bins = array_map(function ($hex) {
                // pad left bits with 0
                return str_pad(base_convert($hex, 16, 2), 8, '0', STR_PAD_LEFT);
            }, $hexes);

            return implode(' ', $bins);
        };

        // get array of bytes represented as decimal values
        $strToDecFormatted = function ($str) use ($getHexByteArray) {
            $hexes = $getHexByteArray($str);
            $decs = array_map(function ($hex) {
                return base_convert($hex, 16, 10);
            }, $hexes);

            return implode(' ', $decs);
        };

        for ($i = 0; $i < $iterations; ++$i) {
            $characterString = mb_substr($text, $i, 1);
            $bytes = strlen($characterString);

            $charDebug = [];

            // The character itself
            $charDebug[] = '\''.$characterString.'\'';

            // Detected encoding + Bytes of the character
            $charDebug[] = mb_detect_encoding($characterString).' ('.$bytes.'B)';

            // Hex representation of the character
            $charDebug[] = '['.str_pad($strToHexFormatted($characterString), 11, ' ').']';

            // Binary representation of the character
            $charDebug[] = '['.str_pad($strToBinFormatted($characterString), 35, ' ').']';

            // Decimal representation of the character
            $charDebug[] = '['.str_pad($strToDecFormatted($characterString), 15, ' ').']';

            if (isset($charList[$i])) {
                $entity = $charList[$i]->getEntity();

                $charDebug[] = 'E: '.str_pad(($entity !== null ? $entity : 'null'), 4);
                $charDebug[] = 'S: '.implode(',', $charList[$i]->getStyle());
            }

            $charsDebugData[str_pad($i, 4, ' ')] = implode(' - ', $charDebug);
        }

        $debugData = [
            'chars' => $charsDebugData,
            'real_text_length' => $textRealLength,
            'charList_count' => $charListCount,
            'text' => $this->getText(),
            'text_in_bytes' => strlen($text),
            'depth' => $this->getDepth(),
            'key' => $this->getKey(),
        ];

        if ($textRealLength !== $charListCount) {
            $debugData['ERROR'] = 'THE SIZE OF THE CHARACTER METADATA ARRAY MISMATCH THE REAL TEXT LENGTH!';
        }

        return $debugData;
    }

    /**
     * @param $startOffset
     * @param $endOffset
     *
     * @throws DraftException
     */
    private function assertOffsets($startOffset, $endOffset)
    {
        $textLength = $this->getLength();

        if ($startOffset < 0 || $startOffset > $textLength) {
            throw new DraftException(
                'Cannot insert/replace/remove text in content block because startOffset must be a number '.
                "between 0 and text length ${textLength}. Given startOffset: ${startOffset}."
            );
        }

        if ($endOffset < 0 || $endOffset > $textLength) {
            throw new DraftException(
                'Cannot insert/replace/remove text in content block because endOffset must be a number '.
                "between 0 and text length ${textLength}. Given endOffset: ${endOffset}."
            );
        }

        if ($startOffset > $endOffset) {
            throw new DraftException(
                'Cannot insert/replace/remove text in content block because endOffset must be a number '.
                "greater than startOffset. Given startOffset: ${startOffset} / endOffset ${endOffset}."
            );
        }
    }
}
