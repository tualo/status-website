<?php
namespace Tualo\Office\StatusWebsite;

use Tualo\Office\DS\DSTable;
use Tualo\Office\Basic\TualoApplication;
class State {

    public static function setWorkflows():array{
        $export = DSTable::instance('view_status_website_workflow_config')->read()->get();

        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->select(1);

        for($i=0;$i<count($export);$i++){
            $redis->set($export[$i]['key'],$export[$i]['cnf']);
        }
        return $export;
    }

    public static function getAll():array{
        $db = TualoApplication::get('session')->getDB();
        

        $redis = new \Redis();
        
        $list = [];
        $redis->connect('127.0.0.1', 6379);
        $redis->select(1);
        $allKeys = $redis->keys('sample_*');
        foreach( $allKeys as $key ){
            $data = $redis->get($key);
            $data = json_decode($data,true);
            // $data['key'] = $key;
             
            $list[] = $data[0];

        


        $sql = 'insert ignore into status_website_workflow_logger (workflow_id,
        step_id,
        region_id,
        timestamp,
        microseconds,
        status_code,
        status,
        proto,
        contentlength,
        proto_major,
        proto_minor) values (
            {workflow_id},
        {step_id},
        {region_id},
        FROM_UNIXTIME({timestamp}),
        {microseconds},
        {status_code},
        {status},
        {proto},
        {contentlength},
        {proto_major},
        {proto_minor})';
        try {
            $db->direct($sql,$data[0]);
            $redis->delete($key);
        }catch(\Exception $e){
            TualoApplication::result('msg', $e->getMessage());
        }

    }


        return $list;
    }
}
