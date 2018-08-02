<?php

namespace App;

class View
{
	public static function render(string $viewName, array $params = array())
    {
        $viewName = str_replace('.', '/', $viewName);
        $viewFile = $viewName . '.twig';

        if (file_exists(VIEWPATH . $viewFile) && is_readable(VIEWPATH . $viewFile)) {
            $loader = new \Twig_Loader_Filesystem(VIEWPATH);
            $twig = new \Twig_Environment($loader, array('debug' => true));
            $twig->addExtension(new \Twig_Extension_Debug());
            $twig->addExtension(new \Twig_Extension_Asset());
            $twig->addExtension(new \Twig_Extension_Auth());

            echo $twig->render($viewFile, $params);
        }
    }
}