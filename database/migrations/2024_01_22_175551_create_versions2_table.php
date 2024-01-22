    <?php
    
    use Migration\MigrationInterface;
    
    return new class extends MigrationInterface
    {        

        protected $version = "1.0.2";

        public function handle()
        {
            return [
                "
                INSERT INTO `pages` (`id`, `migration`, `batch`) VALUES ('4', '55', '66');
                "
            ];
        }
    };
    
    ?>