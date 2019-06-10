<?php
namespace app\actions;


use Slim\Http\Request;
use Slim\Http\Response;

class PublishDataAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $logger = $this->container->get('logger');

        $queryParams = $request->getQueryParams();

        $key = "{$args['service']}/{$queryParams['task']}/{$queryParams['valuename']}";
        $value = $queryParams['value'];

        if (empty($key)) {
            return $response->withStatus(400);
        }

        $registeredData = $this->container->get('settings')['registered_data'];
        if (!array_key_exists($key, $registeredData)) {
            //$logger->info('The value was discarded', ['key' => $key, 'value' => $value]);
            return $response;
        }

        $this->container->get('memcached')->set($key, $value);
        //$logger->info('Value added', ['key' => $key, 'value' => $value]);

        return $response;
    }
}