<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\model\AdminUser;
use think\Db;


class IndexController extends BaseController
{

    public function index()
    {
        $this->redirect('login');
    }

    /**
     * 注册
     */
    public function signUp()
    {
        if ($this->isNotAjaxAndPost()) {
            return $this->fetch();
        }

        $param = $this->request->param();
        $param['password'] = hash_md5_password($param['password']);
        $param['repassword'] = hash_md5_password($param['repassword']);

        //验证字段
        $validate = validate('admin_user');
        if (!$validate->check($param)) {
            $this->error($validate->getError());
        }

        // 匹配账号
        $where['account'] = $param['account'];
        if (AdminUser::where($where)->find()) {
            $this->error('账号已存在');
        }

        // 保存
        unset($param['repassword']);
        $user = new AdminUser($param);
        $result = $user->allowField(true)->save();
        if ($result) {
            $this->success('注册成功');
        } else {
            $this->error('注册失败：' . $user->getError());
        }
    }

    /**
     * 登录
     */
    public function login()
    {
        if ($this->isNotAjaxAndPost()) {
            return $this->fetch();
        }

        // 参数处理
        $param = $this->request->param();
        $param['password'] = hash_md5_password($param['password']);

        // 匹配账号
        $where['account'] = $param['account'];
        $user = Db::name('admin_user')->where($where)->find();
        if ($user === null) {
            $this->error('账号不存在');
        } else {
            // 匹配密码
            if ($user['password'] != $param['password']) {
                $this->error('密码不正确');
            } else {
                // 处理登录成功的信息，包括判断角色，跳转页面
                $this->success('登录成功', 'index');
            }
        }
    }

}
