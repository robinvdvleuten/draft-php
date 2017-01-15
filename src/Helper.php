<?php

namespace Draft;

/**
 * Class Helper.
 */
class Helper
{
    /**
     * @param string $text
     * @param int    $offset
     * @param int    $length
     * @param string $replacement
     *
     * @return string
     */
    public static function replaceOffsetMultiByte(
        $text,
        $offset,
        $length = null,
        $replacement = null
    ) {
        $offset = intval($offset);
        $length = intval($length);
        $text = (string) $text;

        return mb_substr($text, 0, $offset).$replacement.mb_substr($text, $offset + $length);
    }

    /**
     * @param $byteOffset
     * @param $text
     *
     * @return int
     */
    public static function getMultiByteOffset($byteOffset, $text) {
        return mb_strlen(substr($text, 0, $byteOffset));
    }

    /**
     * @param array  $match A match returned by preg_match* function with PREG_OFFSET_CAPTURE flag
     * @param string $text  The original text on which the regex was executed
     *
     * @return array [startOffset, endOffset]
     */
    public static function pregOffsetMatchToMultiByteOffsetRange(array $match, $text) {
        $text = (string) $text;

        $startOffsetMatch = Helper::getMultiByteOffset($match[1], $text);
        $realMatchLength = mb_strlen($match[0]);
        $endOffsetMatch = $realMatchLength + $startOffsetMatch;

        return [$startOffsetMatch, $endOffsetMatch];
    }
}
