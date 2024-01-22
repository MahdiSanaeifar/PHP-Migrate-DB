    <?php
    
    use Migration\MigrationInterface;
    
    return new class extends MigrationInterface
    {        

        protected $version = "1.0.3";

        public function handle()
        {
            return [
                "
                INSERT INTO `pages` (`id`, `migration`, `batch`) VALUES ('5', '55', '66');
                "
            ];
        }
    };
    
    ?>