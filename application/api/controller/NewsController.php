<?php

namespace app\api\controller;

use think\Controller;

class NewsController extends Controller
{
    // redis的对象
    protected $_redis = null;

    public function _initialize() {
        // 读取配置文件中的关于redis的配置
        $config_redis = config('redis');

        // Redis数据库连接
        $this->_redis = new \Redis();
        $this->_redis->connect($config_redis['host'],$config_redis['port'],$config_redis['timeout']);
        $this->_redis->auth($config_redis['auth']);
    }

    public function index() {
        $id_arr = $this->_redis->zrange('news:zset:id',0,-1);
        // dump($id_arr);

        // 通过id可以得到每条id对应的具体的数据
        foreach ($id_arr as $id) {
            // hash的key
            $key = 'news:id:' . $id;
            $item = $this->_redis->hgetall($key);
            $data[] = $item;
        }

        return api($data);
    }
}
