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
     * @param int $offset
     * @param int $length
     * @param string $replacement
     *
     * @return string
     */
    public static function replaceOffsetMultiByte(string $text, int $offset, int $length = 0, string $replacement = '') {
        return mb_substr($text, 0, $offset).$replacement.mb_substr($text, $offset + $length);
    }
}
