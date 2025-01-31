<?php

namespace Vnet\Widgets;

use Vnet\Entities\PostCities;

class WeatherWidget extends \WP_Widget
{

    function __construct()
    {
        parent::__construct(
            'weather_widget',
            __('Погода городов', 'vnet'),
            ['description' => __('Виджет с выбором города, и показам температуры для него', 'vnet')]
        );
    }

    // Форма в админке
    function form($instance)
    {
        $selectedPost = !empty($instance['selected_post']) ? $instance['selected_post'] : '';
        $args = [
            'post_type'      => 'cities',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC'
        ];
        $posts = get_posts($args);
?>
        <p>
            <label for="<?= $this->get_field_id('selected_post'); ?>">Выберите город:</label>
            <select class="widefat" id="<?= $this->get_field_id('selected_post'); ?>" name="<?= $this->get_field_name('selected_post'); ?>">
                <option value="">— Выбрать город —</option>
                <?php foreach ($posts as $post) : ?>
                    <option value="<?= $post->ID; ?>" <?= selected($selectedPost, $post->ID, false); ?>><?= esc_html($post->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
<?php
    }

    // Сохранение настроек
    function update($newInstance, $oldInstance)
    {
        $instance = [];
        $instance['selected_post'] = (!empty($newInstance['selected_post'])) ? sanitize_text_field($newInstance['selected_post']) : '';
        return $instance;
    }

    // Вывод виджета на сайт
    function widget($args, $instance)
    {
        if (!empty($instance['selected_post'])) {
            $postId = $instance['selected_post'];
            $postTitle = get_the_title($postId);

            $city = PostCities::getById($postId);
            $temp = $city->getTemperature();
            if ($temp) {
                $tempString = 'Температура: ' . $temp . ' °C';
            } else {
                $tempString = 'Не удалось получить данные о погоде.';
            }

            echo $args['before_widget'];
            echo "<p>" . esc_html($postTitle) . "</p>";
            echo "<p>" . esc_html($tempString) . "</p>";
            echo $args['after_widget'];
        }
    }
}
