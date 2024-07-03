<?php
namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as TApp;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\StatusWebsite\State as S;

class AppSlices implements IRoute{
    public static function register(){
       
        BasicRoute::add('/status-website-app/slices/(?P<workflow_id>\w+)/(?P<region_id>\w+/(?P<buckets>\w+)',function($matches){
            $db = TApp::get('session')->getDB();
            TApp::contenttype('application/json');
            echo 'HERE';
            try{
                $sql = file_get_contents(__DIR__.'/templates/slices.sql');
                if ($matches['region_id']=='all'){
                    $sql = str_replace('and region_id = {region_id}','',$sql);
                }
                $data= $db->direct($sql,[
                    'workflow_id'=>$matches['workflow_id'],
                    'start_timestamp'=>(new \DateTime())->sub(\DateInterval::createFromDateString('1 day'))->format('Y-m-d H:i:s'),
                    'stop_timestamp'=>(new \DateTime())->format('Y-m-d H:i:s'),
                    'region_id'=>$matches['region_id'],
                    'buckets'=>$matches['buckets'],
                ]);
                if (!($data)) $data = [];
                if (count($data)==1){
                    $data[] = $data[0];
                    $data[0]['stop_timestamp'] =$data[0]['start_timestamp'];

                    $data[1]['start_timestamp'] =$data[1]['stop_timestamp'];
                    
                    $data[1]['cluster_id'] = "99999";
                }
                TApp::result('data', $data);
                TApp::result('success',true);
            }catch(\Exception $e){
                TApp::result('msg', $e->getMessage());
            }
        },['get'],true);


    }
}