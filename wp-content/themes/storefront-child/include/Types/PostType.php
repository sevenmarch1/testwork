<?php

namespace Vnet\Types;

use Vnet\Helpers\ArrayHelper;

class PostType extends Core
{

    /**
     * @var null|\WP_Post_Type
     */
    protected $postType = null;


    /**
     * @see https://wp-kama.ru/function/register_post_type
     * @param array $params 
     */
    protected function register(array $params)
    {
        add_action('init', function () use ($params) {
            $postType = register_post_type($this->getSlug(), $this->filterParams($params));
            if (!is_wp_error($postType)) {
                $this->postType = $postType;
            }
        });
    }


    private function filterParams(array $params): array
    {
        $defLabels = [
            'name' => 'Элементы',
            'singular_name' => 'Элемент',
            'add_new' => 'Добавить элемент',
            'add_new_item' => 'Добавление элемента',
            'edit_item' => 'Редактирование элемента',
            'new_item' => 'Новый элемент',
            'view_item' => 'Смотреть элемент',
            'search_items' => 'Искать элемент',
            'not_found' => 'Не найдено',
            'not_found_in_trash' => 'Не найдено в корзине',
            'parent_item_colon' => 'Родительский элемент:',
            'menu_name' => 'Элементы'
        ];

        $labels = $params['labels'] ?? [];

        if (empty($params['label'])) {
            $params['label'] = 'Элементы';
        }

        if (empty($labels['name'])) {
            $labels['name'] = $params['label'];
        }

        if (empty($labels['menu_name'])) {
            $labels['menu_name'] = $params['label'];
        }

        $labels = array_merge($defLabels, $labels);

        $params['labels'] = $labels;

        $params['public'] = $params['public'] ?? true;
        $params['has_archive'] = $params['has_archive'] ?? true;

        return $params;
    }


    /**
     * - Заголовок будет генерироваться автоматически
     * @param callable $getTitle функция для получения заголовка
     * @return void 
     */
    protected function autoTitle(callable $getTitle)
    {
        $this->theAdminStyle("
            body.post-type-{$this->slug} #titlediv #titlewrap {
                pointer-events: none!important;
                opacity: .4!important;
                user-select: none!important;
            }
        ");

        add_filter('wp_insert_post_data', function ($data) use ($getTitle) {
            if ($data['post_type'] !== $this->slug) {
                return $data;
            }
            $data['post_title'] = call_user_func($getTitle, $data);
            return $data;
        }, 99);
    }


    protected function addImgColumn(string $position = 'title', string $label = 'Картинка', ?callable $callback = null)
    {
        if ($callback === null) {
            $callback = function ($postId) {
                if ($img = get_the_post_thumbnail_url($postId)) {
                    echo '<img src="' . $img . '" style="width: 120px;">';
                }
            };
        }
        $this->addColumn('image', $label, $callback, $position);
    }


    /**
     * - Добавляет колонку на странице списка терминов
     * @param string $colKey уникальный ключ новой колонки
     * @param string $colLabel Название колонки
     * @param callable $getValCallback функция для получения значения ячейки
     * @param string $position либо ключ колонки после которой вставить текущую, либо first|last
     */
    protected function addColumn(string $colKey, string $colLabel, callable $getValCallback, string $position = 'last')
    {
        add_filter("manage_{$this->slug}_posts_columns", function (array $columns) use ($colKey, $position, $colLabel) {
            if ($position === 'last') {
                $position = array_keys($columns)[count(array_keys($columns)) - 1];
            } else if ($position === 'first') {
                $position = 'cb';
            }
            $columns = ArrayHelper::insert($columns, $position, [$colKey => $colLabel]);
            return $columns;
        });
        add_action("manage_{$this->slug}_posts_custom_column", function ($column, $postId) use ($colKey, $getValCallback) {
            if ($column === $colKey) {
                echo call_user_func($getValCallback, (int)$postId);
            }
        }, 10, 3);
    }


    protected function sortableColumn(string $colKey)
    {
        add_filter("manage_edit-{$this->slug}_sortable_columns", function ($columns) use ($colKey) {
            $columns[$colKey] = $colKey;
            return $columns;
        });
    }


    protected function addAdminFilter(callable $drawCallback, callable $filterCallback)
    {
        add_action('restrict_manage_posts', function () use ($drawCallback) {
            global $pagenow;
            if (!is_admin() || $pagenow !== 'edit.php' || $_GET['post_type'] !== $this->slug) {
                return;
            }
            call_user_func($drawCallback);
        });

        add_filter('parse_query', function (\WP_Query $query) use ($filterCallback) {
            global $pagenow;
            if (!is_admin() || $pagenow !== 'edit.php' || $_GET['post_type'] !== $this->slug || !$query->is_main_query()) {
                return;
            }
            call_user_func($filterCallback, $query);
        });
    }


    protected function menuColorOrange()
    {
        $this->menuColor('orange');
    }


    protected function menuColorGreen()
    {
        $this->menuColor('green');
    }


    /**
     * - Устанавливает цвет пункту меню
     * @param string $color orange|green
     */
    protected function menuColor(string $color)
    {
        $this->addMenuClass('theme-menu-item', "c-{$color}");
    }


    protected function menuAwesomeIco(string $ico)
    {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
        });

        $this->addMenuClass('theme-menu-item', 'awesome-icon');

        $this->theAdminStyle("
            .theme-menu-item.awesome-icon#menu-posts-{$this->slug} .wp-menu-image {
                font-family: \"Font Awesome 5 Free\";
            }
            .theme-menu-item.awesome-icon#menu-posts-{$this->slug} .wp-menu-image:before {
                content: \"\\{$ico}\";
                font-family: inherit;
                font-weight: bold;
            }
        ");
    }


    /**
     * - Скрывает создание и список
     * - Перенаправляет на страницу таксономии
     * @param string $tax
     */
    protected function groupTaxonomies(string $tax)
    {
        $url = '/wp-admin/edit-tags.php?taxonomy=' . $tax . '&post_type=' . $this->slug;

        // скрываем пункты меню
        $this->theAdminStyle("
            #menu-posts-{$this->slug} .wp-submenu .wp-first-item,
            #menu-posts-{$this->slug} .wp-submenu .wp-first-item + li {
                display: none!important;
            }
        ");

        // меняем основную ссылку
        $this->theAdminScript("
            jQuery('#menu-posts-{$this->slug} a').first().attr('href', '{$url}');
        ");

        // выполняем редирект если пользователь попал на скрытые страницы

        $file = basename($_SERVER['SCRIPT_NAME']);

        if (empty($_GET['post_type']) || !in_array($file, ['edit.php', 'post-new.php'])) {
            return;
        }

        if ($_GET['post_type'] !== $this->slug) {
            return;
        }

        wp_redirect($url);

        exit;
    }



    /**
     * - Должен быть вызван вначале!
     * - Устанавливает значение колонки в таблице posts из значения ACF поля
     * @param string $acfName название ACF поля
     * @param string $postField название колонки из таблицы posts
     * @return void 
     */
    protected function acfToPostField(string $acfName, string $postField)
    {
        $postField = strtolower($postField);

        $allowFields = [
            'post_title',
            'post_author',
            'post_content',
            'post_excerpt'
        ];

        if (!in_array($postField, $allowFields)) {
            return;
        }

        add_filter("acf/load_value/name={$acfName}", function ($value, int $postId) use ($postField) {
            if (get_post_type($postId) !== $this->getSlug()) {
                return $value;
            }
            $post = get_post($postId);
            if (!$post || !property_exists($post, $postField)) {
                return $value;
            }
            return $post->$postField ?: $value;
        }, 10, 2);

        add_filter("acf/update_value/name={$acfName}", function ($value, int $postId) use ($acfName, $postField) {
            if (get_post_type($postId) !== $this->getSlug()) {
                return $value;
            }
            if (is_string($value) || is_numeric($value)) {
                $GLOBALS['acf_top_post_field_' . $postField . '_' . $acfName . '_' . $postId] = $value;
                return null;
            }
            return $value;
        }, 10, 2);

        $this->onAcfSave(function (int $postId) use ($acfName, $postField) {
            if (get_post_type($postId) !== $this->getSlug()) {
                return;
            }
            $value = $GLOBALS['acf_top_post_field_' . $postField . '_' . $acfName . '_' . $postId] ?? '';
            wp_update_post(['ID' => $postId, $postField => $value]);
            if (isset($GLOBALS['acf_top_post_field_' . $postField . '_' . $acfName . '_' . $postId])) {
                unset($GLOBALS['acf_top_post_field_' . $postField . '_' . $acfName . '_' . $postId]);
            }
        });
    }

    /**
     * - Срабатывает после обновления полей ACF
     * @see https://www.advancedcustomfields.com/resources/acf-save_post/
     * @param callable $callback 
     * @return void 
     */
    protected function onAcfSave(callable $callback)
    {
        add_action('acf/save_post', function ($postId) use ($callback) {
            if (!is_numeric($postId)) {
                return;
            }
            if (get_post_type($postId) !== $this->getSlug()) {
                return;
            }
            call_user_func($callback, $postId);
        }, 10, 1);
    }

    /**
     * - Удаляет колонку
     * @param string $colKey 
     * @return void 
     */
    protected function removeColumn(string $colKey)
    {
        add_filter("manage_{$this->slug}_posts_columns", function (array $columns) use ($colKey) {
            if (isset($columns[$colKey])) {
                unset($columns[$colKey]);
            }
            return $columns;
        });
    }
}
