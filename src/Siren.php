<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace Protocols;

use Songshenzong\SirenClient\SirenMessage;


/**
 * Class Siren
 *
 * @package Protocols
 */
class Siren
{


    /**
     * 包头长度
     *
     * @var integer
     */
    const PACKAGE_FIXED_LENGTH = 24;

    /**
     * udp 包最大长度
     *
     * @var integer
     */
    const MAX_UDP_PACKAGE_SIZE = 65507;

    /**
     * char类型能保存的最大数值
     *
     * @var integer
     */
    const MAX_CHAR_VALUE = 255;


    /**
     *  unsigned short 能保存的最大数值
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

        // 防止模块名过长
        if (\strlen($message->module) > self::MAX_CHAR_VALUE) {
            $message->module = substr($message->module, 0, self::MAX_CHAR_VALUE);
        }

        // 防止接口名过长
        if (\strlen($message->submodule) > self::MAX_CHAR_VALUE) {
            $message->submodule = substr($message->submodule, 0, self::MAX_CHAR_VALUE);
        }

        if (!$message->success) {
            $message->request = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : '';
            $message->request .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $message->request .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        }


        // 防止msg过长
        $token_len      = \strlen($message->token);
        $request_len    = \strlen($message->request);
        $file_len       = \strlen($message->file);
        $module_len     = \strlen($message->module);
        $submodule_len  = \strlen($message->submodule);
        $available_size = self::MAX_UDP_PACKAGE_SIZE
                          - self::PACKAGE_FIXED_LENGTH
                          - $token_len
                          - $request_len
                          - $file_len
                          - $module_len
                          - $submodule_len;


        if (\strlen($message->msg) > $available_size) {
            // 9184
            /** @var string $msg */
            $message->msg = substr($message->msg, 0, $available_size);
        }

        $msg_len = \strlen($message->msg);

        return pack('CnCCfCNnNcCn',
                    $token_len,
                    $request_len,
                    $module_len,
                    $submodule_len,
                    $message->cost_time,
                    $message->success ? 1 : 0,
                    $message->code,
                    $msg_len,
                    time(),
                    $message->alert,
                    $message->line,
                    $file_len
               ) . $message->token . $message->request . $message->module . $message->submodule . $message->msg . $message->file;
    }


    /**
     *
     * @param $bin_data
     *
     * @return SirenMessage
     */
    public static function decode($bin_data)
    {
        $data = unpack('Ctoken_len/nrequest_len/Cmodule_len/Csubmodule_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime/calert/Cline/nfile_len', $bin_data);


        $sirenMessage = new SirenMessage();

        if (!isset($data['token_len'],
            $data['request_len'],
            $data['module_len'],
            $data['submodule_len'],
            $data['cost_time'],
            $data['success'],
            $data['code'],
            $data['msg_len'],
            $data['time'],
            $data['alert'],
            $data['line'],
            $data['file_len'])) {

            return $sirenMessage;
        }

        $sirenMessage->token = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['token_len']);


        if (!$data['success']) {
            $sirenMessage->request = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                       + $data['token_len'],
                                            $data['request_len']);


            $sirenMessage->msg = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                   + $data['token_len']
                                                   + $data['request_len']
                                                   + $data['module_len']
                                                   + $data['submodule_len'],
                                        $data['msg_len']);


            $sirenMessage->file = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                    + $data['token_len']
                                                    + $data['request_len']
                                                    + $data['module_len']
                                                    + $data['submodule_len']
                                                    + $data['msg_len'],
                                         $data['file_len']);
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
        $sirenMessage->success   = $data['success'];
        $sirenMessage->time      = $data['time'];
        $sirenMessage->code      = $data['code'];
        $sirenMessage->alert     = $data['alert'];
        $sirenMessage->line      = $data['line'];
        return $sirenMessage;
    }
}
