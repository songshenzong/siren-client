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
     * Type int
     */
    const TYPE_SUCCESS = 1;

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
     * @var int
     */
    public $success = self::TYPE_SUCCESS;

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
