<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use think\Db;


class IndexController extends BaseController
{

    public function index()
    {
        $this->redirect('login');
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
        $this->request->filter(['htmlspecialchars', 'trim']);
        $param = $this->request->param();

        // 参数验证
        $validate = validate('admin_user');
        if (!$validate->check($param)) {
            $this->result(null, -1, $validate->getError());
        }

        // 匹配账号
        $where['account'] = $param['account'];
        $user = Db::name('admin_user')->where($where)->find();
        if ($user === null) {
            $this->error('账号不存在');
        } else {
            // 匹配密码
            $password = hash_md5_password($param['password']);
            if ($user['password'] != $password) {
                $this->error('密码不正确');
            } else {
                // 处理登录成功的信息，包括判断角色，跳转页面
                $this->success('登录成功', 'index');
            }
        }
    }

}
