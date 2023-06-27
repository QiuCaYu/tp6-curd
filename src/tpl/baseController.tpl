<?php
namespace app\<PATH>;

use think\App;

/**
 * 控制器基础类
 */
abstract class BaseController extends \app\routing\BaseController
{
    public $middleware = [

    ];
    
    // 初始化
    protected function initialize()
    {
        if($this->request->isPost() !== true){

        }
    }

}
