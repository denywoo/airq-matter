<?php
namespace app\actions;


use Slim\Http\Request;
use Slim\Http\Response;

class PublishDataAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $queryParams = $request->getQueryParams();

        $key = "{$args['service']}/{$queryParams['task']}/{$queryParams['valuename']}";
        $value = $queryParams['value'];

        if (empty($key)) {
            return $response->withStatus(400);
        }

        $this->container->get('memcached')->set($key, $value);
        $this->container->get('logger')->info('Value added', ['key' => $key, 'value' => $value]);

        return $response;
    }
}