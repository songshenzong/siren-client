<?php

namespace Songshenzong\Siren;

use Songshenzong\Siren\Traits\PacketTrait;

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
        $this->type       = SIREN_TYPE_SUCCESS;
        $this->request_id = Siren::getRequestId();
        $this->time       = microtime(true);
    }
}

