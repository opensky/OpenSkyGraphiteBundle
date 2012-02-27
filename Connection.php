<?php

namespace OpenSky\Bundle\GraphiteBundle;

interface Connection
{
    function write($data);

    function open();

    function close();
}