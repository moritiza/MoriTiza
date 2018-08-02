<?php

final class Twig_Extension_Asset extends Twig_Extension
{
	public function getFunctions()
	{
		return array(
            new Twig_Function('asset', array($this, 'getPublicPath'))
        );
	}

	public function getPublicPath(string $path)
	{
		$publicPath = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . rtrim($_SERVER['SCRIPT_NAME'], 'index.php') . 'public/';
		// $publicPath = $_SERVER['REQUEST_URI'] . '/public/';
		$assetPath = $publicPath . $path;
		
		return $assetPath;
	}
}