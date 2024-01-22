<?php

// Include the autoload file from the vendor directory
require_once("vendor/autoload.php");

// Function for quick debugging purposes
function dd(...$array)
{
    print "<pre>";
    print_r($array);
    exit;
}

use Migration\Migration;

// Define the document root for the migrations directory
define('DOCUMENT_ROOT', __DIR__ . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR);

// Create an instance of the Migration class
$migration = new Migration();

// Set the database connection parameters
$migration->setConnection("localhost", "pakage_test", "root", "mahdi");

// Set the migration directory
$migration->setMigrationDirectory(DOCUMENT_ROOT);

// Uncomment and run to create a new migration file
// $migration->newMigration("create_posts_table");

// Uncomment and run to execute a specific migration file
// $migration->run("2024_01_19_153455_create_posts_table.php");

// Uncomment and run to execute multiple specific migration files
// $migration->run("2024_01_19_153455_create_posts_table.php", "2024_01_22_171511_create_pages_table.php");

// Uncomment and run to create additional migration files
// $migration->newMigration("create_users_table");
// $migration->newMigration("create_categories_table");
// $migration->newMigration("create_menus_table");

// Uncomment and run to execute a specific migration file
// $migration->run("2024_01_22_171511_create_pages_table.php");

// version control
// 1. create file
// $migration->newMigration("create_versions_table");
// $migration->newMigration("create_versions2_table");
// 2. run no version file
// $migration->run("2024_01_22_171511_create_pages_table.php");
// 3. run on version
// $migration->migrateToVersion("1.0.3");
$migration->run();
