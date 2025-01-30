<?php

namespace Vnet\Types;

use Vnet\Constants\PostTypes;
use Vnet\Constants\Taxonomies;

class TaxCitiesCountries extends Taxonomy
{

    protected $slug = Taxonomies::CITIES_COUNTRIES;


    function setup()
    {
        $this->register([
            'label' => 'Страны',
            'publicly_queryable' => false
        ], [PostTypes::CITIES]);
    }
}