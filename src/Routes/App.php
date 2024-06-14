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

                foreach($export as &$item){
                    
                    
                    $current_since = '-1 day';
                    $since = (new \DateTime())->modify($current_since) ;

                    $table = \Tualo\Office\DS\DSTable::instance('status_website_workflow_logger');
                    $plot_data = $table
                        ->f('workflow_id','eq',$item['id'])
                        //->f('region_id','eq',$region_id)
                        ->f('timestamp','gt',   $since->format('Y-m-d H:i:s') )
                        // ->f('status_code','eq','200')
                        ->get();
                    $apdex_satisfied = 600;
                    $apdex_tolerance = 0.3;
                    $apdex_count = [0,0,0];

                    foreach($plot_data as $item){
                        if ($item['microseconds']/1000<=$apdex_satisfied){
                            $apdex_count[0]++;
                        }else if ($item['microseconds']/1000<=$apdex_satisfied*(1+$apdex_tolerance)){
                            $apdex_count[1]++;
                        }else{
                            $apdex_count[2]++;
                        }
                    }
    
                    $apdex = 0;
                    if ( ($apdex_count[0]+$apdex_count[1]+$apdex_count[2]) > 0)
                    $apdex = ($apdex_count[0]+$apdex_count[1]*0.5) / ($apdex_count[0]+$apdex_count[1]+$apdex_count[2]);
    
                    $sla=[0,0];
                    foreach($plot_data as $item){
                        if ( intval($item['status_code']) >=200 ){
                            if ( intval($item['status_code']) <400 ) $sla[0]++;
                        }
                        $sla[1]++;
                    }
                    $sla_qoute = 0;
                    if ($sla[1]!=0)
                    $sla_qoute = ($sla[0]/$sla[1])*100;

                    $item['apdex'] = $apdex;
                    $item['sla'] = $sla_qoute;
                }
                 

                

                
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