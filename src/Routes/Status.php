<?php
namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\StatusWebsite\State as S;

class Status implements IRoute{
    public static function register(){
        BasicRoute::add('/status-website/getall',function($matches){
            try{
                App::contenttype('application/json');

                App::result('r',S::getAll());

                App::result('success',true);
            }catch(\Exception $e){
                App::result('msg', $e->getMessage());
            }
        },['post'],true);

    }
}