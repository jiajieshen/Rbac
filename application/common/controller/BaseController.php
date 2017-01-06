<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 12/23/16
 * Time: 12:50 AM
 */

namespace app\common\controller;


use think\Config;
use think\Controller;

class BaseController extends Controller
{

    protected function _initialize()
    {
        parent::_initialize();

        // 统一参数过滤
        $this->request->filter(['htmlspecialchars', 'trim']);
    }

    /**
     * 判断非 ajax 和非 post 请求
     * @return bool
     */
    public function isNotAjaxAndPost()
    {
        return !$this->request->isAjax() && !$this->request->isPost();
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        $isAjax = $this->request->isAjax();
        $isPost = $this->request->isPost();
        if (!$isAjax && $isPost) { // 非 ajax 下 post 请求返回 json 类型
            return 'json';
        }
        return $isAjax ? Config::get('default_ajax_return') : Config::get('default_return_type');
    }


}