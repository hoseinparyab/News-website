<?php

namespace Auth;

use database\DataBase;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Auth
{

    protected function redirect($url)
    {
        header('Location: ' . trim(CURRENT_DOMAIN, '/ ') . '/' . trim($url, '/ '));
        exit;
    }

    protected function redirectBack()
    {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    private function hash($password)
    {
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        return $hashPassword;
    }

    private function random()
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    public function activationMessage($username, $verifyToken)
    {
        $message = '
        <h1>فعال سازی حساب کاربری</h1>
        <p>' . $username . ' عزیز برای فعال سازی حساب کاربری خود لطفا روی لینک زیر کلیک نمایید</p>
        <di><a href="' . url('activation/' . $verifyToken) . '">فعال سازی حساب</a></di>
        ';
        return $message;
    }

    private function sendMail($emailAddress, $subject, $body)
    {

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output
            $mail->CharSet = "UTF-8"; //Enable verbose debug output
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = MAIL_HOST; //Set the SMTP server to send through
            $mail->SMTPAuth = SMTP_AUTH; //Enable SMTP authentication
            $mail->Username = MAIL_USERNAME; //SMTP username
            $mail->Password = MAIL_PASSWORD; //SMTP password
            $mail->SMTPSecure = 'tls'; //Enable implicit TLS encryption
            $mail->Port = MAIL_PORT; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(SENDER_MAIL, SENDER_NAME);
            $mail->addAddress($emailAddress);


            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }

    }


    public function register()
    {
        require_once(BASE_PATH . '/template/auth/register.php');
    }


    public function registerStore($request)
    {
        if (empty($request['email']) || empty($request['username']) || empty($request['password'])) {
            flash('register_error', 'تمامی فیلد ها اجباری میباشند');
            $this->redirectBack();
        } else if (strlen($request['password']) < 8) {
            flash('register_error', 'رمز عبور باید حداقل ۸ کاراکتر باشد');
            $this->redirectBack();
        } else if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            flash('register_error', 'ایمیل معتبری وارد نشده است');
            $this->redirectBack();
        } else {
            $db = new DataBase();
            $user = $db->select('SELECT * FROM users WHERE email = ?', [$request['email']])->fetch();
            if ($user != null) {
                flash('register_error', 'کاربر از قبل در سیستم وجود دارد عزیزم');
                $this->redirectBack();
            } else {
                $randomToken = $this->random();
                $activationMessage = $this->activationMessage($request['username'], $randomToken);
                $result = $this->sendMail($request['email'], 'فعال سازی حساب کاربری', $activationMessage);
                if ($result) {
                    $request['verify_token'] = $randomToken;
                    $request['password'] = $this->hash($request['password']);
                    $db->insert('users', array_keys($request), $request);
                    $this->redirect('login');
                } else {
                    flash('register_error', 'ارسال ایمیل با خطا مواجه شد');
                    $this->redirectBack();
                }

            }
        }
    }

    public function activation($verifyToken)
    {
        $db = new Database();
        $user = $db->select("SELECT * FROM users WHERE verify_token = ? AND is_active = 0;", [$verifyToken])->fetch();
        if ($user == null) {
            $this->redirect('login');
        } else {
            $result = $db->update('users', $user['id'], ['is_active'], [1]);
            $this->redirect('login');
        }
    }

    public function login()
    {
        require_once(BASE_PATH . '/template/auth/login.php');
    }


    public function checkLogin($request)
    {
        if(empty($request['email']) || empty($request['password']))
        {
            flash('login_error', 'تمامی فیلد ها الزامی میباشند');
            $this->redirectBack();
        }
        else{
            $db = new DataBase();
            $user = $db->select("SELECT * FROm users WHERE email = ?", [$request['email']])->fetch();

            if($user != null)
            {
                if(password_verify($request['password'], $user['password']) && $user['is_active'] == 1)
                {
                    $_SESSION['user'] = $user['id'];
                    $this->redirect('admin');
                }
                else{
                    flash('login_error', 'ورود انجام نشد');
                    $this->redirectBack();
                }
            }
            else{
                flash('login_error', 'کاربری با این مشخصات یافت نشد');
                $this->redirectBack();
            }
        }
    }

}
