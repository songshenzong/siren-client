<?php

namespace Songshenzong\Siren\Traits;

/**
 * Trait PacketTrait
 *
 * @package Songshenzong\Siren\Traits
 */
trait PacketTrait
{
    /**
     * @var string
     */
    public $version = '1.00.00';

    /**
     * @var string
     */
    public $token = '';

    /**
     * @var string
     */
    public $uuid;

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
    public $type = 1;

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
    public $line = 0;

    /**
     * @var string
     */
    public $request = '';

    /**
     * @var string
     */
    public $time;
}

