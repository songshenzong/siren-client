<?php

namespace Songshenzong\SirenClient;

/**
 * Class SirenMessage
 *
 * @package Songshenzong\SirenClient
 */
class SirenMessage
{


    /**
     * @var string
     */
    public $token = '';


    /**
     * @var string
     */
    public $module = '';

    /**
     * @var string
     */
    public $interface = '';

    /**
     * @var float
     */
    public $cost_time = 0;

    /**
     * @var int
     */
    public $success = 1;

    /**
     * @var int
     */
    public $code;

    /**
     * @var string
     */
    public $msg;

    /**
     * @var integer
     */
    public $alert;

    /**
     * @var string
     */
    public $file;

    /**
     * @var integer
     */
    public $line;

    /**
     * @var string
     */
    public $request = '';

    /**
     * @var string
     */
    public $ip;

    /**
     * @var string
     */
    public $time;
}
