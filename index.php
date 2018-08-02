<?php

use Symfony\Component\HttpFoundation\Request;
use Core\Bootstrap;

require_once __DIR__ . '/vendor/autoload.php';

$request = Request::createFromGlobals();

$url = $request->get('url');
$url = $url !== null ? $url : 'index';

Bootstrap::run($url);