<?php


namespace DBConnection;

use PDO;
use PDOException;

class Connection
{

    private static $dbConnectionInstance = null;

    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    private function __construct()
    {
    }

    public static function getDBConnectionInstance()
    {

        if (self::$dbConnectionInstance == null) {
            $DBConnectionInstance = new Connection();
            self::$dbConnectionInstance = $DBConnectionInstance->dbConnection();
        }

        return self::$dbConnectionInstance;
    }

    private function dbConnection()
    {
        $options = $this->options ?? [];

        $host = "localhost";
        $dbname = "ma_example";
        $user = "root";
        $pass = "mahdi";

        try {
            return new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $user, $pass, $options);
        } catch (PDOException $e) {
            echo "Error in database connection: " . $e->getMessage();
            return false;
        }
    }
}
