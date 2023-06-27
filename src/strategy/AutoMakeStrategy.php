<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 10:54 PM
 */

namespace cayu\tp6curd\strategy;

use cayu\tp6curd\template\IAutoMake;

class AutoMakeStrategy
{
    protected $strategy;

    public function Context(IAutoMake $obj)
    {
        $this->strategy = $obj;
    }

    public function executeStrategy($flag, $path, $other, $param)
    {
        $this->strategy->check($flag, $path, $other);
        $this->strategy->make($flag, $path, $other, $param);
    }
}