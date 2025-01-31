<?php

/**
 * - Базовый класс для работы с API
 */

namespace Vnet\Api;

class Api
{

    /**
     * @var static[]
     */
    private static $insts = [];


    protected function __construct() {}


    /**
     * @return static 
     */
    final static function getInstance(): self
    {
        $class = get_called_class();
        if (!isset(self::$insts[$class])) {
            self::$insts[$class] = new static();
        }
        return self::$insts[$class];
    }

    /**
     * - Объединяет части пути
     * - Верент путь буз закрывающего /
     * @param string[] $parts
     * @return string
     */
    protected function joinPath(...$parts)
    {
        $str = preg_replace("/\/$/", '', $parts[0]);
        $total = count($parts);

        for ($i = 1; $i < $total; $i++) {
            /**
             * @var string $cur
             */
            $cur = $parts[$i];
            $cur = trim($cur);
            if (!$cur) {
                continue;
            }
            $cur = preg_replace("/^\//", '', $cur);
            $cur = preg_replace("/\/$/", '', $cur);
            $str .= '/' . $cur;
        }

        return $str;
    }


    /**
     * - Асинхронно выполняет несколько запросов
     * @param array $arParams массив массивов параметров
     * @return ApiResponse[]
     */
    protected function multiFetch(array $arParams): array
    {
        $arCh = [];
        $mh = curl_multi_init();

        foreach ($arParams as $key => $params) {
            $arCh[$key] = $this->getCurl($params['url'], $params);
        }

        foreach ($arCh as $key => $ch) {
            curl_multi_add_handle($mh, $ch);
        }

        $run = null;

        do {
            curl_multi_exec($mh, $run);
        } while ($run);


        $res = [];

        foreach ($arCh as $key => $ch) {
            $response = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            $res[$key] = new ApiResponse($info, $response, $this->getParamsFormat($arParams[$key]));
        }

        return $res;
    }


    /**
     * - Отправляет curl запрос
     * @param array $params массив параметров запроса запроса
     * [url]* string бсолютный url адрес
     * [format] string формат ответа (xml|json|raw)
     * [method] string метод запроса
     * [timeout] int время запроса
     * [authBasic] string значение для заголовка базовой авторизации
     * [port] int порт для подключения
     * [headers] array дополнительные заголовки
     * [params] array параметры запроса
     * [body] array тело запроса
     * [curlopt] array массив опций для curl
     * @return ApiResponse
     */
    protected function fetch(array $params = []): ApiResponse
    {
        $params['format'] = $this->getParamsFormat($params);

        $res = $this->fetchRaw($params['url'], $params);

        $response = new ApiResponse($res['info'], $res['body'], $res['format']);

        return $response;
    }


    private function getParamsFormat(array $params): string
    {
        if (!empty($params['format'])) {
            return $params['format'];
        }
        return 'json';
    }


    /**
     * - Выполняет запрос без формирования объекта ответа
     * - Использовать в случае с большими данными (может нехватить памяти для формирования объекта ответа)
     * @param string $url абсолютный адрес запроса
     * @param array $params @see self::fetch() 
     * @return array [body => string, info => array, format => string]
     */
    private function fetchRaw($url, $params)
    {
        $ch = $this->getCurl($url, $params);

        $format = 'raw';
        if (!empty($params['format'])) {
            $format = $params['format'];
        }

        $body = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        return ['info' => $info, 'body' => $body, 'format' => $format];
    }


    /**
     * - Парсит параметры для curl запроса
     * @param string $url абсолютный адрес запроса
     * @param array $params @see self::fetch() 
     */
    function getCurl($url, $params)
    {
        $method = 'GET';
        if (!empty($params['method'])) {
            $method = strtoupper($params['method']);
        }

        if (!empty($params['params'])) {
            if (is_array($params['params'])) {
                $url .= '?' . http_build_query($params['params']);
            } else {
                $url .= '?' . $params['params'];
            }
        }

        $headers = [];

        if (!empty($params['headers'])) {
            $headers = $params['headers'];
        }

        // устанавливаем заголовок HTTP авторизации
        if (!empty($params['authBasic'])) {
            $auth = base64_encode($params['authBasic']);
            $headers[] = "Authorization: Basic {$auth}";
        }

        $ch = curl_init($url);

        // устанавливаем порт подключения
        if (!empty($params['port'])) {
            curl_setopt($ch, CURLOPT_PORT, $params['port']);
        }

        // устанавливаем метод запроса
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        foreach ($headers as $key => &$head) $head = "$key: $head";

        // устанавливаем заголовки
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // устанавливаем тело запроса
        if (!empty($params['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
        }

        // определяем время выполнения
        $timeout = 300;
        if (!empty($params['timeout'])) {
            $timeout = $params['timeout'];
        }

        // устанавливаем базовые опции
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);

        // устанавливаем дополнительные опции
        if (!empty($params['curlopt'])) {
            foreach ($params['curlopt'] as $opt => $val) {
                curl_setopt($ch, $opt, $val);
            }
        }

        return $ch;
    }
}
