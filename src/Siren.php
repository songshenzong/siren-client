<?php

namespace Songshenzong\Siren;

use Exception;
use function is_array;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;
use Songshenzong\HttpClient\HttpClient;
use function json_encode;
use function strtolower;

/**
 * Class Siren
 *
 * @package Songshenzong\Siren
 */
class Siren
{
    /**
     * @var string
     */
    protected static $uuid;

    /**
     *
     * @var array
     */
    protected static $timeMap = [];


    /**
     * @var array
     */
    protected static $config = [];


    /**
     * @var array
     */
    protected static $backtrace;

    /**
     * @return string
     */
    public static function getUuid()
    {

        if (!self::$uuid) {
            try {
                $uuid1      = Uuid::uuid1();
                self::$uuid = $uuid1->toString();
            } catch (UnsatisfiedDependencyException $e) {
                self::$uuid = '';
            }
        }

        return self::$uuid;
    }


    /**
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
    }


    /**
     * @param null $key
     * @param null $default
     *
     * @return array|mixed|null
     */
    public static function getConfig($key = null, $default = null)
    {
        if ($key !== null) {
            return isset(self::$config[$key]) ? self::$config[$key] : $default;
        }
        return self::$config;
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
     * Report Your Packet
     *
     * @param Packet $packet
     *
     * @return bool
     */
    protected static function report(Packet $packet)
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

        $packet->token     = self::getConfig('token');
        $packet->cost_time = microtime(true) - $time_start;


        switch (strtolower(self::getConfig('protocol'))) {
            case 'udp':
                self::reportUdp($packet);
                break;
            case 'tcp':
                self::reportTcp($packet);
                break;
            case 'http':
                self::reportHttp($packet);
                break;
            default:
                self::reportUdp($packet);

        }

        return true;
    }


    /**
     * @param Packet $packet
     *
     * @return bool
     */
    protected static function reportTcp(Packet $packet)
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        $config = self::getConfig('tcp');
        if (!is_array($config)) {
            return false;
        }

        if (!isset($config['host'], $config['port'])) {
            return false;
        }

        $connect = socket_connect($socket, $config['host'], $config['port']);
        if (!$connect) {
            socket_close($socket);
            return false;
        }


        $msg = json_encode($packet);
        socket_write($socket, $msg, strlen($msg));
        socket_shutdown($socket);
        socket_close($socket);
        return true;
    }


    /**
     * @param \Songshenzong\Siren\Packet $packet
     *
     * @return bool
     */
    protected static function reportHttp(Packet $packet)
    {
        $config = self::getConfig('http');
        if (!is_array($config)) {
            return false;
        }

        if (!isset($config['host'], $config['port'])) {
            return false;
        }

        $url = $config['host'] . ':' . $config['port'];

        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => $packet
        ];

        return (bool) HttpClient::post($url, $options);
    }


    /**
     * @param Packet $packet
     *
     * @return bool
     */
    protected static function reportUdp(Packet $packet)
    {
        $config = self::getConfig('udp');
        if (!is_array($config)) {
            return false;
        }

        if (!isset($config['host'], $config['port'])) {
            return false;
        }

        $bin_data    = UdpProtocol::encode($packet);
        $udp_address = 'udp://' . $config['host'] . ':' . $config['port'];
        $socket      = stream_socket_client($udp_address);
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
        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        return self::report($sirenMessage);
    }


    /**
     * @param   string $module
     * @param   string $submodule
     * @param   string $message Your Message 你可以自定义错误消息
     * @param   int    $alert   [Optional] -1 不发送警报消息 0 永远发送警报消息 2 积累两次后发送
     *
     * @return bool
     */
    public static function error($module, $submodule, $message, $alert = 0)
    {
        if (self::$backtrace === null) {
            self::$backtrace = debug_backtrace();
        }

        $packet            = new Packet();
        $packet->module    = $module;
        $packet->submodule = $submodule;
        $packet->file      = isset(self::$backtrace[0]['file']) ? self::$backtrace[0]['file'] : '';
        $packet->line      = isset(self::$backtrace[0]['line']) ? self::$backtrace[0]['line'] : '';
        $packet->type      = SIREN_TYPE_ERROR;
        $packet->msg       = $message;
        $packet->alert     = $alert;

        return self::report($packet);
    }


    /**
     * @param array $backtrace
     */
    protected static function setBacktrace(array $backtrace)
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
        $sirenMessage            = new Packet();
        $sirenMessage->module    = 'Exception';
        $sirenMessage->submodule = $exception->getCode();
        $sirenMessage->file      = $exception->getFile();
        $sirenMessage->line      = $exception->getLine();
        $sirenMessage->type      = SIREN_TYPE_ERROR;
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
        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->type      = SIREN_TYPE_LOG;
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
        $sirenMessage            = new Packet();
        $sirenMessage->module    = $module;
        $sirenMessage->submodule = $submodule;
        $sirenMessage->type      = SIREN_TYPE_NOTICE;
        $sirenMessage->msg       = $message;
        $sirenMessage->alert     = 0;

        return self::report($sirenMessage);
    }

}
