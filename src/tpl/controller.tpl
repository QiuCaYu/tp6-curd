<?php
namespace app<namespace>;

<useController>;
<useModel>;

use tpApi\http\Response;
use hg\apidoc\annotation as Apidoc;

/**
* @Apidoc\Group("<controller>")
* @Apidoc\Title("<controller>管理")
*/
class <controller> extends BaseController
{
    /**
    * 获取列表
    * @Apidoc\Desc("获取列表")
    * @Apidoc\Method("POST")
    * @Apidoc\Author("xxx")
    * @Apidoc\Tag("获取列表")
    * @Apidoc\Returned("total", type="string", desc="总条数"),
    * @Apidoc\Returned("list", type="array", desc="列表", default="[]",
    *      @Apidoc\Returned("id", type="string", desc="id"),
    *      @Apidoc\Returned("add_time", type="datetime", desc="添加时间"),
    *      @Apidoc\Returned("update_time", type="datetime", desc="更新时间")
    * )
    */
    public function index()
    {
        $page  = $this->request->post('page',1);
        $pagesize  = $this->request->post('pagesize',20);
        $fields = [<columnFields>];
        $where = [];
        $<model> = new <model>();
        $list = $<model>->whereNotDel()->field($fields)->page($page, $pagesize)->select();
        $data = [
            'total' => $list->count(),
            'list' => $list->toArray(),
        ];
        Response::json(20000,$data);
    }

    /**
    * 创建一条数据
    * @Apidoc\Desc("创建一条数据")
    * @Apidoc\Method("POST")
    * @Apidoc\Author("xxx")
    * @Apidoc\Tag("创建一条数据")
    * @Apidoc\param("xxx", require=true, type="string", desc="xxx"),
    * @Apidoc\Returned("id", type="string", desc="id"),
    */
    public function create()
    {
        $fields = $this->request->post();
        try {
             $<model> = (new <model>())->create($fields);
             throw_unless($<model>,new \Exception('新增失败'));
        }catch(\Exception $e){
             Response::message($e->getMessage())->json(40000);
        }
        Response::json(20000,['id'=>$<model>->id]);
    }

    /**
    * 获取详情
    * @Apidoc\Desc("获取详情")
    * @Apidoc\Method("POST")
    * @Apidoc\Author("xxx")
    * @Apidoc\Tag("获取详情")
    * @Apidoc\Param("id", type="string", desc="id"),
    * @Apidoc\Returned("id", type="string", desc="id"),
    * @Apidoc\Returned("update_time", type="datetime", desc="更新时间")
    */
    public function read()
    {
        $fields = [<columnFields>];
        $<model> = (new <model>())->whereNotDel()->field($fields)->findOrEmpty($this->request->post('id'));
        if ($<model>->isEmpty()) {
            Response::message('获取详情失败')->json(40000);
        }
        Response::json(20000, $<model>->toArray());
    }

    /**
    * 编辑
    * @Apidoc\Desc("编辑")
    * @Apidoc\Method("POST")
    * @Apidoc\Author("xxx")
    * @Apidoc\Tag("编辑")
    * @Apidoc\param("id", require=true, type="string", desc="id"),
    */
    public function edit()
    {
        $<model> = (new <model>())->whereNotDel()->findOrEmpty($this->request->post('id'));
        if ($<model>->isEmpty()) {
            Response::message('获取详情失败')->json(40000);
        }
        $fields = [<columnFields>];
        $update = [];
        foreach ($fields as $field) {
            if ($this->request->post($field) !== null) {
                $update[Str::snake($field)] = $this->request->post($field);
            }
        }
        if ($update) {
            $res = <model>::update($update, ['id' => $this->request->post('id')]);
            if ($res->getNumRows()) {
                Response::json(20000);
            }
        }
        Response::message('更新失败')->json(40000);
    }

    /**
    * 删除
    * @Apidoc\Desc("删除")
    * @Apidoc\Method("POST")
    * @Apidoc\Author("xxx")
    * @Apidoc\Tag("删除")
    * @Apidoc\Param("id", type="string", require=true, desc="id")
    */
    public function del()
    {
        try {
            $<model> = (new <model>())->whereNotDel()->findOrEmpty($this->request->post('id'));
            throw_if($<model>->isEmpty(), new \Exception('获取失败'));
            $res = $<model>->del();
            throw_unless($res, new \Exception('删除失败'));
        } catch (\Exception $e) {
            Response::message($e->getMessage())->json(40000);
        }
        Response::json(20000);
   }
}
