<?php
namespace app\actions;


use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $args) {
        return $response;
    }
}