<?php
namespace Draft;

class Defaults
{
    const DEFAULT_BLOCK_TYPE = 'unstyled';

    const INLINE_STYLES = [
        'BOLD',
        'CODE',
        'ITALIC',
        'STRIKETHROUGH',
        'UNDERLINE',
    ];

    const BLOCK_TYPES = [
        'header-one',
        'header-two',
        'header-three',
        'header-four',
        'header-five',
        'header-six',
        'unordered-list-item',
        'ordered-list-item',
        'blockquote',
        'atomic',
        'code-block',
        'unstyled',
    ];

    const LIST_BLOCK_TYPES = [
        'unordered-list-item',
        'ordered-list-item',
    ];
}
