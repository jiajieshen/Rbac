<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 1/15/17
 * Time: 11:23 PM
 */

namespace app\admin\controller;


use app\common\controller\AuthController;
use app\common\model\AdminNode;

class AdminNodeController extends AuthController
{

    /**
     * 获取菜单
     */
    public function getAdminMenus()
    {
        $adminNode = new AdminNode();
        $menus = $adminNode->getMenus(UID, 'admin');
        $this->success('success', '', $menus);
    }

}