<?php

namespace app\admin\controller;

use think\Controller;

class BaseController extends Controller
{
    public function _initialize() {
        if (!session('?admin.user')) {
            // 用户没有登录
            return $this->redirect(url('admin/login/index'));
        }
    }
}
