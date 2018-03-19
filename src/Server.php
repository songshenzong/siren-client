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
     * @param $random_host
     * @param $protocol
     */
    public function __construct($random_host, $protocol)
    {
        $host_explode = explode(':', $random_host);
        if ($random_host === $host_explode[0]) {
            die('Server Format Error:' . $host_explode[0]);
        }
        $this->host     = $host_explode[0];
        $this->port     = $host_explode[1];
        $this->protocol = $protocol;
    }

}
