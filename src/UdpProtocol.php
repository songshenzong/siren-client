<?php

namespace Songshenzong\Siren;

use const SIREN_MAX_MODULE_LENGTH;
use const SIREN_MAX_MSG_LENGTH;
use const SIREN_MAX_SUBMODULE_LENGTH;


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

        $packet->module    = mb_strcut($packet->module, 0, SIREN_MAX_CHAR_VALUE);
        $packet->submodule = mb_strcut($packet->submodule, 0, SIREN_MAX_CHAR_VALUE);


        if ($packet->type !== SIREN_TYPE_SUCCESS) {
            $packet->request .= isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : '';
            $packet->request .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $packet->request .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            if ($packet->request === '://') {
                $packet->request = '';
            }
        }

        if ($packet->module) {
            $packet->module = mb_strcut($packet->module, 0, SIREN_MAX_MODULE_LENGTH);
        }

        if ($packet->submodule) {
            $packet->submodule = mb_strcut($packet->submodule, 0, SIREN_MAX_SUBMODULE_LENGTH);
        }

        if ($packet->msg) {
            $packet->msg = mb_strcut($packet->msg, 0, SIREN_MAX_MSG_LENGTH);
        }

        return pack('n', \strlen($packet)) . $packet;
    }
}
