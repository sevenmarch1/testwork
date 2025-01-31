<?php

/**
 * Template Name: Погода городов
 * Template Post Type: page
 */

get_header();
?>

<div class="weather-container">
    <?php do_action('before_weather_table'); ?>
    
    <input type="text" id="weather-search" placeholder="Введите город..." />
    <table id="weather-table">
        <thead>
            <tr>
                <th>Страна</th>
                <th>Город</th>
                <th>Температура</th>
            </tr>
        </thead>
        <tbody>
            <!-- Данные -->
        </tbody>
    </table>

    <?php do_action('after_weather_table'); ?>
</div>

<?php get_footer(); ?>