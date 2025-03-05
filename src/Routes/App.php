<?php

namespace Tualo\Office\StatusWebsite\Routes;

use Tualo\Office\Basic\TualoApplication as TApp;
use Tualo\Office\Basic\Route as BasicRoute;
use Tualo\Office\Basic\IRoute;
use Tualo\Office\DS\DSTable;
use Tualo\Office\StatusWebsite\State as S;

class App implements IRoute
{
    public static function register()
    {

        BasicRoute::add('/status-website-app/regions', function ($matches) {
            try {
                TApp::contenttype('application/json');
                $export = DSTable::instance('status_website_workflow_regions')->read()->get();

                TApp::result('data', $export);
                TApp::result('success', true);
            } catch (\Exception $e) {
                TApp::result('msg', $e->getMessage());
            }
        }, ['get'], true);

        BasicRoute::add('/status-website-app/getworkflows', function ($matches) {
            try {
                TApp::contenttype('application/json');
                $export = DSTable::instance('status_website_workflows')
                    ->f('enabled', 'eq', 1)
                    ->read()
                    ->get();


                foreach ($export as $index => $item) {


                    $current_since = '-1 day';
                    $since = (new \DateTime())->modify($current_since);

                    $table = \Tualo\Office\DS\DSTable::instance('status_website_workflow_logger');
                    $plot_data = $table
                        ->f('workflow_id', 'eq', $item['id'])
                        ->f('timestamp', 'gt',   $since->format('Y-m-d H:i:s'))
                        // ->f('status_code','eq','200')
                        ->get();
                    $apdex_satisfied = 600;
                    $apdex_tolerance = 0.3;
                    $apdex_count = [0, 0, 0];

                    foreach ($plot_data as $item) {
                        if ($item['microseconds'] / 1000 <= $apdex_satisfied) {
                            $apdex_count[0]++;
                        } else if ($item['microseconds'] / 1000 <= $apdex_satisfied * (1 + $apdex_tolerance)) {
                            $apdex_count[1]++;
                        } else {
                            $apdex_count[2]++;
                        }
                    }

                    $apdex = 0;
                    if (($apdex_count[0] + $apdex_count[1] + $apdex_count[2]) > 0)
                        $apdex = ($apdex_count[0] + $apdex_count[1] * 0.5) / ($apdex_count[0] + $apdex_count[1] + $apdex_count[2]);

                    $sla = [0, 0];
                    foreach ($plot_data as $item) {
                        if (intval($item['status_code']) >= 200) {
                            if (intval($item['status_code']) < 400) $sla[0]++;
                        }
                        $sla[1]++;
                    }
                    $sla_qoute = 0;
                    if ($sla[1] != 0)
                        $sla_qoute = ($sla[0] / $sla[1]) * 100;


                    $export[$index]['apdex'] = $apdex;
                    $export[$index]['sla'] = $sla_qoute;
                }





                TApp::result('data', $export);
                TApp::result('success', true);
            } catch (\Exception $e) {
                TApp::result('msg', $e->getMessage());
            }
        }, ['get'], true);


        BasicRoute::add('/status-website-app/pricing', function ($matches) {
            try {
                TApp::contenttype('application/json');
                $export = DSTable::instance('status_website_plans')->f('valid_from', '<=', (new \DateTime())->format('Y-m-d'))->f('valid_until', '>=', (new \DateTime())->format('Y-m-d'))->s('position', 'asc')->read()->get();
                TApp::result('plans', $export);

                $export = DSTable::instance('status_website_plan_features')->f('valid_from', '<=', (new \DateTime())->format('Y-m-d'))->f('valid_until', '>=', (new \DateTime())->format('Y-m-d'))->s('plan_id', 'asc')->s('position', 'asc')->read()->get();
                TApp::result('features', $export);

                TApp::result('success', true);
            } catch (\Exception $e) {
                TApp::result('msg', $e->getMessage());
            }
        }, ['get'], true);

        BasicRoute::add('/status-website-app/workflowinfo/(?P<workflow_id>\w+)', function ($matches) {
            $db = TApp::get('session')->getDB();
            TApp::contenttype('application/json');
            try {

                $sql = 'select  avg(microseconds) v from status_website_workflow_logger where workflow_id={workflow_id} and timestamp>now() + interval - 15 minute and status_code=200';

                $avg_current = $db->singleValue($sql, ['workflow_id' => $matches['workflow_id']], 'v');
                if (!($avg_current)) $avg_current = 0;
                TApp::result('avg_current', $avg_current);

                $sql = 'select  avg(microseconds) v from status_website_workflow_logger where workflow_id={workflow_id} and timestamp>now() + interval - 24 hour and status_code=200';
                $avg_24h = $db->singleValue($sql, ['workflow_id' => $matches['workflow_id']], 'v');
                if (!($avg_24h)) $avg_24h = 0;
                TApp::result('avg_24h', $avg_24h);

                $sql = 'select  avg(if(status_code<200,0,1)) v from status_website_workflow_logger where workflow_id={workflow_id} and timestamp>now() + interval - 24 hour';
                $sla_24h = $db->singleValue($sql, ['workflow_id' => $matches['workflow_id']], 'v');
                if (!($sla_24h)) $sla_24h = 0;
                TApp::result('sla_24h', $sla_24h);

                $sql = 'select  avg(if(status_code<200,0,1)) v from status_website_workflow_logger where workflow_id={workflow_id} and timestamp>now() + interval - 1 month';
                $sla_month = $db->singleValue($sql, ['workflow_id' => $matches['workflow_id']], 'v');
                if (!($sla_month)) $sla_month = 0;
                TApp::result('sla_month', $sla_month);

                $sql = 'select 
                    status_website_workflow_regions.continent_key,
                    status_website_workflows.apdex_goal,
                    status_website_workflows.sla_goal,
                    avg(if(status_code<200,0,1)) status_code_response,
                    avg(if(status_code=200,1,0)) status_code_200,
                    avg(microseconds) microseconds
                from 
                    status_website_workflow_logger
                    join status_website_workflow_regions 
                        on status_website_workflow_regions.id = status_website_workflow_logger.region_id 
                    join status_website_workflows
                        on status_website_workflows.id = status_website_workflow_logger.workflow_id

                    where workflow_id = {workflow_id}
                    and timestamp>now() + interval - 24 hour

                group by continent_key
                ';

                $continents = $db->direct($sql, ['workflow_id' => $matches['workflow_id']]);
                if (!($continents)) $continents = [];
                TApp::result('continents', $continents);
            } catch (\Exception $e) {
                TApp::result('msg', $e->getMessage());
            }
        }, ['get'], true);

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
