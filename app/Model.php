<?php

namespace App;

use Illuminate\Database\Capsule\Manager as DB;

class Model
{
    public function __invoke()
    {
        $db = new DB;

        $dbDriver = getenv('DB_CONNECTION');
        $dbHost = getenv('DB_HOST');
        $dbName = getenv('DB_DATABASE');
        $dbUsername = getenv('DB_USERNAME');
        $dbPassword = getenv('DB_PASSWORD');

        $db->addConnection([
            'driver'    => $dbDriver,
            'host'      => $dbHost,
            'database'  => $dbName,
            'username'  => $dbUsername,
            'password'  => $dbPassword,
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ]);

        $db->setAsGlobal();
    }
}