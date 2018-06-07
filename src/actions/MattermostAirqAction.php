<?php
namespace app\actions;


use Memcached;
use Slim\Http\Request;
use Slim\Http\Response;

class MattermostAirqAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, $args) {
        $registeredData = $this->container->get('settings')['registered_data'];

        /** @var Memcached $memcached */
        $memcached =  $this->container->get('memcached');

        $airData = [];
        foreach (array_keys($registeredData) as $key) {
            $airData[$key] = $memcached->get($key);
        }
        $airData = array_filter($airData, function($value) {return $value !== false;});

        foreach ($airData as $key => $value) {
            $row = [];
            $row[] = $registeredData[$key]['title'];
            $row[] = $value . ' ' . $registeredData[$key]['unit'];
            $row[] = $this->getEmoji($registeredData[$key]['feel'], $value);
            $airData[$key] = $row;
        }

        array_unshift($airData, $this->getTableHeaders());

        $responseData = [
            'response_type' => $this->settings('mattermost/response_type', 'ephemeral'),
            'text' => $this->renderMarkdownTable($airData),
        ];

        $username = $this->settings('mattermost/username', false);
        if ($username) {
            $responseData['username'] = $username;
        }

        if (file_exists($this->settings('appDir') . '/public/icon.png')) {
            $responseData['icon_url'] = $this->settings('baseUrl') . '/icon.png';
        }

        return $response->withJson($responseData);
    }

    /**
     * @param  string       $key
     * @param  mixed|null   $default
     * @return mixed|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function settings($key, $default = null) {
        $settings = $this->container->get('settings')->all();
        $subKeys = explode('/', $key);
        $subKey = array_shift($subKeys);
        while ($subKey !== null) {
            if (!array_key_exists($subKey, $settings)) {
                return $default;
            }
            $settings = $settings[$subKey];
            $subKey = array_shift($subKeys);
        }
        return $settings;
    }

    private function getEmoji(array $variants, $value) {
        $value = floatval($value);
        $selectedEmoji = '';
        foreach ($variants as $edge => $emoji) {
            $edge = floatval($edge);
            if ($value < $edge) {
                $selectedEmoji = $emoji;
                break;
            }
        }
        return $selectedEmoji;
    }

    private function renderMarkdownTable(array $data) {
        if (empty($data)) {
            return '';
        }

        $headers = array_shift($data);
        $splitters = array_fill(0, count($headers), ':---');

        array_unshift($data, $splitters);
        array_unshift($data, $headers);

        $rows = array_map(function($row) {
            return '|' . implode('|', $row) . '|';
        }, $data);

        $result = implode("\n", $rows);

        return $result;
    }

    private function getTableHeaders()
    {
        return [
            'Parameter',
            'Value',
            'Feel',
        ];
    }
}