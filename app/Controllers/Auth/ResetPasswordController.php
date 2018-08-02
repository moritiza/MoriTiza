<?php

namespace App\Controllers\Auth;

use App\Controller;
use App\View;
use Core\Libraries\Validator;
use Illuminate\Database\Capsule\Manager as DB;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function reset()
	{
		$validatedData = Validator::validate([
			'email' => 'required|email',
			'token' => 'required|alphanum'
		]);

		if ($validatedData === true) {
			$data = DB::table('password_resets')->where(['email' => $this->request->get('email'), 'token' => $this->request->get('token')])->get()->first();
			
			if ($data !== null) {
				if ($this->checkingTokenExpirationTime($data->created_at)) {
					$email = $this->request->get('email');
					$token = $this->request->get('token');

					View::render('auth.password-reset', compact('email', 'token'));
				} else {
					$this->redirect('password/forgot');
				}
			} else {
				$this->redirect('password/forgot');
			}
		} else {
			$this->redirect('password/forgot');
		}
	}

	private function checkingTokenExpirationTime($time)
	{
		$now = Carbon::now(getenv('TIMEZONE'));
		$tokenExpirationTime = Carbon::createFromFormat('Y-m-d H:i:s', $time, getenv('TIMEZONE'))->addHours(24);

		if ($now->gt($tokenExpirationTime)) {
			return false;
		}

		return true;
	}

	public function changePassword()
	{
		$validatedData = Validator::validate([
			'email' => 'required|email',
			'token' => 'required|alphanum',
			'password' => 'required|min:8'
		]);

		if ($validatedData === true) {
			$data = DB::table('password_resets')->where(['email' => $this->request->get('email'), 'token' => $this->request->get('token')])->get()->first();

			if ($data !== null) {
				if ($this->checkingTokenExpirationTime($data->created_at)) {
					$passwordOptions = ['cost' => 12];
					$password = password_hash($this->request->get('password'), PASSWORD_DEFAULT, $passwordOptions);

					DB::table('users')
						->where('email', $this->request->get('email'))
						->update(['password' => $password])
					;

					DB::table('password_resets')->where('email', $this->request->get('email'))->delete();

					$this->redirect('signin');
				} else {
					$this->redirect('password/forgot');
				}
			} else {
				$this->redirect('password/forgot');
			}
		} else {
			$this->redirect('password/reset?email=' . $this->request->get('email') . '&token=' . $this->request->get('token'));
		}
	}
}