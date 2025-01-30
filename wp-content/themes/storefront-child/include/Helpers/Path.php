<?php

namespace Vnet\Helpers;

class Path
{

    static function join(...$parts): string
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
}
