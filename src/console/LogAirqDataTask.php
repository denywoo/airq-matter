<?php

namespace app\console;


use DateTime;
use DateTimeZone;
use Interop\Container\ContainerInterface;
use Memcached;
use Monolog\Logger;

class LogAirqDataTask
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        // access container classes
        // eg $container->get('redis');
        $this->container = $container;
    }

    /**
     * LogAirqData command
     *
     * @param array $args
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function command($args)
    {
        /** @var Logger $logger */
        $logger = $this->container->get('logger');
        $logger->info('LogAirqData task launched');

        $keys = $this->getDataKeys();
        if (empty($keys)) {
            return;
        }

        $record = [
            'datetime' => $this->getMongoDate(),
        ];

        /** @var Memcached $memcached */
        $memcached =  $this->container->get('memcached');

        foreach ($keys as $key) {
            $record[$key] = $memcached->get($key);
        }

        /** @var \MongoDB\Client $mongoClient */
        $mongoClient = $this->container->get('mongo');

        $collection = $mongoClient->AirQ->datalog;
        $collection->insertOne($record);
    }

    private function getDataKeys() {
        $registeredData = $this->container->get('settings')['registered_data'];

        $keys = [];
        foreach ($registeredData as $key => $value) {
            if (array_key_exists('onChart', $value) && $value['onChart']) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    private function getMongoDate() {
        $timeZone = $this->container->get('settings')['timezone'];
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone($timeZone));
        list($hours, $minutes) = explode(':', $date->format('H:i'));
        $hours = intval($hours);
        $minutes = round(intval($minutes) / 15) * 15;
        $date->setTime($hours, $minutes, 0);
        $timestamp = $date->getTimestamp();
        $utcDateTime = new \MongoDB\BSON\UTCDateTime($timestamp * 1000);
        return $utcDateTime;
    }
}