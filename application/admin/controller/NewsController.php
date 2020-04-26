<?php

namespace app\admin\controller;

use think\Validate;

class NewsController extends BaseController
{
    // 新闻列表
    public function index() {
        // dump(session('admin.user'));
        $url = url('admin/news/create');

        return '<a href="' . $url . '">添加新闻</a>';
    }

    // 新闻显示
    public function create() {
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
            // Redis数据库连接
            $redis = new \Redis();
            $redis->connect('101.200.59.204',6379,10);
            $redis->auth('arceus');

            // 得到自增长id值
            $idKey = 'news:id';
            $id = $redis->incr($idKey);

            // 存储对应的id表单信息值
            $hKey = 'news:id:' . $id;
            $data['id'] = $id;
            $redis->hMset($hKey,$data);

            // 把对应的id信息记录在zset中
            $zKey = 'news:zset:id';
            $redis->zAdd($zKey,$id,$id);

            $this -> success('添加新闻成功！','admin/news/index');
        }
        // $res = validate($data,$rules);
        // if($res !== true){
        //     $this->error($res);
        // }else{
        //     dump($data);
        // }

    }
}
