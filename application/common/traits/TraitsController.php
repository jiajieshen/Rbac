<?php
namespace app\common\traits\controller;

use think\Config;
use think\Db;
use think\Loader;

trait TraitsController
{
    /**
     * 回收站
     */
    public function recycleBin()
    {
        $this->request->param(['is_delete' => '1']);
        return $this->index();
    }

    /**
     * 添加（若存在验证场景，则自动对应 add 场景）
     */
    public function add()
    {
        $controller = $this->request->controller();
        $action = $this->request->action();

        if ($this->request->isPost()) {
            //
            $data = $this->request->except(['id']);
            // 验证
            $validateClass = Loader::parseClass(Config::get('traits.validate_path'), 'validate', $controller);
            if (class_exists($validateClass)) {
                $validate = new $validateClass();
                if (!$validate->scene($action)->check($data)) {
                    $this->error('添加失敗：' . $validate->getError());
                }
            }

            // 写入数据
            $modelClass = Loader::parseClass(Config::get('traits.model_path'), 'model', $controller);
            if (class_exists($modelClass)) {
                //使用模型写入，可以在模型中定义更高级的操作
                $model = new $modelClass();
                $result = $model->isUpdate(false)->save($data);
            } else {
                // 简单的直接使用db写入
                Db::startTrans();
                try {
                    $model = Db::name($this->parseTable($controller));
                    $result = $model->insert($data);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    //
                    $this->error('添加失敗：' . $e->getMessage());
                }
            }

            $this->success('添加成功', 'index');
        } else {
            return $this->fetch();
        }
    }

    /**
     * 编辑
     * ps: 默认直接返回数据库全部字段，可以在 before edit方法中，指定要返回的数据 $this->vo;
     * TODO: 字段过滤
     * @return mixed
     */
    public function edit()
    {
        if ($this->request->isPost()) {
            $this->update();
        }

        // 参数
        $data = $this->request->param();
        if (!$data['id']) {
            $this->error('缺少参数 id');
        }

        $controller = $this->request->controller();
        // 编辑
        if (isset($this->vo)) {
            $vo = $this->vo;
        } else {
            $vo = $this->getModel($controller)->find($data['id']);
        }
        if (!$vo) {
            $this->error('该记录不存在！');
        } else {
            $this->assign("vo", $vo);
            return $this->fetch();
        }
    }

    /**
     * 更新数据
     * 注意: 验证场景只能对应操作名
     */
    public function update()
    {
        // 参数
        $data = $this->request->param();
        if (!$data['id']) {
            $this->error('缺少参数 id');
        }

        $controller = $this->request->controller();
        $action = strtolower($this->request->action());

        // 验证
        $validateClass = Loader::parseClass(Config::get('traits.validate_path'), 'validate', $controller);
        if (class_exists($validateClass)) {
            $validate = new $validateClass();
            if (!$validate->scene($action)->check($data)) {
                $this->error('更新失敗：' . $validate->getError());
            }
        }

        // 更新数据
        $modelClass = Loader::parseClass(Config::get('traits.model_path'), 'model', $controller);
        if (class_exists($modelClass)) {
            // 使用模型更新，可以在模型中定义更高级的操作
            $model = new $modelClass();
            $result = $model->isUpdate(true)->save($data, ['id' => $data['id']]);
        } else {
            // 简单的直接使用db更新
            Db::startTrans();
            try {
                $model = Db::name($this->parseTable($controller));
                $result = $model->where('id', $data['id'])->update($data);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                //
                $this->error('更新失敗：' . $e->getMessage());
            }
        }

        $this->success('更新成功', 'index');
    }


    /**
     * 默认删除操作
     */
    public function del()
    {
        $this->updateField($this->FIELD_IS_DELETE, 1, "移动到回收站成功");
    }

    /**
     * 从回收站恢复
     */
    public function recycle()
    {
        $this->updateField($this->FIELD_IS_DELETE, 0, "恢复成功");
    }

    /**
     * 默认禁用操作
     */
    public function forbid()
    {
        $this->updateField($this->FIELD_STATUS, 0, "禁用成功");
    }

    /**
     * 默认恢复操作
     */
    public function resume()
    {
        $this->updateField($this->FIELD_STATUS, 1, "恢复成功");
    }

    /**
     * 永久删除
     */
    public function delForever()
    {
        $model = $this->getModel();
        $pk = $model->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === $model->where($where)->delete()) {
            $this->error('删除失败：' . $model->getError());
        } else {
            $this->success('删除成功');
        }
    }

    /**
     * 清空回收站
     */
    public function clear()
    {
        $model = $this->getModel();
        $where[$this->FIELD_IS_DELETE] = 1;
        if (false === $model->where($where)->delete()) {
            $this->error("清空回收站失败：" . $model->getError());
        } else {
            $this->success('清空回收站成功');
        }
    }

    /**
     * 保存排序
     */
    public function saveOrder()
    {
        $param = $this->request->param();
        if (!isset($param['sort'])) {
            $this->error('缺少参数 sort');
        }

        $model = $this->getModel();
        foreach ($param['sort'] as $id => $sort) {
            $model->where('id', $id)->update(['sort' => $sort]);
        }

        $this->success('保存排序成功', '');
    }

    /**
     * 获取模型
     *
     * @param string $controller
     * @param bool $type 是否返回模型的类型
     *
     * @return \think\db\Query|\think\Model|array
     */
    protected function getModel($controller = '', $type = false)
    {
        $module = Config::get('traits.model_path');
        if (!$controller) {
            $controller = $this->request->controller();
        }
        if (class_exists($modelName = Loader::parseClass($module, 'model', $controller))) {
            $model = new $modelName();
            $modelType = 'model';
        } else {
            $model = Db::name($this->parseTable($controller));
            $modelType = 'db';
        }

        return $type ? ['type' => $modelType, 'model' => $model] : $model;
    }

    /**
     * 格式化表名，将 /. 转为 _ ，支持多级控制器
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function parseTable($name = '')
    {
        if (!$name) {
            $name = $this->request->controller();
        }

        return str_replace(['/', '.'], '_', $name);
    }

    /**
     * 默认更新字段方法
     *
     * @param string $field 更新的字段
     * @param string|int $value 更新的值
     * @param string $msg 操作成功提示信息
     * @param string $pk 主键，默认为主键
     * @param string $input 接收参数，默认为主键
     */
    protected function updateField($field, $value, $msg = "操作成功", $pk = "", $input = "")
    {
        $model = $this->getModel();
        if (!$pk) {
            $pk = $model->getPk();
        }
        if (!$input) {
            $input = $model->getPk();
        }
        $ids = $this->request->param($input);
        $where[$pk] = ["in", $ids];
        if (false === $model->where($where)->update([$field => $value])) {
            $this->error($model->getError());
        }

        $this->success($msg, '');
    }

    /**
     * 过滤禁止操作某些主键
     *
     * @param        $filterData
     * @param string $error
     * @param string $method
     * @param string $key
     */
    protected function filterId($filterData, $error = '该记录不能执行此操作', $method = 'in_array', $key = 'id')
    {
        $data = $this->request->param();
        if (!isset($data[$key])) {
            $this->error('404 缺少必要参数');
        }
        $ids = is_array($data[$key]) ? $data[$key] : explode(",", $data[$key]);
        foreach ($ids as $id) {
            switch ($method) {
                case '<':
                case 'lt':
                    $ret = $id < $filterData;
                    break;
                case '>':
                case 'gt':
                    $ret = $id < $filterData;
                    break;
                case '=':
                case 'eq':
                    $ret = $id == $filterData;
                    break;
                case '!=':
                case 'neq':
                    $ret = $id != $filterData;
                    break;
                default:
                    $ret = call_user_func_array($method, [$id, $filterData]);
                    break;
            }
            if ($ret) {
                $this->error($error);
            }
        }
    }
}