<?php

namespace Songshenzong\StatisticClient;

use Exception;

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
    protected static $ip = '127.0.0.1';

    /**
     * @var string
     */
    protected static $port = 55656;

    /**
     * @var string
     */
    protected static $module;

    /**
     * @var string
     */
    protected static $interface;

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
        self::$module                       = $module;
        self::$interface                    = $interface;
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
     * 上报统计数据
     *
     * @param string $module
     * @param string $interface
     * @param bool   $success
     * @param int    $code
     * @param string $msg
     * @param string $report_address
     *
     * @return boolean
     */
    public static function report($module, $interface, $success, $code, $msg, $report_address = '')
    {

        $module         = $module ? $module : 'module';
        $interface      = $interface ? $interface : 'interface';
        $report_address = $report_address ? $report_address : 'udp://' . self::$ip . ':' . self::$port;

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

        $bin_data = StatisticProtocol::encode($module, $interface, $cost_time, $success, $code, $msg);

        return self::sendData($report_address, $bin_data);
    }


    /**
     * @param $code
     * @param $msg
     *
     * @return bool
     */
    public static function success($code = 0, $msg = 'success')
    {
        return self::report(self::$module, self::$interface, 1, $code, $msg);
    }


    /**
     * @param       $code
     * @param array ...$args
     *
     * @return bool
     */
    public static function error($code, ...$args)
    {

        $result = false;
        foreach (debug_backtrace() as $key => $bt) {
            $parameters = implode(', ', $args);

            $information = "#$key  $parameters called at [{$bt['file']}:{$bt['line']}]";

            $result = self::report(self::$module, self::$interface, 0, $code, $information);
        }
        return $result;

    }


    /**
     * @param Exception $exception
     *
     * @return bool
     */
    public static function exception(Exception $exception)
    {
        $message = "{$exception->getMessage()} called at [{$exception->getFile()}:{$exception->getLine()}]";
        return self::report(self::$module, self::$interface, 0, $exception->getCode(), $message);
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
        return stream_socket_sendto($socket, $buffer) == strlen($buffer);
    }

}

/**
 *
 * Structural statisticProtocol
 * {
 *     unsigned char module_name_len;
 *     unsigned char interface_name_len;
 *     float cost_time;
 *     unsigned char success;
 *     int code;
 *     unsigned short msg_len;
 *     unsigned int time;
 *     char[module_name_len] module_name;
 *     char[interface_name_len] interface_name;
 *     char[msg_len] msg;
 * }
 *
 * @author workerman.net
 */
class StatisticProtocol
{
    /**
     * 包头长度
     *
     * @var integer
     */
    const PACKAGE_FIXED_LENGTH = 17;

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
     * @param string $module
     * @param string $interface
     * @param float  $cost_time
     * @param int    $success
     * @param int    $code
     * @param string $msg
     *
     * @return string
     */
    public static function encode($module, $interface, $cost_time, $success, $code = 0, $msg = '')
    {
        // 防止模块名过长
        if (strlen($module) > self::MAX_CHAR_VALUE) {
            $module = substr($module, 0, self::MAX_CHAR_VALUE);
        }

        // 防止接口名过长
        if (strlen($interface) > self::MAX_CHAR_VALUE) {
            $interface = substr($interface, 0, self::MAX_CHAR_VALUE);
        }

        // 防止msg过长
        $module_name_length    = strlen($module);
        $interface_name_length = strlen($interface);
        $available_size        = self::MAX_UDP_PACKAGE_SIZE - self::PACKAGE_FIXED_LENGTH - $module_name_length - $interface_name_length;


        if (strlen($msg) > $available_size) {
            // 9184
            $msg = substr($msg, 0, $available_size);
        }


        // 打包
        return pack('CCfCNnN', $module_name_length, $interface_name_length, $cost_time, $success ? 1 : 0, $code, strlen($msg), time()) . $module . $interface . $msg;
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
        $data      = unpack("Cmodule_name_len/Cinterface_name_len/fcost_time/Csuccess/Ncode/nmsg_len/Ntime", $bin_data);
        $module    = substr($bin_data, self::PACKAGE_FIXED_LENGTH, $data['module_name_len']);
        $interface = substr($bin_data, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'], $data['interface_name_len']);
        $msg       = substr($bin_data, self::PACKAGE_FIXED_LENGTH + $data['module_name_len'] + $data['interface_name_len']);
        return [
            'module'    => $module,
            'interface' => $interface,
            'cost_time' => $data['cost_time'],
            'success'   => $data['success'],
            'time'      => $data['time'],
            'code'      => $data['code'],
            'msg'       => $msg,
        ];
    }

}


if (PHP_SAPI == 'cli' && isset($argv[0]) && $argv[0] == basename(__FILE__)) {
    date_default_timezone_set('Asia/Chongqing');
    StatisticClient::setAddress('10.21.7.47', 55656);
    StatisticClient::tick('TestModule2', 'TestApi2');
    // usleep(rand(10000, 600000));
    $code = rand(300, 400);
    $msg  = str_repeat('我爱你中国啊', 120);


    var_export(StatisticClient::success());

    var_export(StatisticClient:: error($code, $msg, '可以有很多个参数'));;

    try {
        throw new Exception('New Exception');
    } catch (Exception $exception) {
        var_export(StatisticClient:: exception($exception));
    }

}


