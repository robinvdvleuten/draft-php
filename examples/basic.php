<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__DIR__).'/vendor/autoload.php';

use Draft\Encoding;
use Draft\Model\Immutable\ContentState;

$rawState = json_decode('{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/","rel":null,"title":"hi","extra":"foo"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}', true);

$contentBlocks = Encoding::convertFromRaw($rawState);
$contentState = ContentState::createFromBlockArray($contentBlocks);

var_dump($contentState);
