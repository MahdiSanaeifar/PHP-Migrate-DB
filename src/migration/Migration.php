<?php

namespace Migration;


class Migration
{

    private $migrationsDirectory;

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

    public function runAllMigrations() {

        $migrations= $this->getAllMigrations();

        foreach($migrations as $migration) {

            $this->runSpecificFileMigration($migration);
        
        }


    }


    public function runSpecificFileMigration($migration)
    {


        $file = require $migration;

        dd($file->handle());


    }


    public function getLastMigration()
    {
    }

    public function createMigrationsTable()
    {
    }

    public function createNewMigration($name = "migration")
    {

        $migrationsDirectory = $this->getMigrationsDirectory();
        $fileName = date("Y_m_d_His") . "_" . str_replace(' ', '-', (string)$name) . ".php";

        $fullFilePath = $migrationsDirectory . DIRECTORY_SEPARATOR . $fileName;


        $file = fopen($fullFilePath, "w") or die("Unable to open file!");

        $txt = "<?php return 123; ?>";
        fwrite($file, $txt);

        fclose($file);

    }
}
