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

        $this->register([
            'label' => 'Города',
            'has_archive' => false,
            'exclude_from_search' => false,
            'supports' => ['title']
        ]);
    }
}
