<?php

/**
 * Template Name: Погода городов
 * Template Post Type: page
 */

use Vnet\Entities\PostCities;

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
            <?php foreach ($data as $row) { 

                $city = PostCities::getById($row['ID']);
                $temp = $city->getTemperature();
                ?>
                <tr>
                    <td><?= esc_html($row['city']); ?></td>
                    <td><?= esc_html($row['country'] ?: 'Не указано'); ?></td>
                    <td><?= $temp ? 'Температура: ' . $temp . ' °C' : 'Не удалось получить данные о погоде.'; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php do_action('after_weather_table'); ?>
</div>

<?php get_footer(); ?>