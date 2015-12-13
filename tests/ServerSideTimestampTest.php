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
            'quote'    => '"',
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
            'quote'     => '"',
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
            'quote'    => '`',

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
        $capsule = $this->setConnection($connection);

        $capsule->connection()->enableQueryLog();

        $table       = new TestModel();
        $table->name = 'One';
        $table->save();

        /** @var \Fnp\Eloquent\Grammar $grammar */
        $grammar      = $this->config[ $connection ][ 'grammar' ];
        $quote        = function ($name) use ($connection) {
            $quote = $this->config[ $connection ][ 'quote' ];

            return $quote . $name . $quote;
        };
        $timestamp    = $grammar::timestamp();
        $generatedSql = $capsule->connection()->getQueryLog()[ 0 ][ 'query' ];
        $pattern      = '#insert into .test_table. \(.name., .updated_at., .created_at.\) values \(\?, (.*), (.*)\)#';
        $match        = [];
        $result       = preg_match($pattern, $generatedSql, $match);

        $this->assertEquals($match[ 1 ], $timestamp);
        $this->assertEquals($match[ 2 ], $timestamp);
    }

    function testNotUpdated()
    {
        $capsule = $this->setConnection(self::CONNECTION_SQLITE);

        $table       = new TestModelNotUpdated();
        $table->name = 'Two';
        $table->save();

        $this->assertNotNull($table->created_at);
        $this->assertNull($table->updated_at);
        $this->assertNull($table->updated_custom);
    }

    function testCustomCreated()
    {
        $capsule = $this->setConnection(self::CONNECTION_SQLITE);

        $table       = new TestModelUpdatedCustom();
        $table->name = 'Two';
        $table->save();

        $this->assertNotNull($table->created_at);
        $this->assertNull($table->updated_at);
        $this->assertNotNull($table->updated_custom);
        $this->assertEquals($table->created_at, $table->updated_custom);
    }

    /**
     * @param $connection
     *
     * @return \Illuminate\Database\Capsule\Manager
     */
    protected function setConnection($connection)
    {
        $capsule = new Capsule();
        $capsule->addConnection($this->config[ $connection ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $capsule->schema()->create('test_table', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->dateTime('updated_custom')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        return $capsule;
    }

}