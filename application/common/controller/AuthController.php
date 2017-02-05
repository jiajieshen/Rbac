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
    protected $uid;
    protected $isAdmin;

    /**
     * @var array 不需要被检测权限的方法列表
     */
    protected $nonAccessList = [];

    public function _initialize()
    {
        parent::_initialize();

        $this->uid = session(SessionKey::UID);
        $this->isAdmin = session(SessionKey::IS_ADMIN);

        if ($this->uid === null) {
            $this->error("登录超时，请重新登录");
        } else {
            if (!$this->checkAccess()) {
                $this->error('缺少访问授权');
            }
        }
    }

    /**
     * 检测用户是否可以访问当前方法
     * return bool
     */
    private function checkAccess()
    {
        // 超级管理员不需要检测
        if ($this->isAdmin) {
            // ignore 无需检测
            return true;
        }

        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();

        // 判断方法是否不需要检测权限
        if (in_array($action, $this->nonAccessList)) {
            // ignore 无需检测
            return true;
        }

        // 获取授权节点列表
        $accessNodeList = session(SessionKey::ACCESS_NODE_LIST);
        if (!$accessNodeList) {
            $nodeModel = new AdminNode();
            $accessNodeList = $nodeModel->getAccessNodeList($this->uid);
            // TODO : session 缓存 $accessNodeList
//            session(SessionKey::$PERMISSION_NODE_LIST, $accessNodeList);
        }

        // 构造 module/controller/action 的 node target action
        $target = $module . DS . $controller . DS . $action;
        // 匹配权限列表
        foreach ($accessNodeList as $node) {
            if ($node['action'] == $target) {
                return true;
            }
        }
        return false;
    }



}