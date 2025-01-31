<?php

namespace Vnet\Entities;

use Vnet\Api\ApiWeather;
use Vnet\Cache;
use Vnet\Constants\Cache as ConstantsCache;
use Vnet\Constants\PostTypes;
use Vnet\Constants\Taxonomies;

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

        if ($temp) {
            return $temp;
        }

        return null;
    }


    //получает данные городов
    static function getWeatherData($search = '')
    {
        global $wpdb;

        $tax = Taxonomies::CITIES_COUNTRIES;

        $query = "SELECT 
                p.ID,
                p.post_title AS city,
                t.name AS country
              FROM {$wpdb->prefix}posts AS p
              LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON p.ID = tr.object_id
              LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
              LEFT JOIN {$wpdb->prefix}terms AS t ON tt.term_id = t.term_id
              WHERE p.post_type = 'cities' AND p.post_status = 'publish' AND (tt.taxonomy = '{$tax}' OR tt.taxonomy IS NULL)";
        
        if (!empty($search)) {
            $query .= $wpdb->prepare(" AND p.post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
        }
        
        $results = $wpdb->get_results($query, ARRAY_A);

        return $results;
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
