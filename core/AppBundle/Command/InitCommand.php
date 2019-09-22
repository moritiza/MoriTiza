<?php

namespace Core\AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class InitCommand extends Command
{
    private $dbh = NULL;

    protected function configure()
    {
        $this
            ->setName('init')

            ->setDescription('Create initial tables')

            ->setHelp('This command just run at first time...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dotenv = \Dotenv\Dotenv::create(dirname(dirname(dirname(__DIR__))));
        $dotenv->load();

        $dbName = getenv('DB_DATABASE');
        $dbUsername = getenv('DB_USERNAME');
        $dbPassword = getenv('DB_PASSWORD');
        
        $dsn = "mysql:host=localhost;dbname=$dbName";

		$options = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
            );
            
        try {
            $this->dbh = new \PDO($dsn, $dbUsername, $dbPassword, $options);
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dbh->exec("SET character_set_results=utf8;");
            $this->dbh->exec("SET character_set_client=utf8;");
            $this->dbh->exec("SET character_set_connection=utf8;");
            $this->dbh->exec("SET character_set_database=utf8;");
            $this->dbh->exec("SET character_set_server=utf8;");

            $this->createUsersTable($output);
            $this->createPasswordResetsTable($output);

            $output->writeln("");
            $output->writeln("\e[1;32m   The initial tables created successfully.   \e[0m");
            $output->writeln("");

            $this->disconnect();

            $this->appKeyGenerator();
        } catch(\PDOException $e) {
            $output->writeln("");
            $output->writeln("\e[1;41m                                            \e[0m");
            $output->writeln("\e[1;41m   Whoops! There is a problem.              \e[0m");
            $output->writeln("\e[1;41m   Please, check .env file and try again.   \e[0m");
            $output->writeln("\e[1;41m                                            \e[0m");
            $output->writeln("");
        }
    }

    private function createUsersTable($output)
    {
        $createUsersTableQuery = "CREATE TABLE `users` (
            `id` INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `remember_token` VARCHAR(255),
            `created_at` TIMESTAMP NULL,
            `updated_at` TIMESTAMP NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci";

        $stmt = $this->dbh->prepare($createUsersTableQuery);
        $stmt->execute();
    }

    private function createPasswordResetsTable($output)
    {
        $createUsersTableQuery = "CREATE TABLE `password_resets` (
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `token` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP NULL
        ) ENGINE=InnoDB AUTO_INCREMENT=1 CHARACTER SET utf8 COLLATE utf8_general_ci";

        $stmt = $this->dbh->prepare($createUsersTableQuery);
        $stmt->execute();
    }

    private function disconnect()
	{
		$this->dbh = NULL;
    }
    
    private function appKeyGenerator()
    {
        $envFilePath = str_replace('\\', '/', dirname(__FILE__, 4)) . '/';
        $envFile = fopen($envFilePath . '.env', 'a+');

        while (! feof($envFile)) {
            $line = fgets($envFile);

            if (substr($line, 0, 8) === 'APP_KEY=') {
                $contents = file_get_contents($envFilePath . '.env');
                $contents = str_replace($line, "APP_KEY=" . base64_encode(bin2hex(random_bytes(20))) . "\n", $contents);
                file_put_contents($envFilePath . '.env', $contents);
            }
        }

        fclose($envFile);
    }
}
