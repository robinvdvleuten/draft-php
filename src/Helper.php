<?php
namespace Draft;

/**
 * Class Helper
 * @package Draft
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
}
