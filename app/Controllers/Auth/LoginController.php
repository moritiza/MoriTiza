<?php

namespace App\Controllers\Auth;

use App\Controller;
use Core\Libraries\Validator;
use Core\Libraries\Session;
use Illuminate\Database\Capsule\Manager as DB;

class LoginController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function login()
	{
		$validatedData = Validator::validate([
			'email' => 'required|email',
			'password' => 'required|min:8'
		]);

		if ($validatedData === true) {
			$user = DB::table('users')->where('email', $this->request->get('email'))->get()->first();
			
			if ($user !== null) {
				if (password_verify($this->request->get('password'), $user->password)) {
					
					if ($this->request->get('remember-me') !== null) {
						$rememberToken = $this->rememberTokenGenerator();
						
						DB::table('users')->where('id', $user->id)->update(['remember_token' => $rememberToken]);
						
						setcookie('remember-me', openssl_encrypt($rememberToken, "AES-128-ECB", getenv('APP_KEY'), 0, ""), time() + 604800, null, null, null, true);
					}

					Session::put('login', true);
					Session::put('loggedInUserDetails', [
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'created_at' => $user->created_at,
						'updated_at' => $user->updated_at
					]);
					
					$this->redirect('/');
				} else {
					$this->redirect('signin');
				}
			} else {
				$this->redirect('signin');
			}
		} else {
			$this->redirect('signin');
		}
	}

	private function rememberTokenGenerator()
	{
		return bin2hex(random_bytes(20));
	}
}