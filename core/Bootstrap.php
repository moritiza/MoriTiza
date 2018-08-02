<?php

namespace Core;

use Core\Dispatcher;
use Core\Libraries\SessionHandler;
use Core\Libraries\Session;

class Bootstrap
{
	public static function run(string $url)
	{
		require_once __DIR__ . '/Config/config.php';
        require_once __DIR__ . '/Helpers/functions.php';
        
        $dotenv = new \Dotenv\Dotenv(dirname(__DIR__));
        $dotenv->load();

        if (getenv('APP_DEBUG') === 'true') {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        } elseif (getenv('APP_DEBUG') === 'false') {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }

		ini_set('session.save_handler', 'files');

        session_save_path(__DIR__ . '/storage/sessions');
        $sessionHandler = new SessionHandler();
        session_set_save_handler($sessionHandler, true);

        if (session_id() === '') {
            session_start();
        } else {
            Session::regenerate();
        }
        
        $dispatch = new Dispatcher($url);
	}
}