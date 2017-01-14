<?php

/*
 * This file is part of the Draft.php library.
 *
 * (c) The Webstronauts <contact@webstronauts.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Draft\Model\Immutable;

use Draft\Model\Immutable\CharacterMetadata;
use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class ContentBlockSpec extends ObjectBehavior
{
    public function it_is_initializable_with_defaults()
    {
        $this->beConstructedWith('123', 'unstyled');

        $this->shouldHaveType('Draft\Model\Immutable\ContentBlock');

        $this->getKey()->shouldReturn('123');
        $this->getType()->shouldReturn('unstyled');
        $this->getText()->shouldReturn('');
        $this->getCharacterList()->shouldReturn([]);
        $this->getDepth()->shouldReturn(0);
    }

    public function it_is_initializable_with_custom_values()
    {
        $charList = array_fill(0, 13, new CharacterMetadata());
        $this->beConstructedWith('123', 'unstyled', 'Hello, Block!', $charList, 2);

        $this->shouldHaveType('Draft\Model\Immutable\ContentBlock');

        $this->getKey()->shouldReturn('123');
        $this->getType()->shouldReturn('unstyled');
        $this->getText()->shouldReturn('Hello, Block!');
        $this->getCharacterList()->shouldReturn($charList);
        $this->getDepth()->shouldReturn(2);
    }

    public function it_can_modify_the_text()
    {
        //$ori = "äöäöü - ,.-`^ — – \xC3\x85";
        //$ori = "A\xCC\x8A";
        //$ori = "w͢͢͝h͡o͢͡ ̸͢k̵͟n̴͘ǫw̸̛s͘ ̀́w͘͢ḩ̵a҉̡͢t ̧̕h́o̵r͏̵rors̡ ̶͡͠lį̶e͟͟ ̶͝in͢ ͏t̕h̷̡͟e ͟͟d̛a͜r̕͡k̢̨ ͡h̴e͏a̷̢̡rt́͏ ̴̷͠ò̵̶f̸ u̧͘ní̛͜c͢͏o̷͏d̸͢e̡͝?͞";
        //$nor = \Normalizer::normalize($ori, \Normalizer::FORM_KD);
        //dump(PHP_EOL, $ori, $nor, \Normalizer::isNormalized($ori));

        // Can't test emojis yet, since the IDE don't show it and distrupt the input

        $this->beConstructedWith('123', 'unstyled', 'This is a vƐry proud test.   ', [
            new CharacterMetadata(['BOLD'], 0),                 // 0 T / ENTITY 0 / recognize character in first character
            new CharacterMetadata([], 0),                       // 1 h / ENTITY 0
            new CharacterMetadata([], 0),                       // 2 i / ENTITY 0
            new CharacterMetadata([], 0),                       // 3 s / ENTITY 0
            new CharacterMetadata([], null),                    // 4' '
            new CharacterMetadata(['BOLD'], null),              // 5 i
            new CharacterMetadata(['BOLD'], null),              // 6 s
            new CharacterMetadata([], null),                    // 7' '
            new CharacterMetadata(['BOLD'], null),              // 8 a
            new CharacterMetadata([], null),                    // 9' '
            new CharacterMetadata(['BOLD', 'ITALIC'], null),    // 10 v
            new CharacterMetadata(['BOLD', 'ITALIC'], null),    // 11 e
            new CharacterMetadata(['ITALIC', 'BOLD'], null),    // 12 r
            new CharacterMetadata(['ITALIC', 'BOLD'], null),    // 13 y
            new CharacterMetadata([], null),                    // 14' '
            new CharacterMetadata(['BOLD'], null),              // 15 p
            new CharacterMetadata(['BOLD', 'ITALIC'], null),    // 16 r
            new CharacterMetadata(['BOLD', 'ITALIC', 'UNDERLINE'], null), // 17 o
            new CharacterMetadata([], null),                    // 18 u
            new CharacterMetadata([], null),                    // 19 d
            new CharacterMetadata([], null),                    // 20' '
            new CharacterMetadata([], 0),                       // 21 t / ENTITY 0
            new CharacterMetadata([], 0),                       // 22 e / ENTITY 0
            new CharacterMetadata([], 0),                       // 23 s / ENTITY 0
            new CharacterMetadata([], 0),                       // 24 t / ENTITY 0
            new CharacterMetadata(['BOLD'], null),              // 25 . - recognize style in last character
            new CharacterMetadata([], null),                    // 26 ' ' - empty space
            new CharacterMetadata([], null),                    // 27 ' ' - empty space
            new CharacterMetadata([], null),                    // 28 ' ' - empty space
        ], 2);

        $multiByteRichString = ' coÔØÒool-👍😄';

        $this->__replaceText(9, 13, $multiByteRichString, ['UNDERLINE'], 1);

        $this->__getRangesByRegex($multiByteRichString)->shouldReturn([
            [9, 21],
        ]);

        // Reduce multiple spaces to one
        foreach ($this->__getRangesByRegex('[ ]{2,}')->getWrappedObject() as $range) {
            $this->__replaceText($range[0], $range[1], ' ');
        }

        // Append a text with 4 spaces at the end
        $this->__insertText($this->getLength(), 'HERE AGAIN A TEXT    ', ['ITALIC'], 6);

        // Prepend 3 spaces at the beginning
        $this->__insertText(0, '   IMPORTANT: ', ['BOLD'], null);

        // Trim leading spaces by regex
        foreach ($this->__getRangesByRegex('^[ ]{1,}')->getWrappedObject() as $range) {
            $this->__replaceText($range[0], $range[1], '');
        }

        // Trim trailing spaces by regex
        foreach ($this->__getRangesByRegex('[ ]{1,}$')->getWrappedObject() as $range) {
            $this->__replaceText($range[0], $range[1], '');
        }

        $this->__getRangesByRegex($multiByteRichString)->shouldReturn([
            [20, 32],
        ]);

        // Find the text range of the multi byte charset string which we inserted at the beginning
        foreach ($this->__getRangesByRegex($multiByteRichString)->getWrappedObject() as $range) {
            $this->__removeText($range[0], $range[1]);
        }

        $this->__insertText($this->getLength(), ' “MÄ!€”😄');

        //dump($this->getWrappedObject());
    }
}
