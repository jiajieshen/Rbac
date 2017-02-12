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

    /**
     * 从二维数组中取出自己要的KEY值
     * @param  array $arrData
     * @param string $key
     * @param $im true 返回逗号分隔
     * @return array
     */
    function filter_value($arrData, $key, $im = false)
    {
        $re = [];
        foreach ($arrData as $k => $v) {
            if (isset($v[$key])) $re[] = $v[$key];
        }
        if (!empty($re)) {
            $re = array_flip(array_flip($re));
            sort($re);
        }

        return $im ? implode(',', $re) : $re;
    }
}