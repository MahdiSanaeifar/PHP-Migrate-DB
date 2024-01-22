<?php

namespace Migration;

use DBConnection\Connection;
use Exception;
use InvalidArgumentException;
use Migration\Traits\HasVersionControl;

class Migration
{
    use HasVersionControl;
    
    /**
     * The name of the migrations table in the database.
     *
     * @var string
     */
    private $migrationTable;

    /**
     * The path to the directory containing migration files.
     *
     * @var string
     */
    private $migrationsDirectory;

    /**
     * Array to store SQL queries for a specific migration file.
     *
     * @var array
     */
    private $sqlMigrationArray = [];

    /**
     * Array to store names of migrated files.
     *
     * @var array
     */
    private $migratedFileArray = [];

    /**
     * The migration batch ID.
     *
     * @var int
     */
    private $migrationBatch;

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
     * Constructor for the Migration class.
     */
    public function __construct()
    {
        $this->migrationTable = "migrations";
    }

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

    /**
     * Set the directory path where migration files are located.
     *
     * @param string $path
     */
    public function setMigrationDirectory($path)
    {
        $this->migrationsDirectory = rtrim($path, "\/");
    }

    /**
     * Get the migrations directory path.
     *
     * @return string
     */
    private function getMigrationsDirectory()
    {
        return $this->migrationsDirectory;
    }

    /**
     * Get an array containing all migration file paths in the specified directory.
     *
     * @return array
     */
    private function getAllMigrations()
    {
        $migrationsDirectory = $this->getMigrationsDirectory();
        $allMigrationsArray = glob($migrationsDirectory . DIRECTORY_SEPARATOR . "*.php");

        return $allMigrationsArray;
    }

    /**
     * Run all migrations found in the specified directory.
     */
    private function runAllMigrations()
    {
        $migrations = $this->getAllMigrations();

        foreach ($migrations as $migration) {
            $this->runSpecificFileMigration($migration);
        }
    }

    /**
     * Check if a migration has already been executed based on the migration file name.
     *
     * @param string $migration
     * @return bool
     */
    private function checkIsMigrationAlreadyExecuted($migration): bool
    {
        $migrationsDirectory = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR;
        $migratedFileArray = $this->getMigratedFileArray();

        $migrationFileName = str_replace($migrationsDirectory, "", $migration);

        return in_array($migrationFileName, $migratedFileArray);
    }

    /**
     * Run migration for a specific file.
     *
     * @param string $migration
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function runSpecificFileMigration($migration)
    {
        // Check if migration has already been executed
        if ($this->checkIsMigrationAlreadyExecuted($migration)) {
            return true;
        }

        $this->clearMigrationSql();

        if (!file_exists($migration)) {
            throw new InvalidArgumentException("Migration file not exists on path: {$migration}.");
        }

        $file = require $migration;
        $migrationSql = $file->getQuery();

        if (is_array($migrationSql)) {
            foreach ($migrationSql as $sql) {
                $this->pushMigrationSql($sql);
            }
        } else {
            $this->pushMigrationSql($migrationSql);
        }

        $migrationSql = $this->getMigrationSql();

        $pdoInstance = Connection::getDBConnectionInstance(
            $this->connectionHost,
            $this->connectionDB,
            $this->connectionUser,
            $this->connectionPass
        );

        try {
            // Start a transaction
            $pdoInstance->beginTransaction();

            foreach ($migrationSql as $sql) {
                if (empty($sql)) {
                    continue;
                }

                // Execute the SQL query
                $pdoInstance->exec($sql);
            }

            // Commit the transaction if all queries succeed
            if ($pdoInstance->inTransaction()) {
                $pdoInstance->commit();
            }
        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            if ($pdoInstance->inTransaction()) {
                $pdoInstance->rollBack();
            }

            throw new Exception("There is an error according to running migration on file {$migration} - error ({$e->getMessage()}), This migration not saved!.");
        }

        // Insert new migration record
        $migrationsDirectory = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR;
        $migrationBatch = $this->getMigrationBatch();
        $migrationFileName = str_replace($migrationsDirectory, "", $migration);

        $result = $pdoInstance->exec("
            INSERT INTO `migrations` SET 
                `migration` = '{$migrationFileName}',
                `batch` = '{$migrationBatch}'
        ");
    }


    /**
     * Get the current migration batch ID.
     *
     * @return int
     */
    private function getMigrationBatch()
    {
        return $this->migrationBatch;
    }

    /**
     * Clear the SQL migration array.
     */
    private function clearMigrationSql()
    {
        $this->sqlMigrationArray = [];
    }

    /**
     * Push a SQL migration query into the array.
     *
     * @param string $sql
     */
    private function pushMigrationSql($sql)
    {
        if (!in_array($sql, $this->sqlMigrationArray)) {
            $this->sqlMigrationArray = array_merge($this->sqlMigrationArray, [$sql]);
        }
    }

    /**
     * Get the array containing SQL migration queries.
     *
     * @return array
     */
    private function getMigrationSql()
    {
        return $this->sqlMigrationArray;
    }

    /**
     * Run migrations for specified files or run all migrations if no files are provided.
     *
     * @param string ...$files
     */
    public function run(...$files)
    {
        $files = array_unique($files);

        // Check if the migrations table exists, create it if not
        if (!$this->checkIsMigrationsTableExists()) {
            $this->createMigrationsTable();
        }

        // Set migration batch ID for this migration
        $this->setMigrationBatch();

        // Set migrated files array
        $this->setMigratedFileArray();

        // Run all migrations or specific files
        if (empty($files)) {
            $this->runAllMigrations();
        } else {
            $migrationsDirectory = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR;

            foreach ($files as $file) {
                $this->runSpecificFileMigration($migrationsDirectory . $file);
            }
        }
    }

    /**
     * Set the array of migrated file names.
     */
    private function setMigratedFileArray()
    {
        $result = $this->query("SELECT GROUP_CONCAT(`migration`) as migrations FROM migrations")->fetch();

        $migratedFilesArray = [];
        if (!empty($result['migrations'])) {
            $migratedFilesArray = explode(",", (string)$result['migrations']);
        }

        $this->migratedFileArray = $migratedFilesArray;
    }

    /**
     * Get the array of migrated file names.
     *
     * @return array
     */
    private function getMigratedFileArray()
    {
        return $this->migratedFileArray;
    }

    /**
     * Set the migration batch ID.
     */
    private function setMigrationBatch()
    {
        $result = $this->query("SELECT MAX(`batch`) as max_batch FROM migrations")->fetch();

        $batch = (int)$result['max_batch'] + 1;

        $this->migrationBatch = $batch;
    }

    /**
     * Check if the migrations table exists in the database.
     *
     * @return bool
     */
    private function checkIsMigrationsTableExists()
    {
        $result = $this->query("
            SELECT * 
            FROM information_schema.tables
            WHERE table_schema = '{$this->connectionDB}'
                AND table_name = '{$this->migrationTable}'
            LIMIT 1;
        ")->fetchAll();

        return !empty($result);
    }

    /**
     * Execute a query on the database.
     *
     * @param string $query
     * @return \PDOStatement
     */
    private function query($query)
    {
        $pdoInstance = Connection::getDBConnectionInstance(
            $this->connectionHost,
            $this->connectionDB,
            $this->connectionUser,
            $this->connectionPass
        );

        $statement = $pdoInstance->prepare($query);
        $statement->execute();

        return $statement;
    }

    /**
     * Create the migrations table in the database.
     */
    private function createMigrationsTable()
    {
        $result = $this->query("
            START TRANSACTION;
                CREATE TABLE `migrations` (
                    `id` int UNSIGNED NOT NULL,
                    `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `batch` int NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ALTER TABLE `migrations` ADD PRIMARY KEY (`id`);
                ALTER TABLE `migrations` MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
            COMMIT;
        ");
    }

    /**
     * Create a new migration file with the provided name.
     *
     * @param string $name
     */
    public function newMigration($name = "")
    {
        $migrationsDirectory = $this->getMigrationsDirectory();
        $fileName = date("Y_m_d_His") . "_" . str_replace(' ', '-', (string)$name) . ".php";

        $fullFilePath = $migrationsDirectory . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($fullFilePath, "w") or die("Unable to open file!");

        $migrationPhpCode = $this->getMigrationFileCode();
        fwrite($file, $migrationPhpCode);

        fclose($file);
    }

    /**
     * Get the code template for a new migration file.
     *
     * @return string
     */
    private function getMigrationFileCode()
    {
        $template = <<<'EOT'
            <?php
            
            use Migration\MigrationInterface;
            
            return new class extends MigrationInterface
            {        
                public function handle()
                {
                    return [];
                }
            };
            
            ?>
        EOT;
    
        return $template;
    }
    
}
