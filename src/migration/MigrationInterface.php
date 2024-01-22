<?php

namespace Migration;

abstract class MigrationInterface
{
    protected $database;
    protected $version = "1.0.0";

    /**
     * Get migration target database name
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get migration version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get migration full query for execute
     */
    public function getQuery()
    {

        return $this->handle();
    }

    public abstract function handle();
}
