<?php

namespace Migration;

use DBConnection\Connection;
use Exception;
use InvalidArgumentException;

class Migration
{

    private $migrationsDirectory;

    private $sqlMigrationArray = [];

    private $migratedFileArray = [];

    private $migrationBatch;

    private $connectionHost;
    private $connectionDB;
    private $connectionUser;
    private $connectionPass;

    private $migrationTable;
    private $pdoInstance;




    public function __construct()
    {
        $this->migrationTable = "migrations";
    }

    public function setConnection($host, $dbname, $user, $pass)
    {

        $this->connectionHost = $host;
        $this->connectionDB = $dbname;
        $this->connectionUser = $user;
        $this->connectionPass = $pass;

    }

    private function setOldMigrationArray()
    {


        $result = $this->query("SELECT GROUP_CONCAT() FORM ");

        // id
        // file
        // group


    }

    public function setMigrationDirectory($path)
    {
        $this->migrationsDirectory = rtrim($path, "\/");
    }

    public function getMigrationsDirectory()
    {

        return $this->migrationsDirectory;
    }

    public function getAllMigrations()
    {

        $migrationsDirectory = $this->getMigrationsDirectory();
        $allMigrationsArray = glob($migrationsDirectory . DIRECTORY_SEPARATOR . "*.php");

        return $allMigrationsArray;
    }

    public function runAllMigrations()
    {

        $migrations = $this->getAllMigrations();

        foreach ($migrations as $migration) {

            $this->runSpecificFileMigration($migration);
        }
    }

    private function checkIsMigrationAlreadyExecuted($migration): bool
    {

        $migrationsDirectory = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR;
        $migratedFileArray = $this->getMigratedFileArray();

        $migrationFileName = str_replace($migrationsDirectory, "", $migration);

        if (in_array($migrationFileName, $migratedFileArray)) {
            return true;
        }

        return false;
    }

    public function runSpecificFileMigration($migration)
    {

        // check migration not run previously
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

        // start transaction
        // $pdoInstance->exec("SET AUTOCOMMIT = 0;");

        foreach ($migrationSql as $sql) {

            if (empty($sql))
                continue;

            try {

                $pdoInstance->exec($sql);

            } catch (Exception $e) {

                // $pdoInstance->exec("ROLLBACK;");
                // $pdoInstance->exec("SET AUTOCOMMIT = 1;");
                throw new Exception("There is an error accord on running migration on file {$migration} - sql {$sql} - error ({$e->getMessage()}), This migration not saved!.");
            }
            
        }

        // insert new migration record
        $migrationsDirectory = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR;
        $migrationBatch = $this->getMigrationBatch();
        $migrationFileName = str_replace($migrationsDirectory, "", $migration);

        $result = $pdoInstance->exec("
            INSERT INTO `migrations` SET 
                `migration` = '{$migrationFileName}',
                `batch` = '{$migrationBatch}'
        ");

        // end transaction
        // $pdoInstance->exec("COMMIT;");
        // $pdoInstance->exec("SET AUTOCOMMIT = 1;");
    }

    private function getMigrationBatch()
    {
        return $this->migrationBatch;
    }

    private function clearMigrationSql()
    {
        $this->sqlMigrationArray = [];
    }

    private function pushMigrationSql($sql)
    {
        if (!in_array($sql, $this->sqlMigrationArray)) {
            $this->sqlMigrationArray = array_merge($this->sqlMigrationArray, [$sql]);
        }
    }

    private function getMigrationSql()
    {
        return $this->sqlMigrationArray;
    }

    public function run(...$files)
    {

        $files = array_unique($files);


        // check migration table exists
        if (!$this->checkIsMigrationsTableExists()) {
            $this->createMigrationsTable();
        }

        // set migration batch id for this migration
        $this->setMigrationBatch();


        // set migrated files array
        $this->setMigratedFileArray();


        if (empty($files)) {
            $this->runAllMigrations();
        } else {
            $migrationsDirectory = $this->getMigrationsDirectory() . DIRECTORY_SEPARATOR;

            foreach ($files as $file) {
                $this->runSpecificFileMigration($migrationsDirectory . $file);
            }
        }
    }

    private function setMigratedFileArray()
    {

        $result = $this->query("SELECT GROUP_CONCAT(`migration`) as migrations FROM migrations")->fetch();

        $migratedFilesArray = [];
        if (!empty($result['migrations'])) {
            $migratedFilesArray = explode(",", (string)$result['migrations']);
        }

        $this->migratedFileArray = $migratedFilesArray;
    }

    private function getMigratedFileArray()
    {
        return $this->migratedFileArray;
    }

    private function setMigrationBatch()
    {

        $result = $this->query("SELECT MAX(`batch`) as max_batch FROM migrations")->fetch();

        $batch = (int)$result['max_batch'] + 1;

        $this->migrationBatch = $batch;
    }

    private function checkIsMigrationsTableExists()
    {
        $result = $this->query("
            SELECT * 
            FROM information_schema.tables
            WHERE table_schema = '{$this->connectionDB}'
                AND table_name = '{$this->migrationTable}'
            LIMIT 1;
        ")->fetchAll();

        if ($result)
            return true;

        return false;
    }

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

    public function createMigrationsTable()
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

    public function createNewMigration($name = "migration")
    {
        $migrationsDirectory = $this->getMigrationsDirectory();
        $fileName = date("Y_m_d_His") . "_" . str_replace(' ', '-', (string)$name) . ".php";

        $fullFilePath = $migrationsDirectory . DIRECTORY_SEPARATOR . $fileName;

        $file = fopen($fullFilePath, "w") or die("Unable to open file!");

        $migrationPhpCode = $this->getMigrationFileCode();
        fwrite($file, $migrationPhpCode);

        fclose($file);
    }

    private function getMigrationFileCode()
    {

        return "<?php

    use Migration\MigrationInterface;

    return new class extends MigrationInterface
    {        
        public function handle()
        {
            return [];
        }
    };

?>";
    }
}
