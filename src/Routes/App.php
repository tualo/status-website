<?php
namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as TApp;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\StatusWebsite\State as S;

class App implements IRoute{
    public static function register(){
        BasicRoute::add('/status-website-app/getworkflows',function($matches){
            try{
                TApp::contenttype('application/json');
                $export = DSTable::instance('status_website_workflows')->read()->get();
                TApp::result('data',$export);
                TApp::result('success',true);
            }catch(\Exception $e){
                TApp::result('msg', $e->getMessage());
            }
        },['get'],true);



        /*
        BasicRoute::add('/status-website/workflows',function($matches){
            try{
                TApp::contenttype('application/json');

                TApp::result('r',S::setWorkflows());

                TApp::result('success',true);
            }catch(\Exception $e){
                TApp::result('msg', $e->getMessage());
            }
        },['get','post'],true);
        */

    }
}