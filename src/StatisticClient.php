<?php

namespace Songshenzong\SirenClient;
require_once 'Siren.php';

use Exception;
use Protocols\Siren;

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
 */
class SirenClient
{
    /**
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
     * @var string
     */
    public static $token = '';


    /**
     * @var array
     */
    public static $backtrace = [];


    /**
     * @var string
     */
    public static $module = '';

    /**
     * @var string
     */
    public static $interface = '';

    /**
     * @var float
     */
    public static $cost_time;

    /**
     * @var int
     */
    public static $success = 1;

    /**
     * @var int
     */
    public static $code;

    /**
     * @var string
     */
    public static $msg;

    /**
     * @var integer
     */
    public static $alert;

    /**
     * @var string
     */
    public static $file;

    /**
     * @var integer
     */
    public static $line;

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
     * 模块接口上报消耗时间记时
     *
     * @param string $module
     * @param string $interface
     *
     * @return void
     */
    public static function tick($module, $interface)
    {
        self::$timeMap[$module][$interface] = microtime(true);
    }


    /**
     * 上报统计数据
     *
     *
     * @return boolean
     */
    protected static function report()
    {

        if (self::$success) {
            self::$backtrace = [];
        }


        if (isset(self::$timeMap[self::$module][self::$interface]) && self::$timeMap[self::$module][self::$interface] > 0) {
            $time_start                                     = self::$timeMap[self::$module][self::$interface];
            self::$timeMap[self::$module][self::$interface] = 0;
        } elseif (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {
            $time_start            = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;
        } else {
            $time_start = microtime(true);
        }

        self::$cost_time = microtime(true) - $time_start;

        $report_address = 'udp://' . self::$ip . ':' . self::$port;
        $bin_data       = Siren::encode(self::class);
        return self::sendData($report_address, $bin_data);
    }


    /**
     * @param        $module
     * @param        $interface
     *
     * @return bool
     */
    public static function success($module, $interface)
    {
        self::$module    = $module;
        self::$interface = $interface;
        return self::report();
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
        self::$file      = isset(self::$backtrace[0]['file']) ? self::$backtrace[0]['file'] : '';
        self::$line      = isset(self::$backtrace[0]['line']) ? self::$backtrace[0]['line'] : '';
        self::$module    = $module;
        self::$interface = $interface;
        self::$success   = 0;
        self::$code      = $code;
        self::$msg       = $message;
        self::$alert     = $alert;
        return self::report();
    }


    /**
     * @param array $backtrace
     */
    public static function setBacktrace(array $backtrace)
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
        self::$file      = $exception->getFile();
        self::$line      = $exception->getLine();
        self::$module    = $module;
        self::$interface = $interface;
        self::$alert     = $alert;
        self::$success   = 0;
        self::$code      = $exception->getCode();
        self::$msg       = $exception->getMessage();
        return self::report();
    }


    /**
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
        return \strlen($buffer) === stream_socket_sendto($socket, $buffer);
    }

}
