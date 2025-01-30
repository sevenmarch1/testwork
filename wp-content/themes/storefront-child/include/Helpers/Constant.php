<?php

namespace Vnet\Helpers;

class Constant
{

    static function get(string $const, $def = null)
    {
        if (defined($const)) {
            return constant($const);
        }
        return $def;
    }
}
