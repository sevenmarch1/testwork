<?php

/**
 * Template Name: Погода городов
 * Template Post Type: page
 */

use Vnet\Entities\PostCities;
use Vnet\Theme\Template;

get_header();

$data = PostCities::getWeatherData();

?>

<div class="weather-container">
    <?php do_action('before_weather_table'); ?>

    <input type="text" id="weather-search" placeholder="Введите город..." />
    <table id="weather-table">
        <thead>
            <tr>
                <th>Город</th>
                <th>Страна</th>
                <th>Температура</th>
            </tr>
        </thead>
        <tbody>
            <?php Template::theTemplate('page-weather-rows', ['data' => $data]); ?>
        </tbody>
    </table>

    <?php do_action('after_weather_table'); ?>
</div>

<?php get_footer(); ?>