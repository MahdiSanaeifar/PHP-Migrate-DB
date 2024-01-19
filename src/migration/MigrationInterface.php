<?php

namespace Migration;

abstract class MigrationInterface
{
    protected $database;

    public $hasTransaction = true;

    public function getDatabase()
    {
        return $this->database;
    }

    public function getQuery() {

        return $this->handle();
    }

    public abstract function handle();
}
