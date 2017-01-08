<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 1/7/17
 * Time: 12:59 AM
 */

namespace app\common\controller;


use app\common\constants\SessionKey;
use app\common\model\AdminNode;
use think\Db;
use think\Session;

class AuthController extends BaseController
{
    /**
     * @var array 不需要被检测权限的方法列表
     */
    protected $nonAccessList = [];

    public function _initialize()
    {
        parent::_initialize();

        // for debug
        $userToken = $this->request->param('user_token');
        if ($userToken) {
            $user = Db::name('admin_user')->where(['token' => $userToken])->find();
            if ($user) {
                define(UID, $user['id']);
            }
        }

        // 用户
        defined('UID') or define('UID', session(SessionKey::UID));
        // 是否超级管理员
        defined('IS_ADMIN') or define('IS_ADMIN', session(SessionKey::IS_ADMIN));

        if (UID === null) {
            $this->backToLogin();
        } else {
            if (!$this->checkAccess()) {
                $this->error('缺少访问授权');
            }
        }
    }

    /**
     * 返回登录页
     */
    private function backToLogin()
    {
        if ($this->isAjaxOrPost()) {
            $this->error("登录超时，请重新登录！", url('index/index/login'));
        } else {
            $this->redirect('index/index/login');
        }
    }

    /**
     * 检测用户是否可以访问当前方法
     * return bool
     */
    private function checkAccess()
    {
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();

        // 判断方法是否不需要检测权限
        if (in_array($action, $this->nonAccessList)) {
            // ignore 无需检测
            return true;
        }

        // 超级管理员不需要检测
        if (Session::get(SessionKey::IS_ADMIN)) {
            // ignore 无需检测
            return true;
        }

        // 获取授权节点列表
        $accessNodeList = session(SessionKey::ACCESS_NODE_LIST);
        if (!$accessNodeList) {
            $nodeModel = new AdminNode();
            $accessNodeList = $nodeModel->getAccessNodeList(UID);
            // TODO : session 缓存 $accessNodeList
//            session(SessionKey::$PERMISSION_NODE_LIST, $accessNodeList);
        }

        // 构造 module/controller/action 的 node key
        $key = $module . DS . $controller . DS . $action;
        // 匹配权限列表
        foreach ($accessNodeList as $node) {
            if ($node['action'] == $key) {
                return true;
            }
        }
        return false;
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        Session::clear();
        $this->success('退出登录', 'index/index/index');
    }


}