# Draft.php

A simple library to handle the content state of Draft.js in the backend.

[![Build Status](https://travis-ci.org/webstronauts/draft-php.svg?branch=master)](https://travis-ci.org/webstronauts/draft-php)

## Usage

```php
use Draft\ContentState;
use Draft\Encoding;

$rawState = json_decode('{"entityMap":{"0":{"type":"LINK","mutability":"MUTABLE","data":{"url":"/","rel":null,"title":"hi","extra":"foo"}}},"blocks":[{"key":"8r91j","text":"a","type":"unstyled","depth":0,"inlineStyleRanges":[{"offset":0,"length":1,"style":"ITALIC"}],"entityRanges":[{"offset":0,"length":1,"key":0}]}]}', true);

$contentBlocks = Encoding::convertFromRaw($rawState);
$contentState = ContentState::createFromBlockArray($contentBlocks);

var_dump($contentState);
```