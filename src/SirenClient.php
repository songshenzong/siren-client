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
    const VERSION = '1.0.0';

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
     * @param $ip   Server's IP 接收服务器的IP
     * @param $port Server's Port 接收服务器的端口
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
     * 为你的上报数据设置TOKEN，如果错误，服务器将抛弃数据
     *
     * @param $token string Set Your Token 你的TOKEN
     */
    public static function setToken($token = '')
    {
        self::$token = $token;
    }


    /**
     * 设置模块子模块可以精确统计耗时
     *
     * @param string $module    Module Name 模块
     * @param string $submodule Submodule Name 子模块
     *
     * @return void
     */
    public static function tick($module, $submodule)
    {
        self::$timeMap[$module][$submodule] = microtime(true);
    }


    /**
     * Report Your UDP
     * 上报你的消息
     *
     * @param SirenMessage $siren_message
     *
     * @return bool
     */
    public static function report(SirenMessage $siren_message)
    {
        self::$backtrace = null;

        if (isset(self::$timeMap[$siren_message->module][$siren_message->submodule]) && self::$timeMap[$siren_message->module][$siren_message->submodule] > 0) {

            $time_start = self::$timeMap[$siren_message->module][$siren_message->submodule];

            self::$timeMap[$siren_message->module][$siren_message->submodule] = 0;

        } elseif (isset(self::$timeMap['']['']) && self::$timeMap[''][''] > 0) {

            $time_start            = self::$timeMap[''][''];
            self::$timeMap[''][''] = 0;

        } else {

            $time_start = microtime(true);

        }

        $siren_message->token     = self::$token;
        $siren_message->cost_time = microtime(true) - $time_start;


        $report_address = 'udp://' . self::$ip . ':' . self::$port;
        $bin_data       = SirenProtocols::encode($siren_message);
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
    public static function success($module, $submodule)
    {
        $sirenMessage            = new SirenMessage();
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
    public static function error($module, $submodule, $code, $message, $alert = 0)
    {
        if (self::$backtrace === null) {
            self::$backtrace = debug_backtrace();
        }

        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->file      = isset(self::$backtrace[0]['file']) ? self::$backtrace[0]['file'] : '';
        $sirenMessage->line      = isset(self::$backtrace[0]['line']) ? self::$backtrace[0]['line'] : '';
        $sirenMessage->type      = SirenMessage::TYPE_ERROR;
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
     * Report Exception(Error)
     *
     * 上报异常（以错误的形式）
     *
     * @param Exception $exception
     * @param int       $alert
     *
     * @return bool
     */
    public static function exception(Exception $exception, $alert = 0)
    {
        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = 'Exception';
        $sirenMessage->submodule = $exception->getCode();
        $sirenMessage->file      = $exception->getFile();
        $sirenMessage->line      = $exception->getLine();
        $sirenMessage->type      = SirenMessage::TYPE_ERROR;
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
    public static function log($module, $submodule, $message)
    {
        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->type      = SirenMessage::TYPE_LOG;
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
    public static function notice($module, $submodule, $message)
    {
        $sirenMessage            = new SirenMessage();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->type      = SirenMessage::TYPE_NOTICE;
        $sirenMessage->msg       = $message;
        $sirenMessage->alert     = 0;

        return self::report($sirenMessage);
    }

}
