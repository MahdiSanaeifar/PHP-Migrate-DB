<?php

namespace Migration\Traits;

use InvalidArgumentException;

trait HasVersionControl
{
    /**
     * Migrate to a specific version.
     *
     * @param int $version The target version to migrate to.
     *
     * @throws InvalidArgumentException
     */
    public function migrateToVersion(string $version)
    {
        $this->setMigrationBatch();
        $this->setMigratedFileArray();
        $migrations = $this->getAllMigrations();

        foreach ($migrations as $migration) {
            // Check if migration has already been executed
            if ($this->checkIsMigrationAlreadyExecuted($migration)) {
                continue;
            }

            // Get the version of the migration file
            $migrationVersion = $this->getMigrationFileVersion($migration);

            // Skip files with versions higher than the target version
            if ($migrationVersion > $version) {
                continue;
            }

            // Run the migration
            $this->runSpecificFileMigration($migration);
        }
    }

    /**
     * Get the version of a migration file.
     *
     * @param string $migration The path to the migration file.
     *
     * @return int The version of the migration file.
     *
     * @throws InvalidArgumentException
     */
    private function getMigrationFileVersion($migration)
    {
        // Check if the migration file exists
        if (!file_exists($migration)) {
            throw new InvalidArgumentException("Migration file not exists on path: {$migration}.");
        }

        // Get the migration file and retrieve its version
        $file = require $migration;
        $migrationVersion = $file->getVersion();

        return $migrationVersion;
    }
}
