<?php

require_once ("vendor/autoload.php");


function dd(...$array) {

    print "<pre>";

    print_r($array);

    exit;

}

use Migration\Migration;

define('DOCUMENT_ROOT',__DIR__ . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR);


$migration = new Migration();

$migration->setMigrationDirectory(DOCUMENT_ROOT);

// $migration->createNewMigration("create_users_table");
// $migration->getAllMigrations();
$migration->runAllMigrations();