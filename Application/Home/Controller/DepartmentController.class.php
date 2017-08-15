<?php
namespace Home\Controller;

use Think\Controller\RestController;
class DepartmentController extends RestController{
    
    public function getDept($token){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        $user_id=$userinfo['uid'];
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        $dept=M('xsrb_department')->where("id=$dept_id")->find();
        $this->response(retmsg(0,$dept),'json');
    }
    
    /**
     * 更新部门数据
     * @param unknown $token
     */
    public function updateDept($token){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        $user_id=$userinfo['uid'];
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        $data=file_get_contents("php://input");
        $data=json_decode($data,true);
        $data['updated_by']=$user_id;
        try {
        $ret=M('xsrb_department')->where("id=$dept_id")->save($data);
        }
        catch (\Exception $e){
            echo $e->__toString();
        }      
        if($ret){
            $this->response(retmsg(0),'json');
        }
        else{
            $this->response(retmsg(-1),'json');
        }
    }
}


?>