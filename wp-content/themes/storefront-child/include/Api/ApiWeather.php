<?php


namespace Vnet\Api;

class ApiWeather extends Api
{

    private $apiKey = '';
    private $apiUrl = 'https://api.openweathermap.org/data/2.5/';

    //данные о погоде за какой период
    private $apiExclude = 'daily';

    //единицы измерения
    private $apiUnits = 'metric';

    private $cacheKey = '_city_temperature'; 

    function setup($params = [])
    {
        if (!empty($params['apiKey'])) {
            $this->apiKey = $params['apiKey'];
        }
    }


    /**
     * - Получает данные с апи
     * @param string[] $args - параметры запроса
     * @return array
     */
    public function getApiData($args = []): array
    {

        $url = 'weather';

        $args['format'] = 'json';
        $args['appid'] = $this->apiKey;
        $args['units'] = $this->apiUnits;

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


    /**
     * - Получает данные о погоде конкретного города
     * @param int $cityId - id города
     * @param string $lat - широта
     * @param string $lon - долгота
     * @return null|string
     */
    public function getCitiesWeather(int $cityId, string $lat, string $lon) 
    {
        if (!$lat || !$lon) {
            return null;
        }

        $cacheTemp = $this->getCachedWeatherData($cityId);

        if($cacheTemp){
            return $cacheTemp;
        }

        $response = $this->getApiData([
            'lat' => $lat,
            'lon' => $lon
        ]);

        if (!isset($response['main']) || empty($response['main'])) {
            return null;
        }

        $temp = $response['main']['temp'];

        $cachedData = [
            'temp' => $temp,
            'timestamp'   => time()
        ];
    
        update_post_meta($cityId, $this->cacheKey, $cachedData);

        return $temp;
    }


    /**
     * - Получает данные из базы, если не прошел еще час
     */
    public function getCachedWeatherData($cityId) {

        $cachedData = get_post_meta($cityId, $this->cacheKey, true); 

        if ($cachedData && (time() - $cachedData['timestamp'] < 3600)) {
            return $cachedData['temp'];
        }

        return null;

    }
}
