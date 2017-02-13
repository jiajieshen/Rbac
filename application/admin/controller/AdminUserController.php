<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 2/8/17
 * Time: 10:52 PM
 */

namespace app\admin\controller;


use app\common\controller\AdminController;

class AdminUserController extends AdminController
{

    public function search()
    {
        $key = $this->request->param('name');
        $startTime = strtotime($this->request->param('start_time'));
        $endTime = strtotime($this->request->param('end_time'));

        $where = [];
        // 昵称关键字
        if ($key) {
            $where['name'] = ['like', '%name%'];
        }

        // 创建时间
        if ($startTime && $endTime) {
            $where['create_time'] = ['between time', [$startTime, $endTime]];
        } else {
            if ($startTime) {
                $where['create_time'] = ['> time', $startTime];
            }
            if ($endTime) {
                $where['create_time'] = ['< time', $endTime];
            }
        }

        $this->indexConfig = [
            'where' => $where
        ];
        $this->index();
    }
}