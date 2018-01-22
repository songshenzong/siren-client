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
 * Class SirenClient
 *
 * @package Songshenzong\SirenClient
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
    public static $backtrace;


    /**
     * @param $ip
     * @param $port
     */
    public static function setHost($ip = '127.0.0.1', $port = 55656)
    {
        if ($ip !== null) {
            self::$ip = $ip;
        }
        if ($port !== null) {
            self::$port = $port;
        }
    }

    /**
     * @param $token string
     */
    public static function setToken($token = '')
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
     *
     * 上报统计数据
     *
     * @param SirenMessage $siren_message
     *
     * @return bool
     */
    public static function report(SirenMessage $siren_message)
    {
        self::$backtrace = null;

        if (isset(self::$timeMap[$siren_message->module][$siren_message->interface]) && self::$timeMap[$siren_message->module][$siren_message->interface] > 0) {

            $time_start = self::$timeMap[$siren_message->module][$siren_message->interface];

            self::$timeMap[$siren_message->module][$siren_message->interface] = 0;

        } elseif (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {

            $time_start            = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;

        } else {

            $time_start = microtime(true);

        }

        $siren_message->token     = self::$token;
        $siren_message->cost_time = microtime(true) - $time_start;


        $report_address = 'udp://' . self::$ip . ':' . self::$port;
        $bin_data       = Siren::encode($siren_message);
        $socket         = stream_socket_client($report_address);
        if (!$socket) {
            return false;
        }
        return \strlen($bin_data) === stream_socket_sendto($socket, $bin_data);
    }


    /**
     * @param        $module
     * @param        $interface
     *
     * @return bool
     */
    public static function success($module, $interface)
    {
        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = $module;
        $sirenMessage->interface = $interface;
        return self::report($sirenMessage);
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

        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = $module;
        $sirenMessage->interface = $interface;
        $sirenMessage->file      = isset(self::$backtrace[0]['file']) ? self::$backtrace[0]['file'] : '';
        $sirenMessage->line      = isset(self::$backtrace[0]['line']) ? self::$backtrace[0]['line'] : '';
        $sirenMessage->success   = 0;
        $sirenMessage->code      = $code;
        $sirenMessage->msg       = $message;
        $sirenMessage->alert     = $alert;

        return self::report($sirenMessage);
    }


    /**
     * @param array $backtrace
     */
    public static function setBacktrace(array $backtrace)
    {
        self::$backtrace = $backtrace;
    }

    /**
     * @param Exception $exception
     * @param int       $alert
     *
     * @return bool
     */
    public static function exception(Exception $exception, $alert = 0)
    {
        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = 'Exception';
        $sirenMessage->interface = $exception->getCode();
        $sirenMessage->file      = $exception->getFile();
        $sirenMessage->line      = $exception->getLine();
        $sirenMessage->success   = 0;
        $sirenMessage->code      = $exception->getCode();
        $sirenMessage->msg       = $exception->getMessage();
        $sirenMessage->alert     = $alert;
        return self::report($sirenMessage);
    }

}
