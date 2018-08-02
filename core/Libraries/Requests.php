<?php

namespace Core\Libraries;

use Symfony\Component\HttpFoundation\Request;

class Requests
{
    private $request = array();

    public function request()
    {
        if (empty($this->request)) {
            $this->setRequest();
        }

        return $this->request;
    }

    private function setRequest()
    {
        $this->request = Request::createFromGlobals();
    }
}