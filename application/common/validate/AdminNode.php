<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 2/2/17
 * Time: 11:39 PM
 */

namespace app\common\validate;


use think\Validate;

class AdminNode extends Validate
{
    protected $rule = [
        'pid' => 'number',
        'level' => 'number',
        'sort' => 'number',
        'is_menu' => 'in:0,1',
        'is_show' => 'in:0,1',
        'action' => 'require',
        'name' => 'require',
    ];
}