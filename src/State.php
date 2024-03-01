<?php
namespace Tualo\Office\StatusWebsite;

class State {

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
