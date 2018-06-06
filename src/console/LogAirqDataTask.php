<?php

namespace app\console;


use Interop\Container\ContainerInterface;
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
    }
}