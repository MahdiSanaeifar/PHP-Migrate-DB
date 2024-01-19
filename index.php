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

$migration->setConnection("localhost","pakage_test","root","mahdi");
$migration->setMigrationDirectory(DOCUMENT_ROOT);

// $migration->createNewMigration("create_posts_table");
// $migration->getAllMigrations();
// $migration->runAllMigrations();
$migration->run("2024_01_19_153455_create_posts_table.php");
$migration->run("2024_01_19_153455_create_posts_table.php");
$migration->run("2024_01_19_153455_create_posts_table.php");


// $migration->createNewMigration("create_users_table");
$migration->run();

// $migration->createNewMigration("create_categories_table");
// $migration->createNewMigration("create_menus_table");
// $migration->createNewMigration("create_fields_table");
