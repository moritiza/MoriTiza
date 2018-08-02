<?php

namespace App\Controllers\Auth;

use App\Controller;
use Core\Libraries\Validator;
use Illuminate\Database\Capsule\Manager as DB;

class ForgotPasswordController extends Controller
{
    private $emailToken = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function forgotPassword()
    {
        $validatedData = Validator::validate(['email' => 'required|email']);
        
        if ($validatedData === true) {
            $this->checkingIfEmailExistsInDatabase();
            $this->settingEmailAndToken();
            $this->sendVerificationEmail();
        } else {
            $this->redirect('password/forgot');
        }
    }

    private function checkingIfEmailExistsInDatabase()
    {
        $email = DB::table('users')->select('email')->where('email', $this->request->get('email'))->get()->first();

        if ($email !== null) {
            return true;
        }

        $this->redirect('password/forgot');
    }

    private function settingEmailAndToken()
    {
        $email = DB::table('password_resets')->select('email')->where('email', $this->request->get('email'))->get()->first();

        if ($email === null) {
            $date = new \DateTime();
            $date->setTimeZone(new \DateTimeZone(getenv('TIMEZONE')));

            $this->emailToken = $this->emailTokenGenerator();
            
            DB::table('password_resets')->insert([
                'email' => $this->request->get('email'),
                'token' => $this->emailToken,
                'created_at' => $date->setTimestamp(time())
            ]);
        } else {
            $date = new \DateTime();
            $date->setTimeZone(new \DateTimeZone(getenv('TIMEZONE')));

            $this->emailToken = $this->emailTokenGenerator();

            DB::table('password_resets')
                ->where('email', $this->request->get('email'))
                ->update(['token' => $this->emailToken, 'created_at' => $date->setTimestamp(time())])
            ;
        }

        return true;
    }

    private function emailTokenGenerator()
    {
        return bin2hex(random_bytes(20));
    }

    private function sendVerificationEmail()
    {
        $transport = (new \Swift_SmtpTransport(getenv('MAIL_HOST'), getenv('MAIL_PORT')))
            ->setUsername(getenv('MAIL_USERNAME'))
            ->setPassword(getenv('MAIL_PASSWORD'))
        ;

        $mailer = new \Swift_Mailer($transport);

        $name = DB::table('users')->select('name')->where('email', $this->request->get('email'))->get()->first();
        $name = explode(' ', $name->name);
        $name = $name[0];

        $message = (new \Swift_Message('MoriTiza Password Reset'))
            ->setFrom(['ardabilsc@gmail.com' => 'MoriTiza PHP Micro Framework'])
            ->setTo([$this->request->get('email')])
            ->setBody(
                '<html>' .
                    '<head>' .
                        '<meta charset="utf-8">' .
                        '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' .
                        '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">' .
                    '</head>' .
                    '<body class="bg-light">' .
                        '<div class="container">' .
                            '<div class="py-5 text-justify">' .
                                '<h2>Hi ' . $name . ',' . '</h2>' .
                                '<p class="lead">We\'ve received a request to reset your password. If you didn\'t make the request, just ignore this email. Otherwise, you can reset your password using this link:</p>' .
                                '<a href="http://localhost/MoriTiza/password/reset?email=' . $this->request->get('email') . '&token=' . $this->emailToken . '" class="btn btn-lg btn-success btn-block">Click here to reset your password</a>' .
                                '<br>' .
                                '<p class="lead">Thanks,<br>MoriTiza PHP Micro Framework</p>' .
                            '</div>' .
                        '</div>' .
                    '</body>' .
                '</html>'
                , 'text/html'
            )
        ;

        $result = $mailer->send($message);

        $this->redirect('password/forgot');
    }
}
