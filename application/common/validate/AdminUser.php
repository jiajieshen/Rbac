<?php
/**
 * Created by PhpStorm.
 * User: sum
 * Date: 1/5/17
 * Time: 11:06 PM
 */

namespace app\common\validate;


use think\Validate;

class AdminUser extends Validate
{
    protected $rule = [
        'account'    => 'require|alphaDash|length:3,32',
        'password'   => 'require|length:32',
        'repassword' => 'require|confirm:password',
        'name'       => 'require',
    ];

    protected $message = [
        'repassword' => '两次填写的密码不一致'
    ];
}