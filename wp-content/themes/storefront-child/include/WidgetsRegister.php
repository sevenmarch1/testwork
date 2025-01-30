<?php

namespace Vnet;

use Vnet\Traits\Instance;


class WidgetsRegister
{
    use Instance;

    function setup(): self
    {
        add_action('widgets_init', [__CLASS__,'register_weather_widget']);
        return $this;
    }


    static function register_weather_widget()
    {
        register_widget('Vnet\Widgets\WeatherWidget');
    }

}
