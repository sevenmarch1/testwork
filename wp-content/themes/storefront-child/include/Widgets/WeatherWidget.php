<?php

namespace Vnet\Widgets;

class WeatherWidget extends \WP_Widget {
    
    function __construct() {
        parent::__construct(
            'weather_widget',
            __('Погода городов', 'vnet'),
            ['description' => __('Виджет с выбором города, и показам температуры для него', 'vnet')]
        );
    }

    // Форма в админке
    function form($instance) {
        $selected_post = !empty($instance['selected_post']) ? $instance['selected_post'] : '';
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
                    <option value="<?= $post->ID; ?>" <?= selected($selected_post, $post->ID, false); ?>><?= esc_html($post->post_title); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    // Сохранение настроек
    function update($new_instance, $old_instance) {
        $instance = [];
        $instance['selected_post'] = (!empty($new_instance['selected_post'])) ? sanitize_text_field($new_instance['selected_post']) : '';
        return $instance;
    }

    // Вывод виджета на сайт
    function widget($args, $instance) {
        if (!empty($instance['selected_post'])) {
            $post_id = $instance['selected_post'];
            $post_title = get_the_title($post_id);
            $post_link = get_permalink($post_id);
            echo $args['before_widget'];
            echo "<p><a href='{$post_link}'>" . esc_html($post_title) . "</a></p>";
            echo $args['after_widget'];
        }
    }
}
