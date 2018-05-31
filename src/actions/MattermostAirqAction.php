<?php
namespace app\actions;


use Memcached;
use Slim\Http\Request;
use Slim\Http\Response;

class MattermostAirqAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, $args) {
        /** @var Memcached $memcached */
        $memcached =  $this->container->get('memcached');

        $airData = array_filter([
            'temperature' => $memcached->get('AirQ/Climate/Temperature'),
            'humidity' => $memcached->get('AirQ/Climate/Humidity'),
            'CO22' => $memcached->get('AirQ/CO2/PPM'),
        ], function($value) {return $value !== false;});

        $responseData = [
            'response_type' => 'ephemeral',
            'text' => $airData,
        ];
        return $response->withJson($responseData);
    }
}