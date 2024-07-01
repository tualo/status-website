<?php
namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as TApp;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\StatusWebsite\State as S;

class AppTimeline implements IRoute{
    public static function register(){
       
        BasicRoute::add('/status-website-app/timeline/(?P<workflow_id>\w+)/(?P<region_id>\w+)',function($matches){
            $db = TApp::get('session')->getDB();
            TApp::contenttype('application/json');
            try{
   
                ;
                $sql = file_get_contents(__DIR__.'/templates/timeline.sql');
                $data= $db->direct($sql,[
                    'workflow_id'=>$matches['workflow_id'],
                    'start_timestamp'=>(new \DateTime())->sub(\DateInterval::createFromDateString('1 day'))->format('Y-m-d H:i:s'),
                    'stop_timestamp'=>(new \DateTime())->format('Y-m-d H:i:s'),
                    'region_id'=>$matches['region_id']
                ]);
                if (!($data)) $data = [];
                TApp::result('data', $data);
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