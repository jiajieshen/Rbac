<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 2/8/17
 * Time: 11:10 PM
 */

namespace app\common\model;


use think\Db;

class AdminRole extends BaseModel
{
    public function users($roleId = 0)
    {
        // 用户列表
        $userList = Db::name("admin_user")
            ->field('id,account,name')
            ->where(['status' => 1, 'is_delete' => 0])
            ->select();

        // 已授权用户
        $roleUsers = Db::name("admin_role_user")
            ->field('user_id')
            ->where("role_id", $roleId)
            ->distinct(true)
            ->select();
        $roleUserIds = array_column($roleUsers, 'user_id');

        // 匹配
        foreach ($userList as $i => $user) {
            $userList[$i]['is_access'] = in_array($user['id'], $roleUserIds);
        }

        return $userList;
    }

    public function saveUsers($roleId, $data = null)
    {
        // 先删除旧数据
        Db::name('admin_role_user')->where(['role_id' => $roleId])->delete();

        // 插入新数据
        if ($data) {
            return Db::name('admin_role_user')->insertAll($data);
        } else {
            return true;
        }
    }

    public function accessNodeList($roleId)
    {
        // 所有可用的授权节点
        $list = Db::name('admin_node')
            ->where(['is_delete' => 0, 'status' => 1])
            ->order('pid,level,sort')
            ->select();

        // 已授权列表
        $accessNodeList = Db::name('admin_access')
            ->where(['role_id' => $roleId])
            ->field('node_id')
            ->distinct(true)
            ->select();
        $accessNodeList = array_column($accessNodeList, 'node_id');

        // 匹配
        foreach ($list as $i => $node) {
            $list[$i]['is_access'] = in_array($node['id'], $accessNodeList);
        }

        return list_to_tree($list);
    }

    public function saveAccessNodeList($roleId, $data = null)
    {
        // 先删除旧数据
        Db::name('admin_access')->where(['role_id' => $roleId])->delete();

        // 插入新数据
        if ($data) {
            return Db::name('admin_access')->insertAll($data);
        } else {
            return true;
        }
    }
}