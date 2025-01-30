<?php

namespace Vnet;

class Cache
{

    private static $cache = [];

    private static $prefix = '';


    /**
     * @param string|object $key 
     * @param mixed $def 
     * @return mixed 
     */
    static function get($key, $def = null)
    {
        return self::$cache[self::getCacheKey($key)] ?? $def;
    }


    /**
     * @param string|object $key 
     * @param mixed $value любое значение отличное от null
     * @return void 
     */
    static function set($key, $value)
    {
        self::$cache[self::getCacheKey($key)] = $value;
    }


    /**
     * - Получает значение из кэша
     * - Если его нет - вызовет функцию $fetchFunction для получения значения
     * @param string|object $key 
     * @param callable $fetchFunction должна вернуть любое значение отличное от null
     * @return mixed 
     */
    static function fetch($key, callable $fetchFunction)
    {
        $cache = self::get($key);

        if ($cache !== null) {
            return $cache;
        }

        $cache = call_user_func($fetchFunction);

        if ($cache === null) {
            throw new \Error("Значение кэша не может быть null");
        }

        self::set($key, $cache);

        return $cache;
    }


    private static function getCacheKey($key): string
    {
        if (is_object($key)) {
            $key = get_class($key);
        }
        if (self::$prefix) {
            $prefix = self::$prefix;
        } else {
            $prefix = $_SERVER['HTTP_HOST'] ?? '';
        }
        return $prefix ? $prefix . ':' . $key : $key;
    }
}
