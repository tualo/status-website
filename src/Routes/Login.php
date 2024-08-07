<?php

namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as TApp;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\Mail\SMTP;
use Tualo\Office\PUG\PUG;
use Tualo\Office\StatusWebsite\State as S;
use Tualo\Office\CMS\CMSMiddleware\Session;

class Login implements IRoute
{
    public static function register()
    {
        BasicRoute::add('/status-website-app/info', function ($matches) {
            $db = TApp::get('session')->getDB();
            $session = new Session();
            TApp::contenttype('application/json');
            TApp::result('success', false);
            try {
                $payload = json_decode(@file_get_contents('php://input'), true);
                if (!isset($payload['username'])) {
                    throw new \Exception('username is missing');
                }
                $user = DSTable::instance('status_website_user')
                    ->f('username', 'eq', $payload['username'])
                    ->read()
                    ->getSingle();
                if ($user === false) {
                    throw new \Exception('username not found');
                }
                if ($session->get('status_website_user',false)) {
                    // logged in user append user data
                }
                TApp::result('success', true);
            } catch (\Exception $e) {
               //  TApp::result('msg', $e->getMessage());
            }
        }, ['post'], true);

        BasicRoute::add('/status-website-app/login', function ($matches) {
            $db = TApp::get('session')->getDB();
            TApp::contenttype('application/json');
            try {
                $payload = json_decode(@file_get_contents('php://input'), true);
                if (!isset($payload['username'])) {
                    throw new \Exception('username is missing');
                }
                if (!isset($payload['password'])) {
                    throw new \Exception('password is missing');
                }

                /*
                if (password_hash($payload['password'], PASSWORD_BCRYPT ) !== $user['password']) {
                    throw new \Exception('password is wrong');
                }
                */
                $user = DSTable::instance('status_website_user')
                    ->f('username', 'eq', $payload['username'])
                    ->read()
                    ->getSingle();

                if ($user === false) {
                    throw new \Exception('login incorrect');
                }

                if (!password_verify($payload['password'],$user['password'])){
                    throw new \Exception('login incorrect');

                }
                TApp::result('success', true);
            } catch (\Exception $e) {
                TApp::result('msg', $e->getMessage());
            }
        }, ['post'], true);
    }
}
