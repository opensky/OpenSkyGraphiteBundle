<?php

namespace OpenSky\Bundle\GraphiteBundle\Connection;

use OpenSky\Bundle\GraphiteBundle\Connection;

class NoopConnection implements Connection
{
    public function write($data)
    {
    }

    public function open()
    {
    }

    public function close()
    {
    }
}