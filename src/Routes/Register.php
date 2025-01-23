<?php

namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as TApp;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\Mail\SMTP;
use Tualo\Office\PUG\PUG;
use Tualo\Office\StatusWebsite\State as S;

class Register implements IRoute
{
    public static function register()
    {

        BasicRoute::add('/status-website-app/register', function ($matches) {
            $db = TApp::get('session')->getDB();
            TApp::contenttype('application/json');
            try {
                
                $payload = $_POST;// json_decode(@file_get_contents('php://input'), true);
                $fromMail = TApp::configuration('status-website', 'mail.from', '---');
                if (!isset($payload['sw_email'])) {
                    throw new \Exception('email is missing');
                }
                if (!isset($payload['sw_username'])) {
                    throw new \Exception('username is missing');
                }
                $userTable = DSTable::instance('status_website_user');

                $user = $userTable->f('username', 'eq', $payload['sw_username'])->read()->getSingle();
                if (count($user)!==0) {
                    // ggf test ob es eine erneute registrierung ist
                    throw new \Exception('username already exists');
                }
                if ($payload['sw_password'] !== $payload['sw_password2']) {
                    throw new \Exception('passwords do not match');
                }
                $user = $userTable->insert([
                    'username' => $payload['sw_username'],
                    'password' => password_hash($payload['sw_password'], PASSWORD_BCRYPT),
                    'email' => $payload['sw_email'],
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'pin' => rand(100000, 999999)
                ]);
                if ($userTable->error()) {
                    throw new \Exception($userTable->errorMessage());
                }

                $user = $userTable->f('username', 'eq', $payload['sw_username'])->read()->getSingle();
                if (count($user)==0) {
                    throw new \Exception('not able to create user');
                }
                $mail = SMTP::get();
                $mail->setFrom($fromMail);
                $mail->addAddress($user['email']);
                $mail->Subject = "Ihre Authentifikation-PIN";
                $mail->isHTML(true);
                $mail->Body    = PUG::render('status_website_mail_pin', ['data' => $user]);
                if (!$mail->send()) {
                    throw new \Exception('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
                }
                TApp::result('success', true);
            } catch (\Exception $e) {
                TApp::result('msg', $e->getMessage());
            }
        }, ['post'], true);
    }
}