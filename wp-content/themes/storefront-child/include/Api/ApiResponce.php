<?php

/**
 * - Класс для работы с ответом по апи
 */

namespace Vnet\Api;


class ApiResponse
{

    private $format = 'raw';

    /**
     * - Код ответа
     * @var int
     */
    private $code = 0;

    /**
     * - результат curl_getinfo()
     * @var array
     */
    private $info = [];

    /**
     * - необработанный ответ
     * - Будет установлен в случае если не передан формат
     *   или если формат равен raw
     * @var string
     */
    private $rawResponse = '';

    /**
     * - обработанный ответ
     * - Устанвливается если передан формат
     *   отличный от raw
     * @var null|array|SimpleXMLElement
     */
    private $response = null;


    /**
     * @param array $info результат curl_getinfo()
     * @param string $body тело ответа
     * @param string $format xml|json|raw ожидаемый формат ответа
     */
    function __construct($info, $body, $format = 'json')
    {
        $this->info = $info;
        $this->code = $this->info['http_code'];

        if (!$this->isSuccess() || $format === 'raw') {
            $this->rawResponse = $body;
            return;
        }

        if ($format === 'json' && $body) {
            $this->response = json_decode($body, true);
            return;
        }

        if ($format === 'xml' && $body) {
            $this->response = new \SimpleXMLElement($body);
            return;
        }

        $this->rawResponse = $body;
    }


    function isSuccess()
    {
        return ($this->code >= 200 && $this->code < 300);
    }


    function getCode()
    {
        return $this->code;
    }


    function getUrl(): string
    {
        return $this->info['url'];
    }


    function getResponse()
    {
        return $this->response;
    }


    function getRawResponse()
    {
        return $this->rawResponse;
    }


    function getInfo()
    {
        return $this->info;
    }
}
