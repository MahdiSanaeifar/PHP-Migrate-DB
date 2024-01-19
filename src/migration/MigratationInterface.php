<?php

namespace Migration;

abstract class MigratationInterface
{
    protected $database;

    public $hasTransaction = true;


    public function getDatabase()
    {
        return $this->database;
    }
}
