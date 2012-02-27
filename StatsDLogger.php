<?php

namespace OpenSky\Bundle\GraphiteBundle;

/**
 * statsd logger built on the etsy example from:
 * https://github.com/etsy/statsd/blob/master/examples/php-example.php
 */
class StatsDLogger
{
    protected $connection;
    protected $prefix;

    /**
     * @param Connection $connection
     * @param string $prefix prefix for all stats, configure per server or environemnt
     */
    public function __construct(Connection $connection, $prefix = '')
    {
        $this->connection = $connection;
        $this->prefix = $prefix;
    }

    /**
     * Log timing information
     *
     * @param string $stats The metric to in log timing info for.
     * @param float $time The ellapsed time (ms) to log
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     */
    public function timing($stat, $time, $sampleRate = 1)
    {
        $this->send(array($stat => sprintf('%s|ms', $time)), $sampleRate);
    }

    /**
     * Increments one or more stats counters
     *
     * @param string|array $stats The metric(s) to increment.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function increment($stats, $sampleRate = 1)
    {
        $this->updateStats($stats, 1, $sampleRate);
    }

    /**
     * Decrements one or more stats counters.
     *
     * @param string|array $stats The metric(s) to decrement.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function decrement($stats, $sampleRate = 1)
    {
        $this->updateStats($stats, -1, $sampleRate);
    }

    /**
     * Updates one or more stats counters by arbitrary amounts.
     *
     * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
     * @param int|1 $delta The amount to increment/decrement each metric by.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function updateStats($stats, $delta = 1, $sampleRate = 1)
    {
        if (! is_array($stats)) {
            $stats = array($stats);
        }
        $data = array();
        foreach ($stats as $stat) {
            $data[$stat] = sprintf('%s|c', $delta);
        }

        $this->send($data, $sampleRate);
    }

    /**
     * Send statsd data over UDP
     *
     * @param array $data
     * @param float $sampleRate
     */
    public function send(array $data, $sampleRate = 1)
    {
        $sampledData = array();

        if ($sampleRate < 1) {
            foreach ($data as $stat => $value) {
                if ($this->getRandomRate() <= $sampleRate) {
                    $sampledData[$stat] = sprintf('%s|@%s', $value, $sampleRate);
                }
            }
        } else {
            $sampledData = $data;
        }

        if (! empty($sampledData)) {
            try {
                foreach ($sampledData as $stat => $value) {
                    $this->connection->write(sprintf("%s%s:%s", $this->prefix, $stat, $value));
                }
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * Close connection
     */
    public function closeConnection()
    {
        $this->connection->close();
    }

    /**
     * Generate a random number between 0 and 1
     * @return float
     */
    protected function getRandomRate()
    {
        return mt_rand() / mt_getrandmax();
    }
}