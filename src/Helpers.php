<?php

if (!function_exists('sirenSetHost')) {
    /**
     * Get the instance
     *
     *
     * @param     $ip
     * @param int $port
     */
    function sirenSetHost($ip, $port = 55656)
    {
        \Songshenzong\SirenClient\SirenClient::setHost($ip, $port);
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
     * @param string $submodule
     */
    function sirenTick($module = '', $submodule = '')
    {
        \Songshenzong\SirenClient\SirenClient::tick($module, $submodule);
    }
}


if (!function_exists('sirenError')) {
    /**
     * Get the instance
     *
     * @param       $module
     * @param       $submodule
     * @param       $code
     * @param       $message
     * @param       $alert
     *
     * @return bool
     */
    function sirenError($module, $submodule, $code, $message, $alert = 0)
    {
        \Songshenzong\SirenClient\SirenClient::setBacktrace(debug_backtrace());
        return \Songshenzong\SirenClient\SirenClient::error($module, $submodule, $code, $message, $alert);
    }
}


if (!function_exists('sirenException')) {
    /**
     * Get the instance
     *
     * @param Exception $exception
     * @param           $alert
     *
     * @return bool
     */
    function sirenException(Exception $exception, $alert = 0)
    {
        return \Songshenzong\SirenClient\SirenClient::exception($exception, $alert);
    }
}


if (!function_exists('sirenSuccess')) {
    /**
     * Report the success.
     *
     * @param        $module
     * @param        $submodule
     *
     * @return bool
     */
    function sirenSuccess($module, $submodule)
    {
        return \Songshenzong\SirenClient\SirenClient::success($module, $submodule);
    }
}


if (!function_exists('sirenLog')) {
    /**
     * Report the Log.
     *
     * @param $module
     * @param $submodule
     * @param $message
     *
     * @return bool
     */
    function sirenLog($module, $submodule, $message)
    {
        return \Songshenzong\SirenClient\SirenClient::log($module, $submodule, $message);
    }
}


if (!function_exists('sirenNotice')) {
    /**
     * Report the Notice.
     *
     * @param $module
     * @param $submodule
     * @param $message
     *
     * @return bool
     */
    function sirenNotice($module, $submodule, $message)
    {
        return \Songshenzong\SirenClient\SirenClient::notice($module, $submodule, $message);
    }
}
