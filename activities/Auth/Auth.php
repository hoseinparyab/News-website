<?php

namespace Auth;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Auth
{
    protected function redirect($url): void
    {
        header('Location :' . trim(CURRENT_DOMAIN, '/') . '/' . trim($url, '/'));
        exit;
    }

    protected function redirectBack()
    {
        header('Location' . $_SERVER['HTTP_REFERER']);
        exit();
    }

    private function hash($password)
    {
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    }
    private function random()
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    public function activationMessage($username, $verifyToken)
    {
        $message = '
        <h1>فعال سازی حساب کاربری</h1>
        <p>'. $username .' عزیز برای فعال سازی حساب کاربری خود لطفا روی لینک زیر کلیک نمایید</p>
        <di><a href="">فعال سازی حساب</a></di>
        ';
        return $message;
    }

    private function sendMail($emailAddress, $subject, $body)
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->CharSet = "UTF-8";                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = Mail_HOST;                     //Set the SMTP server to send through
            $mail->SMTPAuth = SMTP_AUTH;                                   //Enable SMTP authentication
            $mail->Username = MAIL_USERNAME;                     //SMTP username
            $mail->Password = MAIL_PASSWORD;                               //SMTP password
            $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
            $mail->Port = MAIL_PORT;                                    //TCP port to connect to; use 587 if you have set
            // `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom(SENDER_MAIL, SENDER_NAME);
            $mail->addAddress($emailAddress);               //Name is optional


            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $body;
            $result = $mail->send();
            echo 'Message has been sent';
            return $result;
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
        if(empty($request['email']) || empty($request['username']) || empty($request['password']))
        {
            $this->redirectBack();
        }
        else if(strlen($request['password']) < 8)
        {
            $this->redirectBack();
        }
        else if(!filter_var($request['email'], FILTER_VALIDATE_EMAIL))
        {
            $this->redirectBack();
        }
        else{
            $db = new DataBase();
            $user = $db->select('SELECT * FROM users WHERE email = ?', $request['email'])->fetch();
            if($user != null){
                $this->redirectBack();
            }
            else{
                $randomToken = $this->random();
                $activationMessage = $this->activationMessage($request['username'], $randomToken);
                $result = $this->sendMail($request['email'], 'فعال سازی حساب کاربری', $activationMessage);
                if($result)
                {
                    $request['verify_token'] = $randomToken;
                    $request['password'] = $this->hash($request['password']);
                    $db->insert('users', array_keys($request), $request);
                    $this->redirect('login');
                }
                else{
                    $this->redirectBack();
                }

            }
        }
    }
}