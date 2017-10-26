<?php


if (!function_exists('statisticTick')) {
    /**
     * Get the instance
     *
     * @return bool
     *
     * @param string $module
     * @param string $interface
     */
    function statisticTick($module = '', $interface = '')
    {
        return \Songshenzong\StatisticClient\StatisticClient::tick($module, $interface);
    }
}


if (!function_exists('statisticSetAddress')) {
    /**
     * Get the instance
     *
     * @return bool
     *
     * @param     $ip
     * @param int $port
     */
    function statisticSetAddress($ip, $port = 55656)
    {
        return \Songshenzong\StatisticClient\StatisticClient::setAddress($ip, $port);
    }
}


if (!function_exists('statisticError')) {
    /**
     * Get the instance
     *
     * @return bool
     *
     * @param       $code
     * @param array ...$args
     */
    function statisticError($code, ...$args)
    {
        return \Songshenzong\StatisticClient\StatisticClient::error($code, ...$args);
    }
}


if (!function_exists('statisticException')) {
    /**
     * Get the instance
     *
     * @return bool
     *
     * @param Exception $exception
     */
    function statisticException(Exception $exception)
    {
        return \Songshenzong\StatisticClient\StatisticClient::exception($exception);
    }
}


if (!function_exists('statisticSuccess')) {
    /**
     * Get the instance
     *
     * @return bool
     *
     * @param int    $code
     * @param string $msg
     */
    function statisticSuccess($code = 0, $msg = 'success')
    {
        return \Songshenzong\StatisticClient\StatisticClient::success($code, $msg);
    }
}