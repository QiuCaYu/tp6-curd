<?php

namespace app<namespace>;

use app\routing\BaseValidate;

class <validateName> extends BaseValidate
{

    /**
    * 定义验证规则
    * 格式：'字段名' =>  ['规则1','规则2'...]
    *
    * @var array
    */
    protected $rule = <rule>;

    /**
    * 验证字段描述
    * 格式：'字段名' =>  '字段描述'
    *
    * @var array
    */
    protected $attributes = <attributes>;

    /**
    * 定义错误信息
    * 格式：'字段名.规则名' =>  '错误信息'
    *
    * @var array
    */
    protected $message = [];
}