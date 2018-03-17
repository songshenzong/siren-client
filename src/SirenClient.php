<?php

namespace Songshenzong\SirenClient;

use Exception;


/**
 * Class SirenClient
 *
 * @package Songshenzong\SirenClient
 */
class SirenClient
{
    /**
     * Version string
     */
    public const VERSION = '1.0.0';

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
    public static $host = '127.0.0.1';

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
     * @param string $host
     */
    public static function setHost(string $host = null): void
    {
        if ($host !== null) {
            self::$host = $host;
        }
    }


    /**
     * @param string $port
     */
    public static function setPort(string $port = null): void
    {
        if ($port !== null) {
            self::$port = $port;
        }
    }


    /**
     * @return string
     */
    public static function getPort(): string
    {
        return self::$port;
    }

    /**
     * 为你的上报数据设置TOKEN，如果错误，服务器将抛弃数据
     *
     * @param $token string Set Your Token 你的TOKEN
     */
    public static function setToken($token = ''): void
    {
        self::$token = $token;
    }

    /**
     * @return string
     */
    public static function getToken(): string
    {
        return self::$token;
    }

    /**
     * 设置模块子模块可以精确统计耗时
     *
     * @param string $module    Module Name 模块
     * @param string $submodule Submodule Name 子模块
     *
     * @return void
     */
    public static function tick($module, $submodule): void
    {
        self::$timeMap[$module][$submodule] = microtime(true);
    }


    /**
     * Report Your UDP
     * 上报你的消息
     *
     * @param Packet $packet
     *
     * @return bool
     */
    public static function report(Packet $packet): bool
    {
        self::$backtrace = null;

        if (isset(self::$timeMap[$packet->module][$packet->submodule]) && self::$timeMap[$packet->module][$packet->submodule] > 0) {

            $time_start = self::$timeMap[$packet->module][$packet->submodule];

            self::$timeMap[$packet->module][$packet->submodule] = 0;

        } elseif (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {

            $time_start            = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;

        } else {

            $time_start = microtime(true);

        }

        $packet->token     = self::$token;
        $packet->cost_time = microtime(true) - $time_start;


        $report_address = 'udp://' . self::$host . ':' . self::$port;
        $bin_data       = UdpProtocol::encode($packet);
        $socket         = stream_socket_client($report_address);
        if (!$socket) {
            return false;
        }
        return \strlen($bin_data) === stream_socket_sendto($socket, $bin_data);
    }


    /**
     * @param        $module
     * @param        $submodule
     *
     * @return bool
     */
    public static function success($module, $submodule): bool
    {
        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        return self::report($sirenMessage);
    }


    /**
     * @param   string $module
     * @param   string $submodule
     * @param   int    $code    Your Code 你可以自定义错误代码
     * @param   string $message Your Message 你可以自定义错误消息
     * @param   int    $alert   [Optional] -1 不发送警报消息 0 永远发送警报消息 2 积累两次后发送
     *
     * @return bool
     */
    public static function error($module, $submodule, $code, $message, $alert = 0): bool
    {
        if (self::$backtrace === null) {
            self::$backtrace = debug_backtrace();
        }

        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->file      = self::$backtrace[0]['file'] ?? '';
        $sirenMessage->line      = self::$backtrace[0]['line'] ?? '';
        $sirenMessage->type      = Packet::TYPE_ERROR;
        $sirenMessage->code      = $code;
        $sirenMessage->msg       = $message;
        $sirenMessage->alert     = $alert;

        return self::report($sirenMessage);
    }


    /**
     * @param array $backtrace
     */
    public static function setBacktrace(array $backtrace): void
    {
        self::$backtrace = $backtrace;
    }

    /**
     * Report Exception(Error)
     *
     * 上报异常（以错误的形式）
     *
     * @param Exception $exception
     * @param int       $alert
     *
     * @return bool
     */
    public static function exception(Exception $exception, $alert = 0): bool
    {
        $sirenMessage            = new Packet();
        $sirenMessage->module    = 'Exception';
        $sirenMessage->submodule = $exception->getCode();
        $sirenMessage->file      = $exception->getFile();
        $sirenMessage->line      = $exception->getLine();
        $sirenMessage->type      = Packet::TYPE_ERROR;
        $sirenMessage->code      = $exception->getCode();
        $sirenMessage->msg       = $exception->getMessage();
        $sirenMessage->alert     = $alert;
        return self::report($sirenMessage);
    }


    /**
     * Report Log
     *
     * 上报日志
     *
     * @param $module
     * @param $submodule
     * @param $message
     *
     * @return bool
     */
    public static function log($module, $submodule, $message): bool
    {
        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->type      = Packet::TYPE_LOG;
        $sirenMessage->msg       = $message;
        $sirenMessage->alert     = -1;

        return self::report($sirenMessage);
    }


    /**
     * Report Notice
     *
     * 上报通知（记录日志）
     *
     * @param $module
     * @param $submodule
     * @param $message
     *
     * @return bool
     */
    public static function notice($module, $submodule, $message): bool
    {
        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->type      = Packet::TYPE_NOTICE;
        $sirenMessage->msg       = $message;
        $sirenMessage->alert     = 0;

        return self::report($sirenMessage);
    }

}
