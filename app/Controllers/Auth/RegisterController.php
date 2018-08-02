<?php

namespace App\Controllers\Auth;

use App\Controller;
use Core\Libraries\Validator;
use Core\Libraries\Session;
use Illuminate\Database\Capsule\Manager as DB;

class RegisterController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function register()
	{
		$validatedData = Validator::validate([
			'name' => 'required|alpha',
			'email' => 'required|email',
			'password' => 'required|min:8'
		]);

		if ($validatedData === true) {
			$user = DB::table('users')->where('email', $this->request->get('email'))->get()->first();
			
			if ($user === null) {
				$passwordOptions = ['cost' => 12];
				$password = password_hash($this->request->get('password'), PASSWORD_DEFAULT, $passwordOptions);

				$date = new \DateTime();
				$date->setTimeZone(new \DateTimeZone(getenv('TIMEZONE')));

				DB::table('users')->insert([
					'name' => $this->request->get('name'),
					'email' => $this->request->get('email'),
					'password' => $password,
					'created_at' => $date->setTimestamp(time())
				]);

				$loggedInUserDetails = DB::table('users')->select('id', 'name', 'email', 'remember_token', 'created_at', 'updated_at')->where('email', $this->request->get('email'))->get()->first();

				Session::put('login', true);
				Session::put('loggedInUserDetails', [
					'id' => $loggedInUserDetails->id,
					'name' => $loggedInUserDetails->name,
					'email' => $loggedInUserDetails->email,
					'created_at' => $loggedInUserDetails->created_at,
					'updated_at' => $loggedInUserDetails->updated_at
				]);

				$this->redirect('/');
			} else {
				$this->redirect('signup');
			}
		} else {
			$this->redirect('signup');
		}
	}
}
