<?php

namespace app\console;


use Interop\Container\ContainerInterface;

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
     */
    public function command($args)
    {

    }
}