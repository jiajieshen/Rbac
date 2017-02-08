<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 1/15/17
 * Time: 11:23 PM
 */

namespace app\admin\controller;


use app\common\controller\AdminController;
use app\common\model\AdminNode;

class AdminNodeController extends AdminController
{
    /**
     * 获取菜单
     */
    public function menu()
    {
        $adminNode = new AdminNode();
        $menus = $adminNode->getMenus($this->uid, $this->isAdmin);
        $this->success('success', '', $menus);
    }




}