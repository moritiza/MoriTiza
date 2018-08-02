<?php

namespace App;

use Core\Libraries\Session;
use Illuminate\Database\Capsule\Manager as DB;

class User
{
	public function isLoggedIn()
	{
		if (isset($_COOKIE['remember-me'])) {
			return $this->authenticateWithCookie();
		} else {
			return $this->authenticateWithSession();
		}

		return false;
	}

	private function authenticateWithCookie()
	{
		$user = DB::table('users')->where('remember_token', openssl_decrypt($_COOKIE['remember-me'], "AES-128-ECB", getenv('APP_KEY'), 0, ""))->get()->first();

		if ($user !== null) {
			Session::put('login', true);
			Session::put('loggedInUserDetails', [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'created_at' => $user->created_at,
				'updated_at' => $user->updated_at
			]);

			return true;
		}

		if (Session::exists('login') && Session::exists('loggedInUserDetails')) {
			Session::forget('login');
			Session::forget('loggedInUserDetails');
			Session::flush();
		}

		unset($_COOKIE['remember-me']);
		setcookie('remember-me', null, -1);

		return false;
	}

	private function authenticateWithSession()
	{
		if (Session::has('login') && Session::get('login') === true && Session::has('loggedInUserDetails')) {
			$userRememberToken = DB::table('users')->select('remember_token')->where('id', Session::get('loggedInUserDetails')['id'])->get()->first();
			
			if ($userRememberToken->remember_token === null) {
				return true;
			}

			DB::table('users')->where('id', Session::get('loggedInUserDetails')['id'])->update(['remember_token' => null]);

			Session::forget('login');
			Session::forget('loggedInUserDetails');
			Session::flush();
		}

		return false;
	}
}