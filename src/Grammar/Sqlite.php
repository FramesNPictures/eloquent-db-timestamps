<?php

namespace Fnp\Eloquent\Grammar;

use Fnp\Eloquent\Grammar;

class Sqlite implements Grammar
{
    /**
     * Returns server side timestamp SQL command
     *
     * @return string
     */
    public static function timestamp()
    {
        return "DATETIME('now')";
    }
}