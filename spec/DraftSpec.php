<?php

namespace spec\Draft;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DraftSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Draft\Draft');
    }
}
