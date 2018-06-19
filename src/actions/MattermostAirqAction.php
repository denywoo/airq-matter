<?php

namespace app\actions;


use app\graph\AirqGraph;
use Memcached;
use MongoDB\BSON\UTCDateTime;
use Slim\Http\Request;
use Slim\Http\Response;

class MattermostAirqAction extends BaseAction
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $commandText = strtolower(trim($request->getParsedBodyParam('text', '')));

        if ($commandText === 'graph') {
            return $this->actionShowGraph($request, $response, $args);
        }

        return $this->actionShowTable($request, $response, $args);
    }

    public function actionShowTable(Request $request, Response $response, $args)
    {
        $registeredData = $this->container->get('settings')['registered_data'];

        /** @var Memcached $memcached */
        $memcached = $this->container->get('memcached');

        $airData = [];
        foreach (array_keys($registeredData) as $key) {
            $airData[$key] = $memcached->get($key);
        }
        $airData = array_filter($airData, function ($value) {
            return $value !== false;
        });

        foreach ($airData as $key => $value) {
            $row = [];
            $row[] = $registeredData[$key]['title'];
            $row[] = $value . ' ' . $registeredData[$key]['unit'];
            $row[] = $this->getEmoji($registeredData[$key]['feel'], $value);
            $airData[$key] = $row;
        }

        array_unshift($airData, $this->getTableHeaders());

        $markdownTable = $this->renderMarkdownTable($airData);
        $responseData = $this->prepareMattermostResponse($markdownTable);

        return $response->withJson($responseData);
    }

    public function actionShowGraph(Request $request, Response $response, $args)
    {
        $memcached = $this->container->get('memcached');
        /** @var \DateTimeInterface $time */
        $time = $this->container->get('time_quantum');

        $lastGraphTime = $memcached->get('lastGraphTime');

        if ($lastGraphTime != $time) {

        }

        $registeredData = $this->container->get('settings')['registered_data'];
        $keys = array_keys($registeredData);

        $data = $this->prepareGraphData(new \DateInterval('P1D'), $keys);
        $graphs = [];
        foreach ($keys as $key) {
            $graph = new AirqGraph();
            $graph->width = 500;
            $graph->height = 250;
            $graph->dataX = $data['time'];
            $graph->dataY = $data[$key];
            $graph->xAxisTitle = 'Hour';
            $graph->yAxisTitle = "{$registeredData[$key]['title']}, {$registeredData[$key]['unit']}";
            $graph->title = $registeredData[$key]['title'];
            $filename = '/graph/' . md5($key) . '.png';
            $filePath = $this->settings('appDir') . '/public' . $filename;
            $graph->save($filePath);
            $fileUrl = $this->settings('baseUrl') . $filename . '?t=' . $time->getTimestamp();
            $graphs[] = "#### {$registeredData[$key]['title']}\n![{$registeredData[$key]['title']}]({$fileUrl})";
        }

        $text = implode("\n", $graphs);
        $mattermostResponse = $this->prepareMattermostResponse($text);

        return $response->withJson($mattermostResponse);
    }

    /**
     * @param  string $key
     * @param  mixed|null $default
     * @return mixed|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function settings($key, $default = null)
    {
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

    private function getEmoji(array $variants, $value)
    {
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

    private function renderMarkdownTable(array $data)
    {
        if (empty($data)) {
            return '';
        }

        $headers = array_shift($data);
        $splitters = array_fill(0, count($headers), ':---');

        array_unshift($data, $splitters);
        array_unshift($data, $headers);

        $rows = array_map(function ($row) {
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

    /**
     * @return array
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function prepareGraphData(\DateInterval $interval, array $keys)
    {
        $date = (new \DateTime)->sub($interval);
        $mongoDate = new UTCDateTime($date);

        /** @var \MongoDB\Client $mongoClient */
        $mongoClient = $this->container->get('mongo');

        $collection = $mongoClient->AirQ->datalog;

        $logData = $collection->find(['datetime' => ['$gte' => $mongoDate]])->toArray();

        $data = [
            'time' => [],
        ];
        foreach ($keys as $key) {
            $data[$key] = [];
        }

        foreach ($logData as $document) {

            /** @var UTCDateTime $dateTime */
            $dateTime = $document['datetime'];
            list($hour, $mimute) = explode(':', $dateTime
                ->toDateTime()
                ->setTimezone($this->container->get('timezone'))
                ->format('H:i'));
            $data['time'][] = intval($mimute) === 0 ? $hour : '';
            foreach ($keys as $key) {
                if (!array_key_exists($key, $document)) {
                    $data[$key][] = null;
                    continue;
                }
                $value = $document[$key];
                if ($value === null || $value === false) {
                    $data[$key][] = null;
                    continue;
                }
                $data[$key][] = mb_strpos($value, '.') === false ? intval(($value)) : floatval($value);
            }
        }

        return $data;
    }

    /**
     * @param string $text
     * @param array|null $attachments
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function prepareMattermostResponse($text, $attachments = null): array
    {
        $responseData = [
            'response_type' => $this->settings('mattermost/response_type', 'ephemeral'),
            'text' => $text,
        ];

        $username = $this->settings('mattermost/username', false);
        if ($username) {
            $responseData['username'] = $username;
        }

        if (file_exists($this->settings('appDir') . '/public/icon.png')) {
            $responseData['icon_url'] = $this->settings('baseUrl') . '/icon.png';
        }

        if (is_array($attachments)) {
            $responseData['attachments'] = $attachments;
        }

        return $responseData;
    }
}