<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 2/3/17
 * Time: 12:52 AM
 */

namespace app\admin\controller;


use app\common\controller\AdminController;
use app\common\model\AdminRole;

class AdminRoleController extends AdminController
{
    /**
     *
     */
    public function users()
    {
        $id = $this->request->param('id');
        if (!$id) {
            $this->error('缺少 id 参数');
        }

        $AdminRole = new AdminRole();
        $users = $AdminRole->users($id);

        $this->success('获取成功', null, $users);
    }

    public function saveUsers()
    {
        $id = $this->request->param('id');
        if (!$id) {
            $this->error('缺少 id 参数');
        }
        $uids = $this->request->param('uid');

        $data = [];
        if ($uids) {
            $uids = explode(',', $uids);
            foreach ($uids as $i => $uid) {
                if ($uid) {
                    $data[$i]['role_id'] = $id;
                    $data[$i]['user_id'] = $uid;
                }
            }
        }

        $AdminRole = new AdminRole();
        $result = $AdminRole->saveUsers($id, $data);
        if ($result) {
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }


    public function access()
    {
        $id = $this->request->param('id/d');
        if (!$id) {
            $this->error('缺少 id 参数');
        }
        $uids = $this->request->param('uid');


    }
}