<?php

namespace Core\AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MakeControllerCommand extends Command
{
    private $controllerName = null;
    private $controllersPath = null;

    protected function configure()
    {
        $this
            ->setName('make:controller')

            ->setDescription('Make controller')

            ->setHelp('This command make controller...')

            ->addArgument('ControllerName', InputArgument::REQUIRED, 'What is controller name?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->controllerName = $input->getArgument('ControllerName');
        $this->controllersPath = dirname(dirname(dirname(__DIR__))) . '/app/Controllers/';
        
        if (preg_match('/^[A-Z][a-zA-Z\/]*Controller$/', $this->controllerName)) {
            $this->checkControllerExists($output);
        } else {
            $output->writeln("");
            $output->writeln("\e[1;41m                                                                                 \e[0m");
            $output->writeln("\e[1;41m   The controller name is not correct.                                           \e[0m");
            $output->writeln("\e[1;41m   Controller name must like: \"SampleController\" or \"Path/To/SampleController\"   \e[0m");
            $output->writeln("\e[1;41m                                                                                 \e[0m");
            $output->writeln("");
        }
    }

    private function checkControllerExists($output)
    {
        if (file_exists($this->controllersPath . $this->controllerName . '.php')) {
            $output->writeln("");
            $output->writeln("\e[1;41m                                                        \e[0m");
            $output->writeln("\e[1;41m   The controller \"{$this->controllerName}\" is already exists.   \e[0m");
            $output->writeln("\e[1;41m                                                        \e[0m");
            $output->writeln("");
        } else {
            $this->createController($output);
        }
    }

    private function createController($output)
    {   
        $handle = fopen($this->controllersPath . $this->controllerName . '.php', 'w');

        $namespace = 'App\Controllers';

        $controllerNamePartsArray = explode('/', $this->controllerName);

        if (count($controllerNamePartsArray) == 1) {
            $namespace = 'App\Controllers';
        } else {
            for ($i=0; $i < count($controllerNamePartsArray)-1; $i++) { 
                $namespace .= "\\{$controllerNamePartsArray[$i]}";
            }
        }

        $controllerName = explode('/', $this->controllerName);
        $controllerName = end($controllerName);

        fwrite($handle, "<?php\n");
        fwrite($handle, "\n");
        fwrite($handle, "namespace {$namespace};\n");
        fwrite($handle, "\n");
        fwrite($handle, "use App\Controller;\n");
        fwrite($handle, "\n");
        fwrite($handle, "class {$controllerName} extends Controller\n");
        fwrite($handle, "{\n");
        fwrite($handle, "\tpublic function __construct()\n");
        fwrite($handle, "\t{\n");
        fwrite($handle, "\t\tparent::__construct();\n");
        fwrite($handle, "\t}\n");
        fwrite($handle, "}\n");

        fclose($handle);

        $output->writeln("");
        $output->writeln("\e[1;32m   Controller created successfully.   \e[0m");
        $output->writeln("");
    }
}