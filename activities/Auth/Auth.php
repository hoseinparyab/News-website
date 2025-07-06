<?php

namespace Auth;

class Auth
{
    protected function redirect($url): void
    {
        header('Location :' . trim(CURRENT_DOMAIN, '/') . '/' . trim($url, '/'));
        exit;
    }
     protected  function  redirectBack()
     {
         header('Location' . $_SERVER['HTTP_REFERER']);
         exit();
     }
     private function hash ($password)
     {
         $hashPassword = password_hash($password , PASSWORD_DEFAULT);
     }
     private  function sendMail($emailAddress ,$subject ,$body)
     {
        
     }
}