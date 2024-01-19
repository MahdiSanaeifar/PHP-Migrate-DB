<?php

    use Migration\MigrationInterface;

    return new class extends MigrationInterface
    {        
        public function handle()
        {
            return [

                "CREATE TABLE `menus` (
                                `id` int UNSIGNED NOT NULL,
                                `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `batch` int NOT NULL
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    
                "ALTER TABLE `menus` ADD PRIMARY KEY (`id`);"
    
            ];
        }
    };

?>