<?php

namespace app\console;


use Interop\Container\ContainerInterface;
use Memcached;
use MongoDB\BSON\UTCDateTime;
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
            'datetime' => new UTCDateTime($this->container->get('time_quantum')),
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
            if (array_key_exists('onGraph', $value) && $value['onGraph']) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}