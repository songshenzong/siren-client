<?php

namespace Songshenzong\SirenClient;

/**
 * Class SirenPacket
 *
 * @package Songshenzong\SirenClient
 */
class SirenPacket
{

    /**
     * Type of Message int Error
     */
    const TYPE_ERROR = 0;


    /**
     * Type of Message int Success
     */
    const TYPE_SUCCESS = 1;


    /**
     * Type of Message int Log
     */
    const TYPE_LOG = 2;


    /**
     * Type of Message int Notice
     */
    const TYPE_NOTICE = 3;

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
    public $submodule = '';

    /**
     * @var float
     */
    public $cost_time = 0;

    /**
     * @var int Type of Message
     */
    public $type = self::TYPE_SUCCESS;

    /**
     * @var int
     */
    public $code = 0;

    /**
     * @var string
     */
    public $msg = '';

    /**
     * @var integer
     */
    public $alert = -1;

    /**
     * @var string
     */
    public $file = '';

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
