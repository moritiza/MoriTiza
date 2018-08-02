<?php

namespace Core;

use Core\Loader;
use App\View;
use App\User;
use App\Model;

class Dispatcher
{
	private $route = array();
	private $urlParameters = array();
	private $specialRoutes = array('login', 'signin', 'register', 'signup', 'password/forgot', 'password/reset');

	public function __construct(string $url)
	{
		$router = Loader::load('Router');
		$this->route = $router->checkRoute($url);
		$this->urlParameters = $router->getUrlParameters();
		$this->setUrlParametersToRequestBag();

		$model = new Model;
		$model();

		$this->routeDispatcher();
	}

	private function setUrlParametersToRequestBag()
	{
		$request = Loader::load('Requests');
		
		foreach ($this->urlParameters as $key => $value) {
			$request->request()->query->set($key, $value);
		}
	}

	private function routeDispatcher()
	{
		if (is_object($this->route['controller'])) {
			$this->operateOnClosureRoute();
		} else {
			$this->operateOnStringRoute();
		}
	}

	private function authenticator()
	{
		$user = new User();

		if ($user->isLoggedIn()) {
			return true;
		}

		return false;
	}

	private function operateOnClosureRoute()
	{
		$executeResult = call_user_func_array($this->route['controller'], array_values($this->urlParameters));
		
		if (in_array($this->route['routeName'], $this->specialRoutes)) {
			$this->operateOnSpecialRoutes($executeResult);
		} else {
			if (isset($this->route['auth']) && $this->route['auth'] === true) {
				if ($this->authenticator()) {
					View::render($executeResult, $this->urlParameters);
				} else {
					$this->sendAccessForbiddenResponse();
				}
			} else {
				View::render($executeResult, $this->urlParameters);
			}
		}
	}

	private function operateOnSpecialRoutes(string $executeResult)
	{
		if ($this->authenticator()) {
			header('Location: /' . explode('/', $_SERVER['REQUEST_URI'])[1], true, 302);
		} else {
			View::render($executeResult, $this->urlParameters);
		}
	}

	private function operateOnStringRoute()
	{
		if (isset($this->route['auth']) && $this->route['auth'] === true) {
			if ($this->authenticator()) {
				$this->createObjectFromControllerAndCallAction();
			} else {
				$this->sendAccessForbiddenResponse();
			}
		} else {
			$this->createObjectFromControllerAndCallAction();
		}
	}

	private function createObjectFromControllerAndCallAction()
	{
		$controllerName = 'App\\Controllers\\' . $this->route['controller'];

		if (class_exists($controllerName)) {
			$ctrl = new $controllerName();

			if (method_exists($ctrl, $this->route['action'])) {
				$action = $this->route['action'];
				$ctrl->$action();
			} else {
				die('Action \'' . $this->route['action'] . '\' Does Not Exists!'); 
			}

		} else {
			die('Controller \'' . $controllerName . '\' Does Not Exists!');
		}
	}

	private function sendAccessForbiddenResponse()
    {
        header("HTTP/1.0 403 Forbidden");
        View::render('errors.403');
        die();
    }

}