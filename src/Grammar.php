<?php

namespace Fnp\Eloquent;

interface Grammar
{
    /**
     * Returns server side timestamp SQL command
     *
     * @return string
     */
    public static function timestamp();
}