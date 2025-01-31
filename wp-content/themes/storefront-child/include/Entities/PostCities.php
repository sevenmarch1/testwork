<?php

namespace Vnet\Entities;

use Vnet\Api\ApiWeather;
use Vnet\Cache;
use Vnet\Constants\Cache as ConstantsCache;
use Vnet\Constants\PostTypes;

class PostCities extends Post
{

    protected static $postType = PostTypes::CITIES;


    /**
     * - Получает ширину
     * @return string
     */
    function getLat(): string
    {
        return (string)$this->getMeta('_latitude', true);
    }


    /**
     * - Получает долготу
     * @return string
     */
    function getLon(): string
    {
        return (string)$this->getMeta('_longitude', true);
    }


   /**
     * - Получает температуру
     * @return null|string
     */
    function getTemperature() 
    {
        $temp = ApiWeather::getInstance()->getCitiesWeather($this->getId(), $this->getLat(), $this->getLon());

        if($temp){
            return $temp;
        }

        return null;
    }


    /**
     * - Получает активные города
     * @return self[]
     */
    static function getActive(int $perPage = -1, int $page = 1): array
    {
        return Cache::fetch(ConstantsCache::CITIES_ACTIVE . $perPage . $page, function () use ($perPage, $page) {
            $args = [
                'posts_per_page' => $perPage,
                'paged' => $page,
            ];

            return parent::getPublished($args);
        });
    }

    /**
     * - Получает активные города количество
     * @return int
     */
    static function getActiveTotal(int $perPage = -1): int
    {
        $total =  Cache::fetch(ConstantsCache::CITIES_ACTIVE . $perPage, function () use ($perPage) {
            $args = [
                'posts_per_page' => $perPage,
            ];

            return parent::getPublished($args);
        });

        return count($total);
    }
}
