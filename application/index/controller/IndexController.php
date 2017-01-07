<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\model\AdminUser;
use app\common\constants\SessionKey;
use think\Db;


class IndexController extends BaseController
{

    public function index()
    {
        return $this->fetch();
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
            $this->success('注册成功', 'login');
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
                // 处理登录成功，包括判断角色，跳转页面
                $this->handleLoginSuccess($user);
            }
        }
    }

    /**
     * 处理登录成功的信息，包括判断角色，跳转页面
     *
     * @param array $user 用户表信息
     */
    private function handleLoginSuccess($user)
    {
        // 保存登录信息
        $update['token'] = randString(16);
        $update['last_login_time'] = time();
        $update['last_login_ip'] = $this->request->ip();
        $result = Db::name('admin_user')->where('id', $user['id'])->update($update);
        if (!$result) {
            $this->error('登录失败，请重试！');
        }

        // 保存用户 session
        session(SessionKey::UID, $user['id']);
        session(SessionKey::USERNAME, $user['name']);
        session(SessionKey::LAST_LOGIN_TIME, $user['last_login_time']);
        session(SessionKey::LAST_LOGIN_IP, $user['last_login_ip']);

        // 根据是否为后台管理员跳转
        if ($user['id'] == 1) {
            session(SessionKey::IS_ADMIN, true);
            $this->success('登录成功', 'admin/index/index');
        } else {
            session(SessionKey::IS_ADMIN, false);
            $this->success('登录成功', 'user/index/index');
        }
    }

}
