<?php

namespace OpenSky\Bundle\GraphiteBundle\Connection;

use OpenSky\Bundle\GraphiteBundle\Connection;

class NullConnection implements Connection
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