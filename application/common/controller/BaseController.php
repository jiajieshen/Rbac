<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 12/23/16
 * Time: 12:50 AM
 */

namespace app\common\controller;


use think\Controller;

class BaseController extends Controller
{

    protected function _initialize()
    {
        parent::_initialize();

        // 统一参数过滤
        $this->request->filter(['htmlspecialchars', 'trim']);
    }
}