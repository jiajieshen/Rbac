<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 1/7/17
 * Time: 1:27 PM
 */

namespace app\common\model;


use think\Db;
use think\Model;

class AdminNode extends Model
{
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
            ->where([
                'user.id'     => $uid,
                'user.status' => 1,
                'role.status' => 1,
                'node.status' => 1
            ])
            ->distinct('node.id')
            ->select();
    }
}