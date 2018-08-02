<?php

namespace App\Controllers\Auth;

use App\Controller;
use App\User;
use Core\Libraries\Session;
use Illuminate\Database\Capsule\Manager as DB;

class LogoutController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function logout()
	{
		$user = new User();

		if ($user->IsLoggedIn()) {
			DB::table('users')->where('id', Session::get('loggedInUserDetails')['id'])->update(['remember_token' => null]);
			
			if (isset($_COOKIE['remember-me'])) {
				unset($_COOKIE['remember-me']);
				setcookie('remember-me', null, -1);
			}

			Session::forget('login');
			Session::forget('loggedInUserDetails');
			Session::flush();

			$this->redirect('signin');
		}

		$this->redirect('/');
	}
}
