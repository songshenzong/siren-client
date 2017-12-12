<?php

if (!function_exists('sirenSetAddress')) {
    /**
     * Get the instance
     *
     *
     * @param     $ip
     * @param int $port
     */
    function sirenSetAddress($ip, $port = 55656)
    {
        \Songshenzong\SirenClient\SirenClient::setAddress($ip, $port);
    }
}


if (!function_exists('sirenSetToken')) {
    /**
     * Get the instance
     *
     *
     * @param     $token string
     */
    function sirenSetToken($token)
    {
        \Songshenzong\SirenClient\SirenClient::setToken($token);
    }
}


if (!function_exists('sirenTick')) {
    /**
     * Get the instance
     *
     *
     * @param string $module
     * @param string $interface
     */
    function sirenTick($module = '', $interface = '')
    {
        \Songshenzong\SirenClient\SirenClient::tick($module, $interface);
    }
}


if (!function_exists('sirenError')) {
    /**
     * Get the instance
     *
     * @param       $module
     * @param       $interface
     * @param       $code
     * @param       $message
     * @param       $alert
     *
     * @return bool
     */
    function sirenError($module, $interface, $code, $message, $alert = 0)
    {
        \Songshenzong\SirenClient\SirenClient::setBacktrace(debug_backtrace());
        return \Songshenzong\SirenClient\SirenClient::error($module, $interface, $code, $message, $alert);
    }
}


if (!function_exists('sirenException')) {
    /**
     * Get the instance
     *
     * @param           $module
     * @param           $interface
     * @param Exception $exception
     * @param           $alert
     *
     * @return bool
     */
    function sirenException($module, $interface, Exception $exception, $alert = 0)
    {
        return \Songshenzong\SirenClient\SirenClient::exception($module, $interface, $exception, $alert);
    }
}


if (!function_exists('sirenSuccess')) {
    /**
     * Report the success.
     *
     * @param        $module
     * @param        $interface
     *
     * @return bool
     */
    function sirenSuccess($module, $interface)
    {
        return \Songshenzong\SirenClient\SirenClient::success($module, $interface);
    }
}
