<?php

namespace Vnet\Entities;

use Vnet\Cache;
use Vnet\Helpers\Acf;
use Vnet\ObjectCache;

class Term
{

    /**
     * @var string
     */
    protected static $tax = '';

    /**
     * @var static[]
     */
    private static $instances = [];

    /**
     * @var static[]
     */
    private static $instancesSlugs = [];

    /**
     * @var \WP_Term
     */
    protected $term = null;


    protected function __construct(\WP_Term $term)
    {
        $this->term = $term;
    }


    /**
     * @param int $termId
     * @return null|static
     */
    static function getById(int $termId): ?self
    {
        $key = static::$tax . '_' . $termId;

        if (array_key_exists($key, self::$instances)) {
            return self::$instances[$key];
        }

        $term = get_term_by('id', $termId, static::$tax);

        if (!$term || is_wp_error($term)) {
            self::$instances[$key] = null;
        } else {
            self::$instances[$key] = new static($term);
        }

        return self::$instances[$key];
    }

    /**
     * - Получает элемент по slug
     * @param string $slug 
     * @return null|static
     */
    static function getBySlug(string $slug): ?self
    {
        $key = static::$tax . '_' . $slug;

        if (array_key_exists($key, self::$instancesSlugs)) {
            return self::$instancesSlugs[$key];
        }

        $term = get_term_by('slug', esc_sql($slug), static::$tax);

        if (!$term) {
            self::$instancesSlugs[$key] = null;
        } else {
            self::$instancesSlugs[$key] = new static($term);
        }

        return self::$instancesSlugs[$key];
    }


    /**
     * - Получает все термины
     * @see https://wp-kama.ru/function/get_terms
     * @param array $args
     * @return static[] 
     */
    static function getAll(array $args = []): array
    {
        $terms = get_terms(array_merge([
            'hide_empty' => false,
            'taxonomy' => static::$tax
        ], $args));

        if (is_wp_error($terms)) {
            return [];
        }

        $res = [];

        foreach ($terms as $term) {
            $res[] = new static($term);
        }

        return $res;
    }


    function getName(): string
    {
        return $this->term->name;
    }


    function getId(): int
    {
        return $this->term->term_id;
    }


    function getSlug(): string
    {
        return $this->term->slug;
    }



    protected function fetchCache(string $key, callable $fetchFuntion)
    {
        return Cache::fetch($this->getCacheKey($key), $fetchFuntion);
    }


    protected function setCache(string $key, $value)
    {
        Cache::set($this->getCacheKey($key), $value);
    }


    protected function getCache(string $key, $def = null)
    {
        return Cache::get($this->getCacheKey($key), $def);
    }


    private function getCacheKey(string $key): string
    {
        return 'terms:' . $this->getId() . ':' . $key;
    }
}
