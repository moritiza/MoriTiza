<?php

use App\User;

final class Twig_Extension_Auth extends Twig_Extension
{
	public function getFunctions()
	{
		return array(
            new Twig_Function('auth', array($this, 'authenticator'))
        );
	}

	public function authenticator()
	{
        $user = new User();

		if ($user->IsLoggedIn()) {
			return true;
		}

		return false;
	}
}