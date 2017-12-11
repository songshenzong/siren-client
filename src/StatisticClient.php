<?php

namespace Songshenzong\StatisticClient;

use Exception;
use function json_decode;
use function strlen;

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

/**
 * 统计客户端
 *
 * @author workerman.net
 */
class StatisticClient
{
    /**
     * [module=>[interface=>time_start, interface=>time_start ...], module=>[interface=>time_start ..], ... ]
     *
     * @var array
     */
    protected static $timeMap = [];

    /**
     * Ip
     *
     * @var string
     */
    public static $ip = '127.0.0.1';

    /**
     * @var string
     */
    public static $port = 55656;


    /**
     * @var array
     */
    public static $backtrace = [];


    /**
     * @var string
     */
    public static $token = '';


    /**
     * @var string
     */
    public static $file = '';

    /**
     * @var string
     */
    public static $line = 0;

    /**
     * @var array
     */
    public static $request;

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
     * 编码
     *
     * @param        $token
     * @param        $module
     * @param        $interface
     * @param        $cost_time
     * @param        $success
     * @param int    $code
     * @param string $msg
     * @param int    $alert
     *
     * @return string
     */
    public static function encode($token, $module, $interface, $cost_time, $success, $code = 0, $msg = '', $alert)
    {
        // 防止模块名过长
        if (strlen($module) > self::MAX_CHAR_VALUE) {
            $module = substr($module, 0, self::MAX_CHAR_VALUE);
        }

        // 防止接口名过长
        if (strlen($interface) > self::MAX_CHAR_VALUE) {
            $interface = substr($interface, 0, self::MAX_CHAR_VALUE);
        }

        // 不成功就搜集现在的请求参数
        if (!$success) {
            $request = [
                'HTTP_HOST'       => $_SERVER['HTTP_HOST'] ?? '',
                'REQUEST_URI'     => $_SERVER['REQUEST_URI'] ?? '',
                'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ];
            $request = json_encode($request);
        } else {
            $request = '';
        }


        // 防止msg过长
        $token_length          = strlen($token);
        $request_length        = strlen($request);
        $file_length           = strlen(self::$file);
        $module_name_length    = strlen($module);
        $interface_name_length = strlen($interface);
        $available_size        = self::MAX_UDP_PACKAGE_SIZE
                                 - self::PACKAGE_FIXED_LENGTH
                                 - $token_length
                                 - $request_length
                                 - $file_length
                                 - $module_name_length
                                 - $interface_name_length;


        if (strlen($msg) > $available_size) {
            // 9184
            /** @var string $msg */
            $msg = substr($msg, 0, $available_size);
        }

        $msg_length = strlen($msg);

        // 打包
        return pack('CCCCfCNnNcCn',
                    $token_length,
                    $request_length,
                    $module_name_length,
                    $interface_name_length,
                    $cost_time,
                    $success ? 1 : 0,
                    $code,
                    $msg_length,
                    time(),
                    $alert,
                    self::$line,
                    $file_length
               ) . $token . $request . $module . $interface . $msg . self::$file;
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

        $request = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                     + $data['token_length']
            , $data['request_length']);

        $module = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                    + $data['token_length'],
                         +$data['request_length'],
                         $data['module_name_len']);

        $interface = substr($bin_data, self::PACKAGE_FIXED_LENGTH
                                       + $data['token_length']
                                       + $data['request_length'],
                            +$data['module_name_len'],
                            $data['interface_name_len']);


        $msg = substr($bin_data, self::PACKAGE_FIXED_LENGTH
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


        return [
            'token'     => $token,
            'request'   => $request,
            'module'    => $module,
            'interface' => $interface,
            'cost_time' => $data['cost_time'],
            'success'   => $data['success'],
            'time'      => $data['time'],
            'code'      => $data['code'],
            'alert'     => $data['alert'],
            'msg'       => $msg,
            'file'      => $file,
            'line'      => $data['line'],
        ];
    }


    /**
     * 模块接口上报消耗时间记时
     *
     * @param string $module
     * @param string $interface
     *
     * @return void
     */
    public static function tick($module = '', $interface = '')
    {
        self::$timeMap[$module][$interface] = microtime(true);
    }


    /**
     * @param $ip
     * @param $port
     */
    public static function setAddress($ip, $port = 55656)
    {
        self::$ip   = $ip;
        self::$port = $port;
    }

    /**
     * @param $token string
     */
    public static function setToken($token)
    {
        self::$token = $token;
    }

    /**
     * 上报统计数据
     *
     * @param string $module
     * @param string $interface
     * @param bool   $success
     * @param int    $code
     * @param string $message
     * @param int    $alert
     *
     * @return boolean
     */
    public static function report($module, $interface, $success, $code, $message = '', $alert = -1)
    {
        $report_address = 'udp://' . self::$ip . ':' . self::$port;

        if (isset(self::$timeMap[$module][$interface]) && self::$timeMap[$module][$interface] > 0) {
            $time_start                         = self::$timeMap[$module][$interface];
            self::$timeMap[$module][$interface] = 0;
        } elseif (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {
            $time_start            = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;
        } else {
            $time_start = microtime(true);
        }

        $cost_time = microtime(true) - $time_start;


        $bin_data = self::encode(self::$token, $module, $interface, $cost_time, $success, $code, $message, $alert);
        return self::sendData($report_address, $bin_data);
    }


    /**
     * @param        $module
     * @param        $interface
     * @param int    $code
     *
     * @return bool
     */
    public static function success($module, $interface, $code = 0)
    {
        self::$backtrace = [];
        self::$file      = '';
        self::$line      = '';
        return self::report($module, $interface, 1, $code);
    }


    /**
     * @param       $module
     * @param       $interface
     * @param       $code
     * @param       $message
     * @param       $alert
     *
     * @return bool
     */
    public static function error($module, $interface, $code, $message, $alert = 0)
    {
        if (self::$backtrace === null) {
            self::$backtrace = debug_backtrace();
        }

        self::$file = isset(self::$backtrace[0]['file'])
            ? self::$backtrace[0]['file']
            : '';

        self::$line = isset(self::$backtrace[0]['line'])
            ? self::$backtrace[0]['line']
            : '';


        return self::report($module, $interface, 0, $code, $message, $alert);
    }


    /**
     * Set backtrace
     *
     * @param $backtrace
     */
    public static function backtrace($backtrace)
    {
        self::$backtrace = $backtrace;
    }

    /**
     * @param           $module
     * @param           $interface
     * @param Exception $exception
     * @param int       $alert
     *
     * @return bool
     */
    public static function exception($module, $interface, Exception $exception, $alert = 0)
    {
        self::$file = $exception->getFile();
        self::$line = $exception->getLine();
        return self::report($module, $interface, 0, $exception->getCode(), $exception->getMessage(), $alert);
    }


    /**
     * 发送数据给统计系统
     *
     * @param string $address
     * @param string $buffer
     *
     * @return boolean
     */
    public static function sendData($address, $buffer)
    {
        $socket = stream_socket_client($address);
        if (!$socket) {
            return false;
        }
        return stream_socket_sendto($socket, $buffer) === strlen($buffer);
    }

}


if (PHP_SAPI === 'cli' && isset($argv[0]) && $argv[0] === basename(__FILE__)) {
    date_default_timezone_set('Asia/Chongqing');

    // Set the server and port, the default value is 127.0.0.1:55656
    StatisticClient::setAddress('127.0.0.1');

    // Module and interface consumption time statistics
    StatisticClient::tick('User', 'destroyToken');


    StatisticClient::success('User', 'destroyToken');

    StatisticClient::error('User', 'destroyToken', 200, 'User 1 token failed to destroy', -1);


    // If Exception
    try {
        throw new Exception('Message');
    } catch (Exception $exception) {
        StatisticClient:: exception('System', 'Exception', $exception);
    }

}




