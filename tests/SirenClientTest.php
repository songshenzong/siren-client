<?php

namespace Songshenzong\SirenClient\Test;

use PHPUnit\Framework\TestCase;
use function dd;
use function sirenError;
use function sirenSetHost;
use function sirenSetPort;


class SirenClientTest extends TestCase
{

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function testReport(): void
    {
        sirenSetHost('127.0.0.1');
        sirenSetPort(55655);
        $this->assertTrue(sirenError('1', '1', 402, '11'));
    }

}
