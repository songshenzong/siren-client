<?php

namespace Songshenzong\Siren;

use Exception;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;
use Songshenzong\HttpClient\HttpClient;

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
    protected static $request_id;

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
    public static function getRequestId()
    {
        if (!self::$request_id) {
            try {
                self::$request_id = Uuid::uuid1()->toString();
            } catch (UnsatisfiedDependencyException $e) {
                self::$request_id = '';
            }
        }

        return self::$request_id;
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
        if ($packet->type !== SIREN_TYPE_SUCCESS) {
            $packet->request .= isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' : '';
            $packet->request .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
            $packet->request .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            if ($packet->request === '://') {
                $packet->request = '';
            }
        }


        switch (strtoupper(self::getConfig('protocol'))) {
            case SIREN_PROTOCOL_UDP:
                return self::reportUdp($packet);
                break;
            case SIREN_PROTOCOL_TCP:
                return self::reportTcp($packet);
                break;
            case SIREN_PROTOCOL_HTTP:
                return self::reportHttp($packet);
                break;
            default:
                return self::reportUdp($packet);

        }

    }


    /**
     * @param Packet $packet
     *
     * @return bool
     */
    protected static function reportTcp(Packet $packet)
    {
        $server  = self::getServer('tcp');
        $socket  = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $connect = socket_connect($socket, $server->host, $server->port);
        if (!$connect) {
            socket_close($socket);
            return false;
        }

        socket_write($socket, $packet, strlen($packet));
        socket_shutdown($socket);
        socket_close($socket);

        return true;
    }


    /**
     * @param Packet $packet
     *
     * @return bool
     */
    protected static function reportHttp(Packet $packet)
    {
        $server  = self::getServer('http');
        $url     = $server->protocol . '://' . $server->host . ':' . $server->port;
        $options = [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => $packet
        ];

        return (bool) HttpClient::post($url, $options);
    }


    /**
     * @param string $protocol
     *
     * @return Server
     */
    protected static function getServer($protocol = 'udp')
    {
        $servers = self::getConfig('servers');
        if (!$servers) {
            die('Servers Not Found');
        }

        $hosts = isset($servers[$protocol]) ? $servers[$protocol] : [];

        if (!isset($hosts['host'], $hosts['port'])) {
            die('Server Not Found:' . $protocol);
        }

        return new Server($hosts['host'], $hosts['port'], $protocol);
    }

    /**
     * @param Packet $packet
     *
     * @return bool
     */
    protected static function reportUdp(Packet $packet)
    {
        $server   = self::getServer('udp');
        $bin_data = UdpProtocol::encode($packet);
        $host     = $server->protocol . '://' . $server->host . ':' . $server->port;
        $socket   = stream_socket_client($host);
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
     * @param       $module
     * @param       $submodule
     * @param       $message
     * @param int   $alert
     * @param array $data
     *
     * @return bool
     */
    public static function error($module, $submodule, $message, $alert = SIREN_ALERT_ALWAYS, array $data = [])
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
        $packet->data      = $data;

        return self::report($packet);
    }


    /**
     * Warning、Notice
     *
     * @param callable $function
     */
    public static function set_error_handler(callable $function)
    {
        set_error_handler(function () use ($function) {
            $error             = \func_get_args();
            $packet            = new Packet();
            $packet->module    = 'set_error_handler';
            $packet->submodule = $error[0];
            $packet->msg       = $error[1];
            $packet->file      = $error[2];
            $packet->line      = $error[3];
            $packet->data      = $error[4];
            $packet->type      = SIREN_TYPE_ERROR;
            $packet->alert     = SIREN_ALERT_ALWAYS;

            self::report($packet);

            $function(...func_get_args());
        });
    }


    /**
     * Fatal Error、Parse Error
     *
     * @param callable $function
     */
    public static function register_shutdown_function(callable $function)
    {
        register_shutdown_function(function () use ($function) {

            $error = \error_get_last();

            if ($error['message']) {
                $packet            = new Packet();
                $packet->module    = 'register_shutdown_function';
                $packet->submodule = isset($error['type']) ? $error['type'] : 'register_shutdown_function';
                $packet->msg       = $error['message'];
                $packet->file      = $error['file'];
                $packet->line      = $error['line'];
                $packet->data      = $error;
                $packet->type      = SIREN_TYPE_ERROR;
                $packet->alert     = SIREN_ALERT_ALWAYS;

                self::report($packet);
            }


            $function(...func_get_args());
        });


    }


    /**
     * set_exception_handler
     *
     * @param callable $function
     */
    public static function set_exception_handler(callable $function)
    {

        set_exception_handler(function ($exception) use ($function) {
            /**
             * @var Exception $exception
             */
            $packet            = new Packet();
            $packet->module    = 'set_exception_handler';
            $packet->submodule = get_class($exception);
            $packet->msg       = $exception->getMessage();
            $packet->file      = $exception->getFile();
            $packet->line      = $exception->getLine();
            $packet->data      = $exception->getTrace();
            $packet->type      = SIREN_TYPE_ERROR;
            $packet->alert     = SIREN_ALERT_ALWAYS;

            self::report($packet);

            $function($exception);
        });


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
    public static function exception(Exception $exception, $alert = SIREN_ALERT_ALWAYS)
    {
        $packet            = new Packet();
        $packet->module    = 'Exception';
        $packet->submodule = $exception->getCode();
        $packet->file      = $exception->getFile();
        $packet->line      = $exception->getLine();
        $packet->type      = SIREN_TYPE_ERROR;
        $packet->msg       = $exception->getMessage();
        $packet->alert     = $alert;
        $packet->data      = $exception->getTrace();

        return self::report($packet);
    }


    /**
     * @param       $module
     * @param       $submodule
     * @param       $message
     * @param array $data
     *
     * @return bool
     */
    public static function log($module, $submodule, $message, array $data = [])
    {
        $packet            = new Packet();
        $packet->module    = $module;
        $packet->submodule = $submodule;
        $packet->type      = SIREN_TYPE_LOG;
        $packet->msg       = $message;
        $packet->alert     = -1;
        $packet->data      = $data;

        return self::report($packet);
    }


    /**
     * @param       $module
     * @param       $submodule
     * @param       $message
     * @param array $data
     *
     * @return bool
     */
    public static function notice($module, $submodule, $message, array $data = [])
    {
        $packet            = new Packet();
        $packet->module    = $module;
        $packet->submodule = $submodule;
        $packet->type      = SIREN_TYPE_NOTICE;
        $packet->msg       = $message;
        $packet->alert     = 0;
        $packet->data      = $data;
        return self::report($packet);
    }

}
