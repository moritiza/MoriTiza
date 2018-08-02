<?php

namespace Core;

use App\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
	private static $routes = array();
    private static $foundedRoutes = array();
    private static $foundedRoute = array();
    private static $urlParameters = array();
    private static $method = null;
    private static $url = null;

	public function __construct()
	{
		require_once __DIR__ . '/Config/Routes.php';
	}

	public static function __callStatic(string $name, array $args)
    {
        self::$method = strtoupper($name);
        call_user_func_array(array(get_class(), 'routeRegister'), $args);
    }

    private static function routeRegister(string $routeName, $routeControllerAction, array $args = array())
    {
        $routeParts = array_reverse(explode('/', rtrim($routeName, '/')));
        $routePartsCount = count($routeParts);

        $routeParameters = array();
        $routeNameParts = array();

        for ($i = 0; $i < $routePartsCount; $i++) { 
            if (substr($routeParts[$i], 0, 1) === '{' && substr($routeParts[$i], -1, 1) === '}') {
                $tempI = $i;
                $routeParameters[$routeParts[++$i]] = $routeParts[$tempI];
            } else {
                $routeNameParts[] = $routeParts[$i];
            }
        }

        $routeParameters = array_reverse($routeParameters);
        
        if (isset($args['prefix']) && $args['prefix'] !== '') {
            $routeName = rtrim($args['prefix'] . '/' . implode('/', array_reverse($routeNameParts)), '/');
        } else {
            $routeName = implode('/', array_reverse($routeNameParts));
        }

        $routeTempArray = array();
        $routeTempArray['routeName'] = $routeName;

        if (is_string($routeControllerAction)) {
            $routeControllerAction = explode('@', $routeControllerAction);

            $routeTempArray['controller'] = ucfirst($routeControllerAction[0]) . 'Controller';
            $routeTempArray['action'] = $routeControllerAction[1];
        } elseif (is_object($routeControllerAction)) {
            $routeTempArray['controller'] = $routeControllerAction;
            $routeTempArray['action'] = null;
        }

        $routeTempArray['parameters'] = $routeParameters;
        $routeTempArray['method'] = self::$method;

        if (isset($args['auth']) && $args['auth'] === true) {
            $routeTempArray['auth'] = true;
        }

        self::$routes[] = $routeTempArray;
    }

    public function checkRoute(string $url)
    {
        // dump(self::$routes);die();
        self::$url = rtrim($url, '/');
        
        $findRoutes = $this->findRoutes();

        if (count(self::$foundedRoutes) > 0) {
            if ($this->findRoute()) {
                return self::$foundedRoute;
            } else {
                $this->sendPageNotFoundResponse();
            }
        } else {
            $this->sendPageNotFoundResponse();
        }
    }

    private function findRoutes()
    {
        foreach (self::$routes as $route) {
            $pos = strpos(self::$url, $route['routeName'], 0);

            if ($pos !== false && $pos == 0) {
                $request = Request::createFromGlobals();
                
                if ($route['method'] === $request->getMethod()) {
                    self::$foundedRoutes[] = $route;
                }
            }
        }

        return true;
    }

    private function findRoute()
    {
        foreach (self::$foundedRoutes as $route) {
            $urlParametersString = substr(self::$url, strlen($route['routeName']));
            $tempUrlParameters = array();
            
            if ($urlParametersString) {
                $tempUrlParameters = explode('/', ltrim($urlParametersString, '/'));
                $tempUrlParametersCount = count($tempUrlParameters);

                if ($tempUrlParametersCount % 2 == 0) {
                    for ($i = 0; $i < $tempUrlParametersCount; $i++) {
                        self::$urlParameters[$tempUrlParameters[$i]] = $tempUrlParameters[++$i];
                    }

                    if ($this->compareUrlAndRouteParameters($route)) {
                        return true;
                    }
                } else {
                    $this->sendPageNotFoundResponse();
                }

            } else {
                if ($this->compareUrlAndRouteParameters($route)) {
                    return true;
                } else {
                    $this->sendPageNotFoundResponse();
                }
            }
        }
    }

    private function compareUrlAndRouteParameters(array $route)
    {
        $urlParametersCount = count(self::$urlParameters);
        $routeParametersCount = count($route['parameters']);

        if ($urlParametersCount == $routeParametersCount) 
        {
            if (count(array_diff_key(self::$urlParameters, $route['parameters'])) == 0) {
                self::$foundedRoute = $route;

                return true;
            }
        }

        return false;
    }

    private function sendPageNotFoundResponse()
    {
        if (function_exists('http_response_code')) {
            http_response_code(404);
            http_response_code();
            View::render('errors.404');
            die();
        } else {
            header("HTTP/1.0 404 Not Found");
            View::render('errors.404');
            die();
        }
    }

    public function getUrlParameters()
    {
        return self::$urlParameters;
    }
}