<?php

namespace Protocols;

use Exception;
use Songshenzong\SirenClient\SirenMessage;
use function sirenException;


/**
 * Class Siren
 *
 * @package Protocols
 */
class Siren
{


    /**
     * PACKAGE_FIXED_LENGTH
     *
     * @var integer
     */
    const PACKAGE_FIXED_LENGTH = 24;

    /**
     * MAX_UDP_PACKAGE_SIZE
     *
     * @var integer
     */
    const MAX_UDP_PACKAGE_SIZE = 65507;

    /**
     * MAX_CHAR_VALUE
     *
     * @var integer
     */
    const MAX_CHAR_VALUE = 255;


    /**
     *  MAX_UNSIGNED_SHORT_VALUE
     *
     * @var integer
     */
    const MAX_UNSIGNED_SHORT_VALUE = 65535;


    /**
     * @param SirenMessage $message
     *
     * @return string
     */
    public static function encode(SirenMessage $message)
    {

        $message->module    = mb_strcut($message->module, 0, self::MAX_CHAR_VALUE);
        $message->submodule = mb_strcut($message->submodule, 0, self::MAX_CHAR_VALUE);


        if ($message->type !== SirenMessage::TYPE_SUCCESS) {
            $message->request .= isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : '';
            $message->request .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $message->request .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            if ($message->request === '://') {
                $message->request = '';
            }
        }


        $token_len      = strlen($message->token);
        $request_len    = strlen($message->request);
        $file_len       = strlen($message->file);
        $module_len     = strlen($message->module);
        $submodule_len  = strlen($message->submodule);
        $available_size = self::MAX_UDP_PACKAGE_SIZE
                          - self::PACKAGE_FIXED_LENGTH
                          - $token_len
                          - $request_len
                          - $file_len
                          - $module_len
                          - $submodule_len;


        if (strlen($message->msg) > $available_size) {
            $message->msg = substr($message->msg, 0, $available_size);
        }
        $msg_len = strlen($message->msg);

        return pack('CnCCfCNnNcCn',
                    $token_len,
                    $request_len,
                    $module_len,
                    $submodule_len,
                    $message->cost_time,
                    $message->type,
                    $message->code,
                    $msg_len,
                    time(),
                    $message->alert,
                    $message->line,
                    $file_len
               ) . $message->token . $message->request . $message->module . $message->submodule . $message->msg . $message->file;
    }


    /**
     * @param $bin_data
     *
     * @return SirenMessage
     */
    public static function decode($bin_data)
    {
        $data = unpack('Ctoken_len/nrequest_len/Cmodule_len/Csubmodule_len/fcost_time/Ctype/Ncode/nmsg_len/Ntime/calert/Cline/nfile_len', $bin_data);


        $sirenMessage = new SirenMessage();

        try {

            $sirenMessage->token = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['token_len']);
            if (!$sirenMessage->token) {
                return $sirenMessage;
            }


            if ($data['type'] === SirenMessage::TYPE_ERROR) {
                $sirenMessage->request = substr($bin_data,
                                                self::PACKAGE_FIXED_LENGTH + $data['token_len'],
                                                $data['request_len']);


                $sirenMessage->file = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                        + $data['token_len']
                                                        + $data['request_len']
                                                        + $data['module_len']
                                                        + $data['submodule_len']
                                                        + $data['msg_len'],
                                             $data['file_len']);
            }


            if ($data['type'] !== SirenMessage::TYPE_SUCCESS) {
                $sirenMessage->msg = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                       + $data['token_len']
                                                       + $data['request_len']
                                                       + $data['module_len']
                                                       + $data['submodule_len'],
                                            $data['msg_len']);
            }


            $sirenMessage->module = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                      + $data['token_len']
                                                      + $data['request_len'],
                                           $data['module_len']);

            $sirenMessage->submodule = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                         + $data['token_len']
                                                         + $data['request_len']
                                                         + $data['module_len'],
                                              $data['submodule_len']);


            $sirenMessage->cost_time = $data['cost_time'];
            $sirenMessage->type      = $data['type'];
            $sirenMessage->time      = $data['time'];
            $sirenMessage->code      = $data['code'];
            $sirenMessage->alert     = $data['alert'];
            $sirenMessage->line      = $data['line'];
            $sirenMessage->module    = mb_strcut($sirenMessage->module, 0, self::MAX_CHAR_VALUE);
            $sirenMessage->submodule = mb_strcut($sirenMessage->submodule, 0, self::MAX_CHAR_VALUE);

        } catch (Exception $exception) {
            sirenException($exception);
            return $sirenMessage;
        }


        return $sirenMessage;
    }
}
