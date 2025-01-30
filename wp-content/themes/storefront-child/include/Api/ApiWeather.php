<?php


namespace Vnet\Api;

class ApiWeather extends Api
{

    private $apiKey = '';
    private $apiUrl = 'https://api.openweathermap.org/data/2.5/weather';

    function setup($params = [])
    {

        if (!empty($params['apiKey'])) {
            $this->apiKey = $params['apiKey'];
        }

    }


    /**
     * - Получает данные с апи
     * @param string $url - урл запроса
     * @param string[] $args - параметры запроса
     * @return array
     */
    public function getApiData($url, $args = []): array
    {

        $args['format'] = 'json';
        
        $params = [
            'url' => $this->joinPath($this->apiUrl, $url),
            'method' => 'GET',
            'timeout' => 120,
            'params' => $args
        ];

        $response = $this->fetch($params);
        if (!$response->isSuccess()) {
            return [];
        }

        $info = $response->getInfo();

        if (!$response->getResponse()) {
            return [];
        }
        return $response->getResponse();
    }
}
