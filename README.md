https://medium.com/@mazraara/create-a-composer-package-and-publish-3683596dec45

# Migration Package

A PHP package for managing database migrations.

## Installation

Install the package using Composer:

composer require mahdisanaeifar/php-migrate-db

## Quick Start
```php
require_once("vendor/autoload.php");

Usage Example
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
$migration->setConnection("localhost", "db", "user", "password");

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

// Version control examples
// 1. Create file
// $migration->newMigration("create_versions_table");
// $migration->newMigration("create_versions2_table");

// 2. Run with no version specified (all pending migrations)
// $migration->run();

// 3. Run to a specific version
// $migration->migrateToVersion("1.0.3");
