<?php

namespace App;

class Controller
{
    protected $request = array();

    public function __construct()
    {
        if (empty($this->request)) {
            $request = \Core\Loader::load('Requests');

            $this->request = $request->request();
        }
    }

    protected function redirect($route, $permanent = false)
    {
        if ($route === '/') {
            header('Location: /' . explode('/', $_SERVER['REQUEST_URI'])[1], true, ($permanent ? 301 : 302));
            die();
        } else {
            header('Location: /' . explode('/', $_SERVER['REQUEST_URI'])[1] . '/' . $route, true, ($permanent ? 301 : 302));
            die();
        }
    }
}