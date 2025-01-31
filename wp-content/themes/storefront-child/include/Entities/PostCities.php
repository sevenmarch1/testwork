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
}
