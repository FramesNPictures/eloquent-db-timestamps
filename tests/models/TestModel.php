<?php

use Fnp\Eloquent\DatabaseTimestamps;
use Illuminate\Database\Eloquent\Model as Eloquent;

class TestModel extends Eloquent
{
    use DatabaseTimestamps;

    protected $table = 'test_table';
}