<?php

namespace app\admin\controller;

use think\Controller;

class LoginController extends Controller
{
    // 后台登录显示
    public function index() {
        return $this -> fetch();
    }

    // 后台登陆处理
    public function login() {
        // 表单数据
        $username = input('username');
        $password = input('password');

        // dump($username);die;
        // 查询数据库，nosql中的key的设计
        $loginKey = 'user:'.$username;

        $redis = new \Redis();
        $redis->connect('101.200.59.204',6379,10);
        $redis->auth('arceus');

        // 判断一下key是否存在
        if(!$redis->exists($loginKey)) {
            return $this->error('登陆失败！');
        }

        // 获取key中保存的密码
        $pwd = $redis->get($loginKey);
        if($pwd != $password) {
            if($redis->exists('news:' . $username . ':count')){
                $count = $redis->get('news:' . $username . ':count');
                if($count >= 2){
                    $redis->set('news:' . $username . ':count',5);
                    $notallow = $redis->expire('news:' . $username . ':count',120);
                    if($notallow){
                        $this->error('密码错误3次，请在120秒后重试');
                        exit;
                    }
                }
                $redis->incr('news:' . $username . ':count');
            }else{
                $redis->set('news:' . $username . ':count',1);
            }
            return $this->error('登录失败！');
        }else{
            $count = $redis->get('news:' . $username . ':count');
            if($count == 5){
                $this->error('密码错误3次，请在120秒后重试');
                exit;
            }
        }

        // 写入到session中
        session('admin.user',$username);
        // 登录成功跳转
        return $this->success('登录成功', url('admin/news/index'));
    }
}
