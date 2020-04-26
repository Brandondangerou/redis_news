<?php

namespace app\admin\controller;

use think\Controller;

class BaseController extends Controller
{
    // redis的对象
    protected $_redis = null;

    public function _initialize() {
        if (!session('?admin.user')) {
            // 用户没有登录
            return $this->redirect(url('admin/login/index'));
        }

        // 读取配置文件中的关于redis的配置
        $config_redis = config('redis');

        // Redis数据库连接
        $this->_redis = new \Redis();
        $this->_redis->connect($config_redis['host'],$config_redis['port'],$config_redis['timeout']);
        $this->_redis->auth($config_redis['auth']);
    }
}
