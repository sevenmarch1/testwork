<?php

namespace Vnet;

use Vnet\Traits\Instance;

class Admin
{
    use Instance;

    function setup(): self
    {
        // добавление метобоксов 
        add_action('add_meta_boxes', [__CLASS__, 'add_cities_metabox']);
        add_action('save_post_cities', [__CLASS__,  'save_cities_metabox']);
        return $this;
    }

    static function add_cities_metabox()
    {
        add_meta_box(
            'cities_metabox',
            'Кординаты',
            [__CLASS__, 'display_cities_metabox'],
            'cities',
        );
    }


    static function display_cities_metabox($post)
    {
        $latitude = get_post_meta($post->ID, '_latitude', true);
        $longitude = get_post_meta($post->ID, '_longitude', true);
        wp_nonce_field(basename(__FILE__), 'cities_metabox_nonce');
        ?>
        <p>
            <label for="latitude">Широта:</label>
            <input type="text" name="latitude" id="latitude" value="<?= esc_attr($latitude); ?>" style="width:100%;" />
        </p>
        <p>
            <label for="longitude">Долгота:</label>
            <input type="text" name="longitude" id="longitude" value="<?= esc_attr($longitude); ?>" style="width:100%;" />
        </p>
        <?php
    }


    static function save_cities_metabox()
    {
        $id = isset($_POST['ID']) ? $_POST['ID'] : false;

        if (!isset($_POST['cities_metabox_nonce']) || !wp_verify_nonce($_POST['cities_metabox_nonce'], basename(__FILE__))) {
            return;
        }
    
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
    
        if (!current_user_can('edit_post', $id)) {
            return;
        }
    
        if (isset($_POST['latitude'])) {
            update_post_meta($id, '_latitude', sanitize_text_field($_POST['latitude']));
        }
    
        if (isset($_POST['longitude'])) {
            update_post_meta($id, '_longitude', sanitize_text_field($_POST['longitude']));
        }
    }
}