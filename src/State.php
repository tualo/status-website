<?php
namespace Tualo\Office\StatusWebsite;

use Tualo\Office\DS\DSTable;

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
        $redis = new \Redis();
        
        $list = [];
        $redis->connect('127.0.0.1', 6379);
        $redis->select(1);
        $allKeys = $redis->keys('*');
        foreach( $allKeys as $key ){
            $data = $redis->get($key);
            $data = json_decode($data,true);
            $data['key'] = $key;
            $list[] = $data;
        }
        return $list;
    }
}
