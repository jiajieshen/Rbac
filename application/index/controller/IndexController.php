<?php
namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\model\AdminUser;
use app\common\constants\SessionKey;
use think\Db;
use think\Session;


class IndexController extends BaseController
{
    /**
     * 注册
     */
    public function signUp()
    {
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
        if (Db::name('admin_user')->where($where)->find()) {
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
    public function signIn()
    {
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
                // 处理登录成功事件，保存相关信息
                $this->handleLoginSuccess($user);
            }
        }
    }

    /**
     * 处理登录成功事件，保存相关信息
     *
     * @param array $user 用户
     */
    private function handleLoginSuccess($user)
    {
        // 保存登录信息
        $update['token'] = randString(16);
        $update['last_login_time'] = time();
        $update['last_login_ip'] = $this->request->ip();
        $result = Db::name('admin_user')->where('id', $user['id'])->update($update);
        if (!$result) {
            $this->error('登录失败，请重试');
        }

        // 保存用户 session
        session(SessionKey::UID, $user['id']);
        session(SessionKey::USERNAME, $user['name']);
        session(SessionKey::LAST_LOGIN_TIME, $user['last_login_time']);
        session(SessionKey::LAST_LOGIN_IP, $user['last_login_ip']);

        //  保存是否为后台管理员
        if ($user['id'] == 1) {
            session(SessionKey::IS_ADMIN, true);
        } else {
            session(SessionKey::IS_ADMIN, false);
        }

        $this->success('登录成功');
    }

    /**
     * 退出登录
     */
    public function signOut()
    {
        Session::clear();
        $this->success('退出登录');
    }

}
