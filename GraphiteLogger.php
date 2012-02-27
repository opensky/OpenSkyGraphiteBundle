<?php

namespace OpenSky\Bundle\GraphiteBundle;

class GraphiteLogger
{
    protected $connection;
    protected $prefix;
    protected $active;

    /**
     * @param Connection $connection
     * @param string $prefix prefix for all stats, configure per server or environemnt
     * @param bool $active if false, don't make udp call
     */
    public function __construct(Connection $connection, $prefix = '')
    {
        $this->connection = $connection;
        $this->prefix = $prefix;
    }

    /**
     * Log metric to graphite
     *
     * @param string $metric The metric to log info for.
     * @param float $value The value to log
     * @param DateTime $time Optionally log the time, defaults to now
     */
    public function log($metric, $value, \DateTime $time = null)
    {
        if ($time === null) {
            $time = new \DateTime();
        }

        $this->connection->write(sprintf("%s%s %s %s", $this->prefix, $metric, $value, $time->getTimestamp()));
    }

    /**
     * Close connection
     */
    public function closeConnection()
    {
        $this->connection->close();
    }
}