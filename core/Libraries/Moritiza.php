<?php

namespace Core\Libraries;

require_once str_replace('\\', '/', dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php'); // Add composer autoloader

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \Core\AppBundle\Command\InitCommand());
$application->add(new \Core\AppBundle\Command\MakeControllerCommand());

$application->run();