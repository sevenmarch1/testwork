<?php

namespace Vnet\Theme;

use Vnet\Helpers\ArrayHelper;

class Template
{

    /**
     * @var string
     */
    private static $pathTemplates = THEME_PATH . 'template-parts';
    private static $pathSections = THEME_PATH . 'template-sections';

    /**
     * @var self[]
     */
    private static $arTemplates = [];

    /**
     * @var self[]
     */
    private static $arSections = [];

    private $name = '';

    private $args = [];

    private $html = '';

    private $file = '';

    private $relativeFile = '';

    private $key = '';


    /**
     * 
     * @param string $name относительный путь к файлу без расширения
     * @param mixed $args [optional]
     * 
     * @return self
     * 
     */
    static function getTemplate($name, $args = [])
    {
        $file = self::$pathTemplates . '/' . $name . '.php';

        if (!file_exists($file)) {
            self::consoleError("Template [$name] does not exists");
        }

        $key = md5($_SERVER['REQUEST_URI'] . $name . serialize($args));

        if (!isset(self::$arTemplates[$key])) {
            self::$arTemplates[$key] = new self($name, $key, $file, $args);
        }

        return self::$arTemplates[$key];
    }

    public static function homeUrl()
    {
        return home_url();
    }

    static function theTemplate($name, $args = [])
    {
        self::getTemplate($name, $args)->render();
    }


    /**
     * 
     * @param string $name относительный путь к файлу без расширения
     * @param mixed $args [optional]
     * 
     * @return self
     * 
     */
    static function getSection($name, $args = [])
    {
        $key = md5($_SERVER['REQUEST_URI'] . $name . serialize($args));

        $file = self::$pathSections . '/' . $name . '.php';

        if (!file_exists($file)) {
            self::consoleError("Section [$name] does not exists");
        }

        if (!isset(self::$arSections[$key])) {
            self::$arSections[$key] = new self($name, $key, $file, $args);
        }

        return self::$arSections[$key];
    }

    static function theSection($name, $args = [])
    {
        self::getSection($name, $args)->render();
    }


    /**
     * - Формирует html тег с содержимым
     * - Удобно для оборачивания в теги шаблоны либо для тегов с большим кол-вом аттрибутов
     * @param string $tag название тега (div|a|span...)
     * @param string[]|string $attrs массив атрибутов, либо строка с классом тега
     * @param string|self|string[]|self[] $content содержимое тега
     * 
     * @return self
     */
    static function getTag($tag, $attrs = [], $content = '')
    {
        $tag = strtolower($tag);
        $strAttrs = '';

        if (is_string($attrs)) {
            $attrs = ['class' => $attrs];
        }

        if (!is_array($content)) {
            $content = [$content];
        }

        foreach ($content as &$row) {
            if ($row instanceof self) {
                $row = $row->getHtml();
            }
        }

        $content = implode('', $content);

        foreach ($attrs as $key => &$val) {
            if (!$val) {
                $val = "$key";
                continue;
            }
            if (strpos("'", $val) !== false) {
                $val = "{$key}=\"{$val}\"";
            } else {
                $val = "{$key}='{$val}'";
            }
        }

        if ($attrs) {
            $strAttrs = ' ' . implode(' ', $attrs);
        }

        $singleTags = [
            'area',
            'base',
            'br',
            'col',
            'command',
            'embed',
            'hr',
            'img',
            'input',
            'keygen',
            'link',
            'meta',
            'param',
            'source',
            'track',
            'wbr'
        ];

        if (in_array($tag, $singleTags)) {
            return new self("<{$tag}{$strAttrs}>");
        }

        return new self("<{$tag}{$strAttrs}>$content</{$tag}>");
    }

    static function theTag($tag, $attrs = [], $content = '')
    {
        self::getTag($tag, $attrs, $content)->render();
    }


    /**
     * - Выводит ошибку в консоль броузера
     * @param string $msg
     */
    private static function consoleError($msg)
    {
        $trace = debug_backtrace();
        $trace = array_map(function ($val) {
            $file = str_replace($_SERVER['DOCUMENT_ROOT'], '', $val['file']);
            return [
                'file' => $file,
                'line' => $val['line'],
                'function' => $val['function']
            ];
        }, $trace);
        unset($trace[0]);
        $trace = array_values($trace);
        $trace = addslashes(json_encode($trace, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        echo "<script>console.error('$msg', '\\n', JSON.parse('$trace'));</script>";
    }

    /**
     * - Получает путь урл текущей страницы без гет параметров
     */
    public static function getCurrentUrl()
    {
        $path = self::getCurrentPath();
        return get_site_url(null, $path);
    }

    public static function getCurrentPath()
    {
        $uri = ArrayHelper::getServer('REQUEST_URI');
        if (!$uri) return '';
        $uri = preg_replace("/\?.*$/", '', $uri);
        $uri = preg_replace("/\/page\/[\d]+\/?$/", '/', $uri);
        return preg_replace("/\?.*$/", '', $uri);
    }

    private function __construct($name = '', $key = '', $file = '', $args = [])
    {
        $this->name = $name;
        $this->key = $key;
        $this->args = $args;
        $this->file = $file;
        $this->relativeFile = str_replace(THEME_PATH, '', $file);

        if (!$this->file) {
            $this->html = $this->name;
            $this->name = '_html_';
            return;
        }

        if (file_exists($this->file)) {
            $this->html = $this->fetchHtml();
        }
    }


    function __toString()
    {
        echo $this->html;
    }


    private function fetchHtml()
    {
        ob_start();

        if (is_user_logged_in()) {
            echo "\r\n<!-- [START_TEMPLATE name:{$this->name}; key:{$this->key}; file:{$this->relativeFile}] -->\r\n";
        }

        require $this->file;

        if (is_user_logged_in()) {
            echo "\r\n<!-- [END_TEMPLATE name:{$this->name}; key:{$this->key}; file:{$this->relativeFile}] -->\r\n";
        }

        return ob_get_clean();
    }


    function getHtml()
    {
        return $this->html;
    }


    function getArg($key, $def = null, $checkEmpty = false)
    {
        $res = ArrayHelper::get($this->args, $key, $def);
        if (!$checkEmpty) {
            return $res;
        }
        return $res ? $res : $def;
    }


    function getName()
    {
        return $this->name;
    }

    function render()
    {
        echo $this->html;
    }
}
