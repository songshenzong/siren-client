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


        // if (\strlen($packet->msg) > $available_size) {
        //     $packet->msg = substr($packet->msg, 0, $available_size);
        // }

        $json       = \json_encode($packet);
        $packet_len = \strlen($json);
        return pack('n', $packet_len) . $json;
    }
}
