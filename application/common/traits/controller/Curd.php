<?php
namespace app\common\traits\controller;

use think\Config;
use think\Db;
use think\Loader;

trait Curd
{
    /**
     * 获取首页列表数据
     * PS：可以在 before index 方法中动态设置查询参数 $this->indexConfig
     */
    public function index()
    {
        $config = [
            'isPage' => true,
            'page' => $this->request->param('page/d', 1),
            'page_size' => $this->request->param('page_size/d', 15),
            'where' => null,
            'field' => true,
            'order' => null
        ];

        if (isset($this->indexConfig)) {
            $config = array_merge($config, $this->indexConfig);
        }

        $model = $this->getModel();
        if ($config['isPage']) {
            $list = $model->field($config['field'])
                ->where($config['where'])
                ->order($config['order'])
                ->page($config['page'], $config['page_size'])
                ->select();
            $data['page'] = $config['page'];
            $data['page_size'] = $config['page_size'];
            $data['list'] = $list;
        } else {
            $data = $model->field($config['field'])
                ->where($config['where'])
                ->order($config['order'])
                ->select();
        }

        $this->success('success', null, $data);
    }

    /**
     * 添加（若存在验证场景，则自动对应 add 场景）
     */
    public function add()
    {
        $param = $this->request->except(['id']);

        $controller = $this->request->controller();
        $action = $this->request->action();

        // 验证
        $validate = $this->getValidate($controller);
        if ($validate && !$validate->scene($action)->check($param)) {
            $this->error('添加失敗：' . $validate->getError());
        }

        // 写入数据
        $model = $this->getModelAndType($controller);
        switch ($model['type']) {
            case 'model':
                $result = $model['model']->isUpdate(false)->save($param);
                break;
            case 'db':
                Db::startTrans();
                try {
                    $result = $model['model']->insert($param);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error('添加失敗：' . $e->getMessage());
                }
                break;
        }

        if (isset($result) && $result) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 更新数据
     * 注意: 验证场景只能对应操作名
     */
    public function update()
    {
        // 参数
        $param = $this->request->param();
        if (!$param['id']) {
            $this->error('缺少参数 id');
        }

        $controller = $this->request->controller();
        $action = strtolower($this->request->action());

        // 验证
        $validate = $this->getValidate($controller);
        if ($validate && !$validate->scene($action)->check($param)) {
            $this->error('更新失敗：' . $validate->getError());
        }

        // 更新数据
        $model = $this->getModelAndType($controller);
        switch ($model['type']) {
            case 'model':
                $result = $model['model']->isUpdate(true)->save($param, ['id' => $param['id']]);
                break;
            case 'db':
                Db::startTrans();
                try {
                    $result = $model['model']->where('id', $param['id'])->update($param);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error('更新失敗：' . $e->getMessage());
                }
                break;
        }

        if (isset($result) && $result) {
            $this->success('更新成功');
        } else {
            $this->error('更新失败');
        }
    }

    /**
     * 编辑
     * ps: 默认直接返回数据库全部字段，可以在 before edit方法中，指定要返回的数据 $this->field;
     * @return mixed
     */
    public function edit()
    {
        // 参数
        $param = $this->request->param();
        if (!$param['id']) {
            $this->error('缺少参数 id');
        }

        $controller = $this->request->controller();

        $model = $this->getModel($controller);
        // 编辑
        if (isset($this->field)) {
            $data = $model->field($this->field)->find($param['id']);
        } else {
            $data = $model->find($param['id']);
        }

        if (!$data) {
            $this->error('该记录不存在！');
        } else {
            $this->success("获取成功", null, $data);
        }
    }

    /**
     * 回收站
     */
    public function recycleBin()
    {
        $this->indexConfig = [
            'where' => ['is_delete' => 1]
        ];
        $this->index();
    }

    /**
     * 默认删除操作
     */
    public function del()
    {
        $this->updateField('is_delete', 1, "移动到回收站成功");
    }

    /**
     * 从回收站恢复
     */
    public function recycle()
    {
        $this->updateField('is_delete', 0, "恢复成功");
    }

    /**
     * 默认禁用操作
     */
    public function forbid()
    {
        $this->updateField('status', 0, "禁用成功");
    }

    /**
     * 默认恢复操作
     */
    public function resume()
    {
        $this->updateField('status', 1, "恢复成功");
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
        $where['is_delete'] = 1;
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
        if (!isset($param['id'])) {
            $this->error('缺少参数 id');
        }
        if (!isset($param['sort'])) {
            $this->error('缺少参数 sort');
        }

        $ids = explode(',', $param['id']);
        $sorts = explode(',', $param['sort']);

        $model = $this->getModel();
        foreach ($ids as $i => $id) {
            if ($id) {
                $model->where('id', $id)->update(['sort' => $sorts[$i]]);
            }
        }

        $this->success('排序成功');
    }

    /**
     * 获取验证器
     *
     * @param string $controller
     * @return
     */
    protected function getValidate($controller = '')
    {
        $module = Config::get('traits.validate_path');
        if (!$controller) {
            $controller = $this->request->controller();
        }
        $validateClass = Loader::parseClass($module, 'validate', $controller);
        if (class_exists($validateClass)) {
            return new $validateClass();
        } else {
            return null;
        }
    }

    /**
     * 获取模型
     *
     * @param string $controller
     *
     * @return \think\db\Query|\think\Model|array
     */
    protected function getModel($controller = '', $type = false)
    {
        return $this->getModelAndType($controller, false);
    }

    /**
     * 获取模型及其类型（model 或者 db）
     *
     * @param string $controller
     * @param bool $type 是否返回模型的类型
     *
     * @return \think\db\Query|\think\Model|array
     */
    protected function getModelAndType($controller = '', $type = true)
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