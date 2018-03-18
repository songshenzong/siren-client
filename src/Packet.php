<?php

namespace Songshenzong\Siren;

use Songshenzong\Siren\Traits\PacketTrait;
use function microtime;

/**
 * Class Packet
 *
 * @package Songshenzong\Siren
 */
class Packet
{
    use PacketTrait;


    /**
     * Packet constructor.
     */
    public function __construct()
    {
        $this->type = SIREN_TYPE_SUCCESS;
        $this->uuid = Siren::getUuid();
        $this->time = microtime(true);
    }


}

