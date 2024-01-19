<?php

use Migration\Migration;

define('c',dirname(__DIR__) . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR);


$migration = new Migration();

$migration->createNewMigration("create_users_table");