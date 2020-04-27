<?php

namespace app\admin\controller;

use think\Validate;

class NewsController extends BaseController
{
    // 新闻列表
    public function index() {
        // dump(session('admin.user'));

        // 得到zset中所有的id数据
        $id_arr = $this->_redis->zrange('news:zset:id',0,-1);
        // dump($id_arr);

        $data = [];
        // 通过id可以得到每条id对应的具体的数据
        foreach ($id_arr as $id) {
            // hash的key
            $key = 'news:id:' . $id;
            $item = $this->_redis->hgetall($key);
            $data[] = $item;
        }
        // dump($data);

        return view('',compact('data'));
        // $url = url('admin/news/create');
        // return '<a href="' . $url . '">添加新闻</a>';
    }

    // 新闻显示
    public function create() {
        // dump(config('redis'));die;
        return view();
    }

    // 添加新闻处理
    public function store() {
        $data = input();

        $validate = new Validate([
            'title|新闻标题'    =>  'require',
            'desn|新闻描述'     =>  'require',
            'author|新闻作者'   =>  'require',
            'body|新闻内容'     =>  'require'
        ]);

        if (!$validate->check($data)) {
            // 表单数据验证
            $this-> error($validate->getError());
        }else{
            // 得到自增长id值
            $idKey = 'news:id';
            $id = $this->_redis->incr($idKey);

            // 使用hash存储对应的id表单信息值
            $hKey = 'news:id:' . $id;
            // 给数据中添加id值
            $data['id'] = $id;
            $this->_redis->hMset($hKey,$data);

            // 把对应的id信息记录在zset中
            $zKey = 'news:zset:id';
            $this->_redis->zAdd($zKey,$id,$id);

            $this -> success('添加新闻成功！','admin/news/index');
        }
    }

    // 删除操作
    public function del($id) {
        // 获取删除的id值
        // $id = input();
        // return ['id' => $id];

        // 先删除hash对应的key
        $hKey = 'news:id:' . $id;
        $this->_redis->del($hKey);

        // 再删除zset数据中的元素
        $zKey = 'news:zset:id';
        $this->_redis->zrem($zKey, $id);

        return ['status' => 0, 'msg' => '删除成功'];
    }

    public function edit($id){
        $key = 'news:id:' . $id;
        $data = $this->_redis->hgetall($key);
        return view('',compact('data'));
    }

    public function update() {
        $data = input();
        // dump($data);die;

        $validate = new Validate([
            'title|新闻标题'    =>  'require',
            'desn|新闻描述'     =>  'require',
            'author|新闻作者'   =>  'require',
            'body|新闻内容'     =>  'require'
        ]);

        if (!$validate->check($data)) {
            // 表单数据验证
            $this-> error($validate->getError());
        }else{
            $id = $data['id'];

            // 使用hash存储对应的id表单信息值
            $hKey = 'news:id:' . $id;
            // 给数据中添加id值
            // $data['id'] = $id;
            $this->_redis->hMset($hKey,$data);

            $this -> success('修改新闻成功！','admin/news/index');
        }
    }

    public function delall(){
        $id_arr = $this->_redis->zrange('news:zset:id',0,-1);

        foreach($id_arr as $id) {
            // 先删除hash对应的key
            $hKey = 'news:id:' . $id;
            $this->_redis->del($hKey);

            // 再删除zset数据中的元素
            $zKey = 'news:zset:id';
            $this->_redis->zrem($zKey, $id);
        }

        return ['status' => 0, 'msg' => '全部删除成功！'];
    }

    public function logout() {
        session(null);
        $this->success('退出成功！');
    }
}
