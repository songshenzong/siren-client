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
     * @var array
     */
    protected static $backtrace;


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
     * @param        $module
     * @param        $interface
     * @param int    $code
     * @param string $message
     *
     * @return bool
     */
    public static function success($module, $interface, $code = 0, $message = 'success')
    {
        return self::report($module, $interface, 1, $code, $message);
    }


    /**
     * @param       $module
     * @param       $interface
     * @param       $code
     * @param       $message
     *
     * @return bool
     */
    public static function error($module, $interface, $code, $message)
    {
        if (self::$backtrace === null) {
            self::$backtrace = debug_backtrace();
        }

        $file = self::$backtrace[0]['file'] ?? 'file';
        $line = self::$backtrace[0]['line'] ?? 'line';

        $information = "$code:$message called at [{$file}:{{$line}]";

        return self::report($module, $interface, 0, $code, $information);
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
     *
     * @return bool
     */
    public static function exception($module, $interface, Exception $exception)
    {
        $message = "{$exception->getMessage()} called at [{$exception->getFile()}:{$exception->getLine()}]";
        return self::report($module, $interface, 0, $exception->getCode(), $message);
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


if (PHP_SAPI == 'cli' && isset($argv[0]) && $argv[0] == basename(__FILE__)) {
    date_default_timezone_set('Asia/Chongqing');

    // Set the server and port, the default value is 127.0.0.1:55656
    StatisticClient::setAddress('127.0.0.1', 55656);

    // Module and interface consumption time statistics
    StatisticClient::tick('User', 'destroyToken');


    StatisticClient::success('User', 'destroyToken');

    StatisticClient::error('User', 'destroyToken', 200, 'User 1 token failed to destroy');


    // If Exception
    try {
        throw new Exception('Message');
    } catch (Exception $exception) {
        StatisticClient:: exception('System', 'Exception', $exception);
    }

}




