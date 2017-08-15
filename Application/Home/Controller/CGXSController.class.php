<?php
namespace Home\Controller;

use Think\Controller\RestController;

class CGXSController extends RestController
{
    public function xsrbDepartment($dept=''){
        header("Access-Control-Allow-Origin: *");
        if (empty($dept)){
            //所有片区
            $sql = "select id as dept,dname as department from xsrb_department where  qt1 =0";
            $result = M()->query($sql);
            $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$result),'json');
        }else{
            if (!is_numeric($dept))
                $this->response(array('resultcode'=>-1,'resultmsg'=>'部门id不正确!'),'json');
            $sql = "select id from xsrb_department where  qt1 =0";
            $pianqu = M()->query($sql);
			
            foreach($pianqu as $v){
                $pianqu_id[] = $v['id'];
            }
            if (in_array($dept,$pianqu_id)){
                //区域的查询
                $where = " where pid = $dept";
            }else
				//经营部查询
                $where = " where id = $dept";
            $sql = "select id as dept,dname as department,pid,(select dname from xsrb_department where id =a.pid) as pname from xsrb_department as a $where";
            $result = M()->query($sql);
            if (!count($result))
                $this->response(array('resultcode'=>-1,'resultmsg'=>'没有相关经营部'),'json');
            $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$result),'json');
        }
    }
	
	public function xsrbDept($department = null){
        header("Access-Control-Allow-Origin: *");
        if (empty($department)) {
            //所有部门
        }else{
			$department = excel_trim2(trim($department,"'"));
            $where  = " and dname like '%$department%'";
        }
        $sql = "select id,dname from xsrb_department where pid != 1 and qt1 !=0 and id !=1 $where order by convert(dname using gbk)";
        $result = M()->query($sql);
        if (!count($result))
            $this->response(array('resultcode'=>-1,'resultmsg'=>'没有相关经营部'),'json');
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$result),'json');
    }
}