<?php
namespace app\index\controller;

use think\Config;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        Db::name('admin_user')->where('id', 1)->find();

        return $this->fetch();
    }
}
