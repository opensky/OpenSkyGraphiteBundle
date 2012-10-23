<?php

namespace OpenSky\Bundle\GraphiteBundle\Connection;

use OpenSky\Bundle\GraphiteBundle\Connection;

class UdpConnection implements Connection
{
    protected $host;
    protected $port;
    protected $handle;

    /**
     * @param string $host host without protocol (1.2.3.4)
     * @param int $port udp port (80)
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function write($data)
    {
        if ($this->handle === null) {
            $this->open();
        }
       fwrite($this->handle, $data);
    }

    public function open()
    {
        $this->handle = fsockopen(sprintf('udp://%s', $this->host), $this->port);
    }

    public function close()
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }
}