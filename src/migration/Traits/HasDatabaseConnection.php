<?php

namespace Migration\Traits;

use InvalidArgumentException;

trait HasDatabaseConnection
{

    /**
     * Database connection parameters.
     *
     * @var string
     */
    private $connectionHost;
    private $connectionDB;
    private $connectionUser;
    private $connectionPass;

    /**
     * PDO instance for database connection.
     *
     * @var \PDO
     */
    private $pdoInstance;

    /**
     * Set the database connection parameters.
     *
     * @param string $host
     * @param string $dbname
     * @param string $user
     * @param string $pass
     */
    public function setConnection($host, $dbname, $user, $pass)
    {
        $this->connectionHost = $host;
        $this->connectionDB = $dbname;
        $this->connectionUser = $user;
        $this->connectionPass = $pass;
    }
}
