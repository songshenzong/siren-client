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
use const JSON_UNESCAPED_UNICODE;


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
        if (\strlen($message->interface) > self::MAX_CHAR_VALUE) {
            $message->interface = substr($message->interface, 0, self::MAX_CHAR_VALUE);
        }

        // 不成功就搜集现在的请求参数
        if (!$message->success) {
            $message->request = [
                'REQUEST_SCHEME'  => isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http',
                'HTTP_HOST'       => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
                'REQUEST_URI'     => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                'HTTP_USER_AGENT' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            ];
            $message->request = json_encode($message->request, JSON_UNESCAPED_UNICODE);
        }


        // 防止msg过长
        $token_length          = \strlen($message->token);
        $request_length        = \strlen($message->request);
        $file_length           = \strlen($message->file);
        $module_name_length    = \strlen($message->module);
        $interface_name_length = \strlen($message->interface);
        $available_size        = self::MAX_UDP_PACKAGE_SIZE
                                 - self::PACKAGE_FIXED_LENGTH
                                 - $token_length
                                 - $request_length
                                 - $file_length
                                 - $module_name_length
                                 - $interface_name_length;


        if (\strlen($message->msg) > $available_size) {
            // 9184
            /** @var string $msg */
            $message->msg = substr($message->msg, 0, $available_size);
        }

        $msg_length = \strlen($message->msg);

        return pack('CnCCfCNnNcCn',
                    $token_length,
                    $request_length,
                    $module_name_length,
                    $interface_name_length,
                    $message->cost_time,
                    $message->success ? 1 : 0,
                    $message->code,
                    $msg_length,
                    time(),
                    $message->alert,
                    $message->line,
                    $file_length
               ) . $message->token . $message->request . $message->module . $message->interface . $message->msg . $message->file;
    }


    /**
     * 解包
     *
     * @param $bin_data
     *
     * @return SirenMessage
     */
    public static function decode($bin_data)
    {
        // 解包
        $data = unpack('Ctoken_length/nrequest_length/Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime/calert/Cline/nfile_len', $bin_data);


        $sirenMessage = new SirenMessage();

        if (isset($data['token_length'])) {
            $sirenMessage->token = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['token_length']);
        } else {
            return $sirenMessage;
        }


        if (!isset($data['success'])) {
            return $sirenMessage;
        }


        if (!$data['success']) {
            $sirenMessage->request = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                       + $data['token_length'],
                                            $data['request_length']);


            $sirenMessage->msg = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                   + $data['token_length']
                                                   + $data['request_length']
                                                   + $data['module_name_len']
                                                   + $data['interface_name_len'],
                                        $data['msg_len']);


            $sirenMessage->file = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                    + $data['token_length']
                                                    + $data['request_length']
                                                    + $data['module_name_len']
                                                    + $data['interface_name_len']
                                                    + $data['msg_len'],
                                         $data['file_len']);
        }


        $sirenMessage->module = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                  + $data['token_length']
                                                  + $data['request_length'],
                                       $data['module_name_len']);

        $sirenMessage->interface = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                                     + $data['token_length']
                                                     + $data['request_length']
                                                     + $data['module_name_len'],
                                          $data['interface_name_len']);


        $sirenMessage->cost_time = $data['cost_time'];
        $sirenMessage->success   = $data['success'];
        $sirenMessage->time      = $data['time'];
        $sirenMessage->code      = $data['code'];
        $sirenMessage->alert     = $data['alert'];
        $sirenMessage->line      = $data['line'];
        return $sirenMessage;
    }
}
