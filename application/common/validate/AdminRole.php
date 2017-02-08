<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 2/8/17
 * Time: 11:27 PM
 */

namespace app\common\validate;


use think\Validate;

class AdminRole extends Validate
{
    protected $rule = [
        'name' => 'require'
    ];
}