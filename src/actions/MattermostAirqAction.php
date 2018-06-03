<?php
namespace app\actions;


use Memcached;
use Slim\Http\Request;
use Slim\Http\Response;

class MattermostAirqAction extends BaseAction
{
    private static $_paramTitles = [
        'AirQ/Climate/Temperature' => [
            'title' => 'Temperature',
            'unit' => '°C',
            'feel' => [
                23 => ':snowflake:', // cold
                25 => ':sunglasses:', // comfort
                29 => ':sweat:', // it is hot
                100 => ':fire:', // too hot
            ],
        ],
        'AirQ/Climate/Humidity' => [
            'title' => 'Humidity',
            'unit' => '%',
            'feel' => [
                30 => ':zap:', // dry
                40 => ':ok_hand:', // not so bad if you in Moscow
                60 => ':sunglasses:', // comfort if you are human
                85 => ':palm_tree:', // comfort if you are plant
                146 => ':umbrella:', // precipitation is possible
            ]
        ],
        'AirQ/CO2/PPM' => [
            'title' => 'CO₂',
            'unit' => 'ppm',
            'feel' => [
                500 => ':deciduous_tree:', // oh, fresh air!
                700 => ':grinning:', // good
                1000 => ':confused:', // not so good
                1500 => ':disappointed:', // bad
                5555 => ':finnadie:', // you are dead!
            ]
        ],
    ];

    public function __invoke(Request $request, Response $response, $args) {
        /** @var Memcached $memcached */
        $memcached =  $this->container->get('memcached');

        $airData = [];
        foreach ($this->getParamKeys() as $key) {
            $airData[$key] = $memcached->get($key);
        }
        $airData = array_filter($airData, function($value) {return $value !== false;});

        foreach ($airData as $key => $value) {
            $row = [];
            $row[] = static::$_paramTitles[$key]['title'];
            $row[] = $value . ' ' . static::$_paramTitles[$key]['unit'];
            $row[] = $this->getEmoji(static::$_paramTitles[$key]['feel'], $value);
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

    private function getParamKeys() {
        return array_keys(static::$_paramTitles);
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