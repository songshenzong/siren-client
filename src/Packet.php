<?php

namespace Songshenzong\SirenClient;

use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Uuid;

/**
 * Class Packet
 *
 * @package Songshenzong\SirenClient
 */
class Packet
{

    /**
     * Type of Message int Error
     */
    public const TYPE_ERROR = 0;


    /**
     * Type of Message int Success
     */
    public const TYPE_SUCCESS = 1;


    /**
     * Type of Message int Log
     */
    public const TYPE_LOG = 2;


    /**
     * Type of Message int Notice
     */
    public const TYPE_NOTICE = 3;


    /**
     * @var string
     */
    public static $uuid;

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
    public $time;


    /**
     * Packet constructor.
     */
    public function __construct()
    {
        self::getUuid();
    }


    /**
     * @return string
     */
    public static function getUuid(): string
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
}

