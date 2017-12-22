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

use const JSON_UNESCAPED_UNICODE;
use Songshenzong\SirenClient\SirenClient;


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
    const PACKAGE_FIXED_LENGTH = 23;

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
     * @param  SirenClient $client
     *
     * @return string
     */
    public static function encode($client)
    {

        // 防止模块名过长
        if (\strlen($client::$module) > self::MAX_CHAR_VALUE) {
            $client::$module = substr($client::$module, 0, self::MAX_CHAR_VALUE);
        }

        // 防止接口名过长
        if (\strlen($client::$interface) > self::MAX_CHAR_VALUE) {
            $client::$interface = substr($client::$interface, 0, self::MAX_CHAR_VALUE);
        }

        // 不成功就搜集现在的请求参数
        if (!$client::$success) {
            $request = [
                'REQUEST_SCHEME'  => isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : 'http://',
                'HTTP_HOST'       => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
                'REQUEST_URI'     => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
                'HTTP_USER_AGENT' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            ];
            $request = json_encode($request, JSON_UNESCAPED_UNICODE);
        } else {
            $request = '';
        }


        // 防止msg过长
        $token_length          = \strlen($client::$token);
        $request_length        = \strlen($request);
        $file_length           = \strlen($client::$file);
        $module_name_length    = \strlen($client::$module);
        $interface_name_length = \strlen($client::$interface);
        $available_size        = self::MAX_UDP_PACKAGE_SIZE
                                 - self::PACKAGE_FIXED_LENGTH
                                 - $token_length
                                 - $request_length
                                 - $file_length
                                 - $module_name_length
                                 - $interface_name_length;


        if (\strlen($client::$msg) > $available_size) {
            // 9184
            /** @var string $msg */
            $client::$msg = substr($client::$msg, 0, $available_size);
        }

        $msg_length = \strlen($client::$msg);

        // 打包
        return pack('CCCCfCNnNcCn',
                    $token_length,
                    $request_length,
                    $module_name_length,
                    $interface_name_length,
                    $client::$cost_time,
                    $client::$success ? 1 : 0,
                    $client::$code,
                    $msg_length,
                    time(),
                    $client::$alert,
                    $client::$line,
                    $file_length
               ) . $client::$token . $request . $client::$module . $client::$interface . $client::$msg . $client::$file;
    }


    /**
     * 解包
     *
     * @param string $bin_data
     *
     * @return array
     */
    public static function decode($bin_data)
    {
        // 解包
        $data = unpack('Ctoken_length/Crequest_length/Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime/calert/Cline/nfile_len', $bin_data);

        $token = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['token_length']);

        if (!$data['success']) {
            $request = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                         + $data['token_length']
                , $data['request_length']);
            $msg     = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                         + $data['token_length']
                                         + $data['request_length']
                                         + $data['module_name_len']
                                         + $data['interface_name_len'],
                              $data['msg_len']);


            $file = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                      + $data['token_length']
                                      + $data['request_length']
                                      + $data['module_name_len']
                                      + $data['interface_name_len']
                                      + $data['msg_len'],
                           $data['file_len']);
        }


        $module = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                    + $data['token_length']
                                    + $data['request_length'],
                         $data['module_name_len']);

        $interface = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                       + $data['token_length']
                                       + $data['request_length']
                                       + $data['module_name_len'],
                            $data['interface_name_len']);


        return [
            'token'     => $token,
            'request'   => isset($request) ? $request : '',
            'module'    => $module,
            'interface' => $interface,
            'cost_time' => $data['cost_time'],
            'success'   => $data['success'],
            'time'      => $data['time'],
            'code'      => $data['code'],
            'alert'     => $data['alert'],
            'msg'       => isset($msg) ? $msg : '',
            'file'      => isset($file) ? $file : '',
            'line'      => $data['line']
        ];
    }
}
