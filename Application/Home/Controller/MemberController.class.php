<?php
namespace Home\Controller;
use Think\Controller\RestController;
class MemberController extends RestController
{
    protected $allowMethod    = array('get','post');
    public function data_sync()
    {
        //添加用户表

        //$sql="delete from sell_users";
        //M()->execute($sql);
        $url="http://222.209.223.242:50999/rb/getUsers.php";
        $post_param_arr = json_decode(file_get_contents($url),true);
        echo count($post_param_arr);
        for($i=1;$i<=count($post_param_arr);$i++)//count($post_param_arr)
        {
            $id=$post_param_arr[$i]['ID'];
            $serial_number=$post_param_arr[$i]['SERIALNUMBER'];
            $user_id=$post_param_arr[$i]['USERID'];
            $user_name=$post_param_arr[$i]['USERNAME'];
            $ic=$post_param_arr[$i]['IC'];
            $sex=$post_param_arr[$i]['SEX'];
            $department_id=$post_param_arr[$i]['DEPARTMENTID'];
            $password=$post_param_arr[$i]['PASSWORD'];
            $product_base=$post_param_arr[$i]['PRODUCTBASE'];
            //$regist_time=date('Y-m-d H:i:s',strtotime($post_param_arr[$i]['REGISTTIME']));
            $regist_time=$post_param_arr[$i]['REGISTTIME'];
            $login_time=$post_param_arr[$i]['LOGINTIME'];
            $isonline=$post_param_arr[$i]['ISONLINE'];
            $state=$post_param_arr[$i]['STATE'];
            $userorder=addslashes($post_param_arr[$i]['USER_ORDER']);
            $modify_time=$post_param_arr[$i]['MODIFIEDTIME'];
            $erp_user_id=$post_param_arr[$i]['ERP_USER_ID'];
            $erp_dept_code=$post_param_arr[$i]['ERP_DEPT_CODE'];
            $resetpwd=$post_param_arr[$i]['RESETPWD'];
            $gys_code=$post_param_arr[$i]['GYS_CODE'];
            $group_id=$post_param_arr[$i]['GROUP_ID'];
            $audit_type_id=$post_param_arr[$i]['AUDIT_TYPE_ID'];
            $rank_id=$post_param_arr[$i]['RANK_ID'];
            $erp_zwdj=$post_param_arr[$i]['ERP_ZWDJ'];
            $erp_zw=$post_param_arr[$i]['ERP_ZW'];
            $manage_range=addslashes($post_param_arr[$i]['MANAGE_RANGE']);
            $dept_id=$post_param_arr[$i]['DEPTID'];
            $creatuser=$post_param_arr[$i]['CREATEUSER'];
            $moduser=$post_param_arr[$i]['MODUSER'];
            $sql="replace INTO `sell_users`(`id`, `serial_number`, `user_id`, `user_name`, `ic`, `sex`, `department_id`, `password`, `product_base`, `regist_time`, `login_time`, `isonline`, `state`, `userorder`, `modify_time`, `erp_user_id`, `erp_dept_code`, `resetpwd`, `gys_code`, `group_id`, `audit_type_id`, `rank_id`, `erp_zwdj`, `erp_zw`, `manage_range`, `dept_id`, `creatuser`, `moduser`) VALUES ($id,'$serial_number','$user_id','$user_name','$ic','$sex','$department_id','$password','$product_base','$regist_time','$login_time','$isonline','$state','$userorder','$modify_time','$erp_user_id','$erp_dept_code','$resetpwd','$gys_code','$group_id','$audit_type_id','$rank_id','$erp_zwdj','$erp_zw','$manage_range','$dept_id','$creatuser','$moduser')";
            M()->execute($sql);
        }

        //添加日报部门表
        //$sql="delete from xsrb_department";
        //M()->execute($sql);

        $url="http://222.209.223.242:50999/rb/getXsrbDept.php";
        $post_param_arr = json_decode(file_get_contents($url),true);
        echo count($post_param_arr);
        for($i=1;$i<=count($post_param_arr);$i++)
        {
            $id=$post_param_arr[$i]['ID'];
            $dname=$post_param_arr[$i]['DNAME'];
            $pid=$post_param_arr[$i]['PID'];
            $qt1=$post_param_arr[$i]['QT1'];
            $qt2=$post_param_arr[$i]['QT2'];
            $qt3=$post_param_arr[$i]['QT3'];
            $sql="replace INTO `xsrb_department`(`id`, `dname`, `pid`, `qt1`, `qt2`, `qt3`) VALUES ($id,'$dname',$pid,'$qt1','$qt2','$qt3')";
            M()->execute($sql);
        }
        echo "OK";
    }
    //部门信息
    public function data_dept($method='',$check ='')//mod表示修改,del表示删除,ins表示添加
    {
        header("Access-Control-Allow-Origin: *");
// 		$url="http://222.209.223.242:50999/rb/getXsrbDept.php";
        $data = json_decode(file_get_contents("php://input"),true);
        $source = file_get_contents("php://input");
        $date = date("Y-m-d H:i:s");
        $sql_log = "insert into test(c1,c2,c3)value('department','$method.$date','$source')";
        M()->execute($sql_log);
        if($check =='yes')
        {
            $this->response($data,'json');
            return;
        }
        switch ($this->_method)
        {
            case 'post':
            {
                switch ($method)
                {
                    case 'rebind':
                    {
                        $erp_user_id = $data['ERP_USER_ID'];
                        $sql_rebind = "update sell_users set user_id='',password='',modify_time=now(),resetpwd=0,group_id='',audit_type_id='',product_base='',dept_id='' where erp_user_id ='$erp_user_id'";
                        echo $sql_rebind;
                        $re = M()->execute($sql_rebind);
                        if (false !==$re)
                        {
                            $this->response(array('resultcode'=>0,'resultmsg'=>'删除成功'),'json');
                        }else
                        {
                            $this->response(array('resultcode'=>-1,'resultmsg'=>'删除失败!'),'json');
                        }
                    }
                        break;
                    case 'del':        //删除部门
                    {
                        foreach ($data['data']['ID'] as $k1 => $v1)
                        {
                            $check = M()->query("select id from xsrb_department where id =".$v1);
                            if (count($check))
                            {
                                $sql = "delete from xsrb_department where id =".$v1;
                                $result = M()->execute($sql);
                            }else
                            {
                                $this->response(array('resultcode'=>-1,'resultmsg'=>'没有id为'.$v1.'的部门'),'json');
                            }
                        }
                        $this->response(array('resultcode'=>0,'resultmsg'=>'删除成功'),'json');
                    }
                        break;
                    case 'mod':        //修改部门信息
                    {
                        foreach ($data as $k2 => $v2)
                        {
                            $check = M()->query("select id from xsrb_department where id =".$v2['ID']);
                            if (count($check))
                            {
                                foreach ($v2 as $k21 => $v21)
                                {
                                    if ($v21 !='null')
                                    {
                                        if (!is_numeric($v21))
                                        {
                                            $v21 = "'".$v21."'";
                                        }
                                        $str .= $k21.'='.$v21.',';
                                    }
                                }
                                $str = rtrim($str,',');
                                $sql = 'update xsrb_department set '.$str.' where id ='.$v2['ID'];
                                $result = M()->execute($sql);
                            }else
                            {
                                $this->response(array('resultcode'=>-1,'resultmsg'=>'没有id为'.$v2['ID'].'的部门'),'json');
                            }
                        }
                        $this->response(array('resultcode'=>0,'resultmsg'=>'修改成功'),'json');
                    }
                        break;
                    case 'ins':       //添加部门信息
                    {
                        foreach ($data['data'] as $k3 => $v3)
                        {
                            $check = M()->query("select id from xsrb_department where id =".$v3['ID']);
                            if (!count($check))
                            {
                                foreach ($v3 as $kv3 => $vv3)
                                {
                                    if ($vv3 =='null')
                                    {
                                        $v3[$kv3] = '';
                                    }
                                }
                                $sql="insert into `xsrb_department`(`id`, `dname`, `pid`, `qt1`, `qt2`, `qt3`) VALUES (".$v3['ID'].",'".$v3['DNAME']."',".$v3['PID'].",'".$v3['QT1']."','".$v3['QT2']."','".$v3['QT3']."')";
                                $result = M()->execute($sql);
                            }else
                            {
                                $this->response(array('resultcode'=>-1,'resultmsg'=>'已添加id为'.$v3['ID'].'的部门'),'json');
                            }
                        }
                        if ($result >0)
                        {
                            $this->response(array('resultcode'=>0,'resultmsg'=>'添加成功'),'json');
                        }
                    }
                        break;
                }
            }
        }
    }
    //用户信息
    public function data_user($method='',$check ='')//mod表示修改,del表示删除,ins表示添加
    {
        //添加用户表
        header("Access-Control-Allow-Origin: *");
// 		$url="http://222.209.223.242:50999/rb/getUsers.php";
        $source = str_replace("'","",file_get_contents("php://input"));
        $data = json_decode($source,true);
        $date = date("Y-m-d H:i:s");
        $sql_log = "insert into test(c1,c2,c3)value('user','$method.$date','$source')";
        M()->execute($sql_log);
        if($check =='yes')
        {
            echo 1111122333;
            $this->response($data,'json');
            return;
        }
        switch ($this->_method)
        {
            case 'post':
            {
                switch ($method)
                {
                    case 'rebind':
                    {
                        $erp_user_id = $data['data']['ERP_USER_ID'];
                        $serial_number =$data['data']['SERIALNUMBER'];
                        $sql_rebind = "update sell_users set serial_number=$serial_number, user_id='',password='',modify_time=now(),resetpwd=0,group_id='',audit_type_id='',product_base='',dept_id='' where erp_user_id ='$erp_user_id'";
                        $re = M()->execute($sql_rebind);
                        if (false !==$re)
                        {
                            $this->response(array('resultcode'=>0,'resultmsg'=>'删除成功'),'json');
                        }else
                        {
                            $this->response(array('resultcode'=>-1,'resultmsg'=>'删除失败'),'json');
                        }
                    }
                        break;
                    case 'del':     //删除用户
                    {
                        foreach ($data as $k1 => $v1)
                        {
                            $check = M()->query("select erp_user_id from sell_users where id=".$v1['ID']);
                            if (count($check))
                            {
                                $sql = "update sell_users set user_id='',password='',regist_time='',modify_time='',resetpwd='',group_id='',audit_type_id='',product_base='',dept_id='' where id =".$v1['ID'];
                                $result = M()->execute($sql);
                            }else
                            {
                                $this->response(array('resultcode'=>-1,'resultmsg'=>'没有id为'.$v1['ID'].'的用户'),'json');
                            }
                        }
                        $this->response(array('resultcode'=>0,'resultmsg'=>'删除成功'),'json');
                    }
                        break;
                    case 'mod':     //修改用户信息
                    {
                        foreach ($data as $k2 => $v2)
                        {
                            if ($v2['ERP_USER_ID'] !='' && $v2['ERP_USER_ID'] != 'null')
                            {
                                $key = 'erp_user_id';
                                $val = "'".$v2['ERP_USER_ID']."'";
                            }else
                            {
                                $key = 'serial_number';
                                $val = $v2['SERIALNUMBER'];
                            }

                            if ($v2['PASSWORD'] !='null' && $v2['USERID'] !='null')     //修改密码
                            {
                                $key = 'user_id';
                                $val = "'".$v2['USERID']."'";
                            }

                            foreach ($v2 as $k21 => $v21)
                            {
                                if ($k21 =='SERIALNUMBER')  $k21 = 'serial_number';
                                if ($k21 =='USERNAME')      $k21 = 'user_name';
                                if ($k21 =='USERID')        $k21 ='user_id';
                                if ($k21 =='DEPARTMENTID')  $k21 ='department_id';
                                if ($k21 =='PRODUCTBASE')   $k21 ='product_base';
                                if ($k21 =='REGISTTIME')    $k21 ='regist_time';
                                if ($k21 =='LOGINTIME')     $k21 ='login_time';
                                if ($k21 =='MODIFIEDTIME')  $k21 ='modify_time';
                                if ($k21 =='DEPTID')        $k21 ='dept_id';
                                if ($k21 =='USER_ORDER')    $k21 = 'userorder';
                                if ($k21 =='CREATEUSER')    $k21 = 'creatuser';

                                if ($v21 !='null')
                                {
                                    if (!is_numeric($v21))
                                    {
                                        $v21 = "'".$v21."'";
                                    }
                                    elseif ($k21 =='ERP_USER_ID')
                                    {
                                        $v21 = "'".$v21."'";
                                    }
                                    elseif($k21 =='regist_time' || $k21 =='login_time' || $k21 =='modify_time')
                                    {
                                        $v21 ="'".date('Y-m-d H:i:s',strtotime($v21))."'";
                                    }else
                                    {
                                        $v21 = "'".$v21."'";
                                    }
                                    $str .= strtolower($k21).'='.$v21.',';
                                }
                            }
                            $str = rtrim($str,',');
                            $sql = 'update sell_users set '.$str.' where '.$key.'='.$val;
                            M()->execute($sql);
                        }
                        $this->response(array('resultcode'=>0,'resultmsg'=>'修改成功'),'json');
                    }
                        break;
                    case 'ins':     //添加用户
                    {
                        foreach ($data['data'] as $k1 =>$v1)
                        {
                            $check = M()->query("select id from sell_users where erp_user_id='".$v1['ERP_USER_ID']."'");
                            if (!count($check))
                            {
                                $sql_ins = "insert into sell_users(`id`,serial_number,user_id,user_name,password,regist_time,erp_user_id,dept_id,creatuser)
                                            VALUES('".$v1['SERIALNUMBER']."','".$v1['SERIALNUMBER']."','".$v1['USERID']."','".$v1['USERNAME']."','".$v1['PASSWORD']."',now(),'".$v1['ERP_USER_ID']."','".$v1['DEPTID']."','".$v1['CREATEUSER']."') ";
                                M()->execute($sql_ins);
                            }
                            foreach ($v1 as $k21 => $v21)
                            {
                                if ($k21 =='SERIALNUMBER')  $k21 = 'serial_number';
                                if ($k21 =='USERNAME')      $k21 = 'user_name';
                                if ($k21 =='USERID')        $k21 ='user_id';
                                if ($k21 =='DEPARTMENTID')  $k21 ='department_id';
                                if ($k21 =='PRODUCTBASE')   $k21 ='product_base';
                                if ($k21 =='REGISTTIME')    $k21 ='regist_time';
                                if ($k21 =='LOGINTIME')     $k21 ='login_time';
                                if ($k21 =='MODIFIEDTIME')  $k21 ='modify_time';
                                if ($k21 =='DEPTID')        $k21 ='dept_id';
                                if ($k21 =='USER_ORDER')    $k21 = 'userorder';
                                if ($k21 =='CREATEUSER')    $k21 = 'creatuser';

                                if ($v21 !='null')
                                {
                                    if ($k21 =='regist_time' || $k21 =='login_time' || $k21 =='modify_time')
                                    {
                                        $v21 ="'".date('Y-m-d H:i:s')."'";
                                    }
                                    elseif ($k21 =='ERP_USER_ID')
                                    {
                                        $v21 = "'".$v21."'";
                                    }
                                    elseif(!is_numeric($v21))
                                    {
                                        $v21 = "'".$v21."'";
                                    }
                                    else
                                    {
                                        $v21 = "'".$v21."'";
                                    }
                                    $str .= strtolower($k21).'='.$v21.',';
                                }
                            }
                            $str = rtrim($str,',');
                            $sql = "update sell_users set ".$str." where erp_user_id ='".$v1['ERP_USER_ID']."'";
                            M()->execute($sql);
                            /*                }else
                                           {
                                               $this->response(array('resultcode'=>-1,'resultmsg'=>'没有erp_user_id为'.$v1['ERP_USER_ID'].'的用户'),'json');
                                           } */
                        }

                        $this->response(array('resultcode'=>0,'resultmsg'=>'修改成功'),'json');
                    }
                        break;
                }
                break;
            }
        }
    }
    //用户权限
    public function data_auth($mothed='')//put表示修改,del表示删除,''表示添加
    {
        //添加日报部门表
        $sql="delete from xsrb_department";
        M()->execute($sql);
        $url="http://222.209.223.242:50999/rb/getXsrbDept.php";
        $post_param_arr = json_decode(file_get_contents($url),true);
        echo count($post_param_arr);
        for($i=1;$i<=count($post_param_arr);$i++)
        {
            $id=$post_param_arr[$i]['ID'];
            $dname=$post_param_arr[$i]['DNAME'];
            $pid=$post_param_arr[$i]['PID'];
            $qt1=$post_param_arr[$i]['QT1'];
            $qt2=$post_param_arr[$i]['QT2'];
            $qt3=$post_param_arr[$i]['QT3'];
            $sql="INSERT INTO `xsrb_department`(`id`, `dname`, `pid`, `qt1`, `qt2`, `qt3`) VALUES ($id,'$dname',$pid,'$qt1','$qt2','$qt3')";
            M()->execute($sql);
        }
        echo "OK";
    }
    //用户登录
    public function member_login($ckey='',$staffOaid='')
    {
        //$Model = new \Think\Model();
        header("Access-Control-Allow-Origin: *");

        $index = "http://".$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].'/xsrb/main.html';

        switch($this->_method)
        {
            case 'get':
            {
                if ($ckey !='' && $staffOaid !='')
                {
                    $str = 'http://www.yjf.com/';
                    $ckey1 = md5(md5($staffOaid.$str));
                    if ($ckey == $ckey1)
                    {
                        $sql="select count(*) as cnt,user_id ,user_name,dept_id,(select pid from xsrb_department where xsrb_department.id=sell_users.dept_id) as  pid,(select qt1 from xsrb_department where xsrb_department.id=sell_users.dept_id) as  qt1  from sell_users where erp_user_id='".$staffOaid."'  and dept_id in (select id from xsrb_department)";
                        if($list = M()->query($sql))
                        {
                            if($list[0]['cnt']!=0)
                            {
                                $dept_id=$list[0]['dept_id'];
                                $user_name=$list[0]['user_name'];
                                $pid=$list[0]['pid'];
                                $qt1=$list[0]['qt1'];
                                $now_time = time();
                                $nowtime = date("Y-m-d H:i:s",$now_time);
                                import("Xsrb.Token");
                                $token_obj = new \Token();
                                $token = $token_obj->GetToken(C('CACHE_NAME'), C('Cache_TimeOut_Token'), array(
                                    "dept_id" => safe($dept_id),
                                    "phone" => safe($phone),
                                    "uid" => $list[0]['user_id'],
                                    "qt1" => $qt1,
                                    "user_name" => $user_name,
                                    "pid" => $pid,
                                    "logintime" => $nowtime,
                                    "ver" => $ver,
                                    // "mac"=>safe($mac),
                                    "application" => safe($application)
                                ));
                                $sql="update sell_users set token='$token',login_time=now(),xmmc ='jump_login' where erp_user_id='".$staffOaid."'";
                                M()->execute($sql);
                                header("Location: $index?token=$token&&qt1=$qt1");
                                return;
                                //$this->response(retmsg(0,array("token"=>$token,"dept_id"=>$dept_id,"user_name"=>$user_name,"uid"=>$id,"qt1"=>$qt1)),'json');
                            }
                            else
                            {
                                $this->response(retmsg(-1),'json');
                            }
                        }
                    }
                    else
                    {
                        $this->response(retmsg(-1),'json');
                    }
                }
                else
                {
                    $this->response(retmsg(-1),'json');
                }
                break;
            }
            case "post":
            {
                $post_param_arr = json_decode(file_get_contents("php://input"),true);
                //$post_param_arr = json_decode(base64_decode(urldecode(file_get_contents("php://input"))),true);
                $data=$post_param_arr["data"];
                $pwd= md5($data["pwd"]);
                $id=$data["id"];
                $sql="select count(*) as cnt,user_name,dept_id,(select pid from xsrb_department where xsrb_department.id=sell_users.dept_id) as  pid,(select qt1 from xsrb_department where xsrb_department.id=sell_users.dept_id) as  qt1  from sell_users where user_id='".$id."' and password='".$pwd."' and dept_id in (select id from xsrb_department)";
                //echo $sql;return;
                if($list = M()->query($sql))
                {
                    if($list[0]['cnt']!=0)
                    {
                        $dept_id=$list[0]['dept_id'];
                        $user_name=$list[0]['user_name'];
                        $pid=$list[0]['pid'];
                        $qt1=$list[0]['qt1'];
                        $now_time = time();
                        $nowtime = date("Y-m-d H:i:s",$now_time);
                        import("Xsrb.Token");
                        $token_obj = new \Token();
                        $token = $token_obj->GetToken(C('CACHE_NAME'), C('Cache_TimeOut_Token'), array(
                            "dept_id" => safe($dept_id),
                            "phone" => safe($phone),
                            "uid" => $id,
                            "qt1" => $qt1,
                            "user_name" => $user_name,
                            "pid" => $pid,
                            "logintime" => $nowtime,
                            "ver" => $ver,
                            // "mac"=>safe($mac),
                            "application" => safe($application)
                        ));
                        $sql="update sell_users set token='$token',login_time=now(),xmmc='mima_login' where user_id='".$id."'";
                        M()->execute($sql);
                        $this->response(retmsg(0,array("token"=>$token,"dept_id"=>$dept_id,"user_name"=>$user_name,"uid"=>$id,"qt1"=>$qt1)),'json');
                    }
                    else
                    {
                        $this->response(retmsg(-9),'json');
                    }

                }
                else
                {
                    $this->response(retmsg(-1),'json');
                }
                break;
            }
        }
    }
    public function testtoken($token)
    {

        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id=$userinfo['dept_id'];
        echo $dept_id;
    }

    /**
     * 获取用户信息
     * @param  $token
     * @return $userinfo
     */
    public function getUserInfo($token){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        //添加测试部门标示
        $test=0;//是否为测试部门标示
        $sql="select * from xsrb_test_dept where id=".$userinfo['dept_id'];
        $ret=M()->query($sql);
        if(!empty($ret)){
            $test=1;
        }
        $userinfo['test']=$test;
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $this->response(retmsg(0,$userinfo),'json');
    }

}
