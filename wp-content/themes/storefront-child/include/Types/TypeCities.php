<?php

namespace Vnet\Types;

use Vnet\Constants\PostTypes;

class TypeCities extends PostType
{

    protected $slug = PostTypes::CITIES;


    function setup()
    {
        $this->menuAwesomeIco('f64f');
        $this->menuColorOrange();

        $this->addColumn('latitude', 'Широта', function ($postId) {
            $str = '';
            if ($latitude = get_post_meta($postId, '_latitude', true)) {
                $str .= $latitude;
            }
            return $str;
        }, 'title');

        $this->addColumn('longitude', 'Долгота', function ($postId) {
            $str = '';
            if ($longitude = get_post_meta($postId, '_longitude', true)) {
                $str .= $longitude;
            }
            return $str;
        }, 'latitude');


        $this->register([
            'label' => 'Города',
            'has_archive' => false,
            'exclude_from_search' => false,
            'supports' => ['title']
        ]);
    }
}
