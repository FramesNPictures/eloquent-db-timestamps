<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class ServerSideTimestampTest extends PHPUnit_Framework_TestCase
{
    const CONNECTION_SQLITE   = 'sqlite';
    const CONNECTION_MYSQL    = 'mysql';
    const CONNECTION_POSTGRES = 'pgsql';

    protected $config = [

        self::CONNECTION_SQLITE => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'grammar'  => \Fnp\Eloquent\Grammar\Sqlite::class,
        ],

        self::CONNECTION_MYSQL => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'fnp_test',
            'username'  => 'travis',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => FALSE,
            'grammar'   => \Fnp\Eloquent\Grammar\Mysql::class,
        ],

        self::CONNECTION_POSTGRES => [
            'driver'   => 'pgsql',
            'host'     => 'localhost',
            'database' => 'fnp_test',
            'username' => 'postgres',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
            'grammar'  => \Fnp\Eloquent\Grammar\Pgsql::class,
        ],

    ];

    public function connectionsProvider()
    {
        return [
            [self::CONNECTION_SQLITE],
            [self::CONNECTION_MYSQL],
            [self::CONNECTION_POSTGRES],
        ];
    }

    /**
     * @param $connection
     *
     * @dataProvider connectionsProvider
     */
    function testDba($connection)
    {
        $capsule = new Capsule();
        $capsule->addConnection($this->config[ $connection ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $capsule->schema()->create('test_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $capsule->connection()->enableQueryLog();

        $table       = new TestModel();
        $table->name = 'One';
        $table->save();

        /** @var \Fnp\Eloquent\Grammar $grammar */
        $grammar      = $this->config[ $connection ][ 'grammar' ];
        $timestamp    = $grammar::timestamp();
        $generatedSql = $capsule->connection()->getQueryLog()[ 0 ][ 'query' ];
        $expectedSql  = sprintf(
            'insert into "test_table" ("name", "updated_at", "created_at") values (?, %s, %s)',
            $timestamp,
            $timestamp
        );

        $this->assertEquals($expectedSql, $generatedSql);
    }

}