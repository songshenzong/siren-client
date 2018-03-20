<?php

namespace Songshenzong\Siren;

/**
 * Class UdpProtocol
 *
 * @package Songshenzong\Siren
 */
class UdpProtocol
{

    /**
     * @param Packet $packet
     *
     * @return string
     */
    public static function encode(Packet $packet)
    {

        if ($packet->module) {
            $packet->module = mb_strcut($packet->module, 0, SIREN_MAX_MODULE_LENGTH);
        }

        if ($packet->submodule) {
            $packet->submodule = mb_strcut($packet->submodule, 0, SIREN_MAX_SUBMODULE_LENGTH);
        }

        if ($packet->msg) {
            $packet->msg = mb_strcut($packet->msg, 0, SIREN_MAX_MSG_LENGTH);
        }

        if ($packet->request) {
            $packet->request = mb_strcut($packet->request, 0, SIREN_MAX_REQUEST_LENGTH);
        }

        return pack('n', \strlen($packet)) . $packet;

    }
}
