<?php
/**
 * BaseModel.php
 * @author wt 2022/5/28
 */
namespace app\routing;

use think\facade\Snowflake;
use think\helper\Str;
use think\Model;

abstract class BaseModel extends Model
{
    /*** 是否删除 */
    // 1-删除
    const DEL_FLAG_YES = 1;
    // 0-可用
    const DEL_FLAG_NO = 0;

    /**
     * 新增前事件
     * @param Model $model
     * @return mixed|void
     * @author wt 2022/5/13
     */
    public static function onBeforeInsert($model)
    {
//        $redis = \think\facade\Cache::store('redis');
        $id = Snowflake::generate();
//        $cacheKeyPrefix = $redis->getCacheKey('db:' . $model->getTable() . ':id:');
//        $cacheKey = $cacheKeyPrefix . $id;
//        // 避免重复
//        while (true) {
//            if (!$redis->setnx($cacheKey, 1)) {
//                $id = Snowflake::generate();
//                $cacheKey = $cacheKeyPrefix . $id;
//                continue;
//            }
//            $redis->expire($cacheKey, 60);
//            break;
//        }
        $model->setAttr('id', (string)$id);
    }

    /**
     * 逻辑删除
     * @return bool
     * @author wt 2022/6/24
     */
    public function del()
    {
        return $this->save([
            'del_flag' => self::DEL_FLAG_YES
        ]);
    }

    /**
     * 添加未删除的条件
     * @param $bool
     * @return BaseModel
     * @author qjy 2023/6/10
     * @update qjy 2023/6/10
     */
    public function whereNotDel($bool = true){
        if($bool){
            return $this->where('del_flag',self::DEL_FLAG_NO);
        }
        return $this->where('del_flag',self::DEL_FLAG_YES);
    }

    /**
     * 获取驼峰写法字段
     * @param array $fields
     * @return array
     * @author qjy 2023/6/12
     * @update qjy 2023/6/12
     */
    public function getFieldRuleExcept(array $fields = []){

        $data = [];
        foreach ($this->fieldComment as $field => $comment) {
            if(!in_array($field,$fields)){
                $data[] = Str::camel($field);
            }
        }
        return $data;
    }
}