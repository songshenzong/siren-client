<?php

namespace Songshenzong\Siren;

class Server
{

    /**
     * @var string
     */
    public $protocol;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $port;

    /**
     * Server constructor.
     *
     * @param $host
     * @param $port
     * @param $protocol
     */
    public function __construct($host, $port, $protocol)
    {
        $this->host     = trim($host);
        $this->port     = trim($port);
        $this->protocol = $protocol;
    }

}
