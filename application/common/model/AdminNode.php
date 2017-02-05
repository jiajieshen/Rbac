<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 1/7/17
 * Time: 1:27 PM
 */

namespace app\common\model;


use think\Db;

class AdminNode extends BaseModel
{

    /**
     * 根据用户 id 获取菜单列表
     * @param int $uid
     * @param bool $isAdmin
     * @return array
     */
    public function getMenus($uid = 0, $isAdmin = false)
    {
        $list = null;
        if ($isAdmin) {
            $list = Db::name('admin_node')
                ->field(['id', 'pid', 'level', 'action', 'name', 'icon'])
                ->where([
                    'status' => 1,
                    'is_menu' => 1,
                    'is_show' => 1
                ])
                ->order('pid,level,sort')
                ->select();
        } else {
            $prefix = config("database.prefix");
            $list = Db::table($prefix . 'admin_user')
                ->alias('user')
                ->join($prefix . 'admin_role_user role_user', 'role_user.user_id = user.id')
                ->join($prefix . 'admin_role role', 'role.id = role_user.role_id')
                ->join($prefix . 'admin_access access', 'access.role_id = role.id')
                ->join($prefix . 'admin_node node', 'node.id = access.node_id')
                ->field(['node.id', 'node.pid', 'node.level', 'node.action', 'node.name', 'node.icon'])
                ->distinct('node.id')
                ->where([
                    'user.id' => $uid,
                    'user.status' => 1,
                    'role.status' => 1,
                    'node.status' => 1,
                    'node.is_menu' => 1,
                    'node.is_show' => 1
                ])
                ->order('pid,level,sort')
                ->select();
        }

        // 将 list 转成 menu tree
        return list_to_tree($list);
    }


    /**
     * 获取授权节点列表
     *
     * @param int $uid 用户 id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAccessNodeList($uid = 0)
    {
        $prefix = config("database.prefix");
        return Db::table($prefix . 'admin_user')
            ->alias('user')
            ->join($prefix . 'admin_role_user role_user', 'role_user.user_id = user.id')
            ->join($prefix . 'admin_role role', 'role.id = role_user.role_id')
            ->join($prefix . 'admin_access access', 'access.role_id = role.id')
            ->join($prefix . 'admin_node node', 'node.id = access.node_id')
            ->field("node.action")
            ->distinct('node.id')
            ->where([
                'user.id'     => $uid,
                'user.status' => 1,
                'role.status' => 1,
                'node.status' => 1
            ])
            ->select();
    }
}