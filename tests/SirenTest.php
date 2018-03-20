<?php

namespace Songshenzong\Siren\Test;

use PHPUnit\Framework\TestCase;
use Songshenzong\Siren\Siren;

class SirenTest extends TestCase
{

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testError()
    {
        $this->assertFalse(Siren::error('1', '1', '1'));
    }

}
