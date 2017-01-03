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

use PhpSpec\ObjectBehavior;

/**
 * @author Robin van der Vleuten <robin@webstronauts.co>
 */
class CharacterMetadataSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith(['ITALIC'], 0);
        $this->shouldHaveType('Draft\Model\Immutable\CharacterMetadata');

        $this->getStyle()->shouldReturn(['ITALIC']);
        $this->getEntity()->shouldReturn(0);
    }
}
