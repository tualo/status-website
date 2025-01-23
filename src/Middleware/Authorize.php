<?php
namespace Tualo\Office\StatusWebsite\Middleware;
use Tualo\Office\Basic\TualoApplication;
use Tualo\Office\Basic\IMiddleware;
class Authorize implements IMiddleware{
    public static function register(){
        TualoApplication::use('StatusWebsite_SW_Auth',function(){
            try{
                $session = TualoApplication::get('session');
                if ($session->getHeader('SW-Authorization') !== false) {
                    $authToken = $session->getHeader('SW-Authorization');
                    if (($key = TualoApplication::configuration('oauth', 'key')) !== false) {
                        $data = base64_decode($authToken);
                        $authToken = \Tualo\Office\TualoPGP\TualoApplicationPGP::decrypt(file_get_contents($key), $data);

                        $sql = 'select * from status_website_user_token where fingerprint = {fingerprint}';
                        $rows = $session->getDB()->direct($sql, ['fingerprint' => md5($authToken)]);
                        foreach ($rows as $row) {
                            if($authToken == $row['token']){
                                $_SESSION['statuswebsite'] = $session->getDB()->direct('select * from status_website_user where id = {status_website_user_id}', $row);
                            }

                        }
                    }
                }
            }catch(\Exception $e){
                TualoApplication::set('maintanceMode','on');
                TualoApplication::addError($e->getMessage());
            }
        },-9999999777,[],false);
    }
}