<?php

namespace Songshenzong\SirenClient;


/**
 * Class Siren
 *
 * @package Protocols
 */
class UdpProtocol
{


    /**
     * PACKAGE_FIXED_LENGTH
     *
     * @var integer
     */
    public const PACKAGE_FIXED_LENGTH = 24;

    /**
     * MAX_UDP_PACKAGE_SIZE
     *
     * @var integer
     */
    public const MAX_UDP_PACKAGE_SIZE = 65507;

    /**
     * MAX_CHAR_VALUE
     *
     * @var integer
     */
    public const MAX_CHAR_VALUE = 255;


    /**
     *  MAX_UNSIGNED_SHORT_VALUE
     *
     * @var integer
     */
    public const MAX_UNSIGNED_SHORT_VALUE = 65535;


    /**
     * @param Packet $packet
     *
     * @return string
     */
    public static function encode(Packet $packet): string
    {

        $packet->module    = mb_strcut($packet->module, 0, self::MAX_CHAR_VALUE);
        $packet->submodule = mb_strcut($packet->submodule, 0, self::MAX_CHAR_VALUE);


        if ($packet->type !== Packet::TYPE_SUCCESS) {
            $packet->request .= isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : '';
            $packet->request .= $_SERVER['HTTP_HOST'] ?? '';
            $packet->request .= $_SERVER['REQUEST_URI'] ?? '';
            if ($packet->request === '://') {
                $packet->request = '';
            }
        }


        $token_len      = \strlen($packet->token);
        $request_len    = \strlen($packet->request);
        $file_len       = \strlen($packet->file);
        $module_len     = \strlen($packet->module);
        $submodule_len  = \strlen($packet->submodule);
        $available_size = self::MAX_UDP_PACKAGE_SIZE
                          - self::PACKAGE_FIXED_LENGTH
                          - $token_len
                          - $request_len
                          - $file_len
                          - $module_len
                          - $submodule_len;


        if (\strlen($packet->msg) > $available_size) {
            $packet->msg = substr($packet->msg, 0, $available_size);
        }
        $msg_len = \strlen($packet->msg);

        return pack('CnCCfCNnNcCn',
                    $token_len,
                    $request_len,
                    $module_len,
                    $submodule_len,
                    $packet->cost_time,
                    $packet->type,
                    $packet->code,
                    $msg_len,
                    time(),
                    $packet->alert,
                    $packet->line,
                    $file_len
               )
               . $packet->token
               . $packet->request
               . $packet->module
               . $packet->submodule
               . $packet->msg
               . $packet->file;
    }
}
