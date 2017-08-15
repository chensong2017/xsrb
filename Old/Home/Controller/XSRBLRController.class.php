<?php
namespace Home\Controller;
use Think\Controller\RestController;
//销售日报录入
class XSRBLRController extends RestController{
    
    //销售日报表提交
    public function submit($js='',$token="",$type ='',$sub='',$date='',$iid=''){
       header("Access-Control-Allow-Origin: *");
      
        if ($type =='')
        {
            $jsonData=json_decode(file_get_contents("php://input"),true); 
        }
        if(empty($jsonData))
            $jsonData=json_decode($js,true);
        $data=$jsonData['data'];
        
        if($date =='')
        {
            $now=TODAY;
        }else 
        {
            $now = $date;
        }
        $dept = $iid;
        
        if ($sub=='')
        {
            //验证token
           $userinfo = checktoken($token);
           if(!$userinfo)
           {
               $this->response(retmsg(-2),'json');
               return;
           }
            $dept=$userinfo['dept_id'];
            //解析json 非明细数据存入mysql
            $sql="replace into xsrblr(fdm,sdj,dmb,type,type_detail,date,dept) values ";
            foreach($data as $keytr=>$tr){
                 
                $tr=$tr['tr'];
                //现金业务、商品业务、应收款录入数据入库
                $val=trim($tr[0]["value"]);
				if($val =='')
				{
					echo '{"resultcode":-1,"resultmsg":"保存失败，数据库发生了未知错误"}';
					exit();
				}
                if(($val=="现金业务"||$val=="商品业务"||$val=="应收款")&&count($tr)>4){
                    $sql.="(";
                    foreach($tr as $keytd=>$td){
                        $product=trim($td["product"]);
                        $type=trim($td["type"]);
                        $type_detail=trim($td["type_detail"]);
                        $value=trim($td["value"]);
            
                        if(is_numeric($value)){
                            $sql.="$value,";
                        }
                    }
            
                    $sql.="'$type','$type_detail','$now','$dept'),";
                }
            }
            
            $sql=substr($sql,0,strlen($sql)-1);
            $ret=M()->execute($sql);
            if(!$ret){
                echo '{"resultcode":1,"resultmsg":"保存失败，Mysql数据库发生了未知错误"}';
                return;
            }
            
            
            //明细数据入库
            //开启事务
            //M()->startTrans();
            //删除当天某部门每张明细表的数据，确保每次提交的数据都是更新后的数据
            $sql="delete from qtsrmx where depart_id='$dept' and date='$now'";
            M()->execute($sql);
            $sql="delete from hwdbmx where dept='$dept' and date='$now'";
            M()->execute($sql);
            $sql="delete from fymx where dept='$dept' and date='$now'";
            M()->execute($sql);
            $sql="delete from zjdbmx where depart_id='$dept' and createtime='$now'";
            M()->execute($sql);
            $sql="delete from yszkmx where depart_id='$dept' and createtime='$now'";
            M()->execute($sql);
            $sql="delete from qtmx where dept='$dept' and date='$now'";
            M()->execute($sql);
            foreach($data as $keytr=>$tr){
                $tr=$tr['tr'];
                foreach($tr as $keytd=>$td){
                    $value=trim($td["value"]);
                    //如果存在明细单元格则遍历child明细数据
                    if(array_key_exists("child",$td)){
                        //明细数据
                        $child_data=$td['child']['child_data'];
                         
                        foreach ($child_data as $k=>$v){
                            //跳过明细表头标题
                            if($k==0)
                                continue;
                                //公有参数
                                $sdj=is_numeric($v['genera'])?$v['genera']:0;//三代机
                                $fdm=is_numeric($v['door'])?$v['door']:0;//防盗门
                                $dmb=is_numeric($v['ground_wave'])?$v['ground_wave']:0;//地面波
                                 
                                //其他收入明细
                                if($value=="其他收入"){
                                    $xm=$v['project'];//项目
                                    $sql="insert into qtsrmx(depart_id,xm,sdj,fdm,dmb,date,xmmc) values
                                    ('$dept','$xm',$sdj,$fdm,$dmb,'$now','其他收入')";
                                }
                                //货物调拨明细
                                elseif($value=="外购入库"||$value=="调拨收入"||$value=="调拨支出"){
                                    //项目类型
                                    $xmlb=$td['type_detail'];
                                    $xmlb=mb_substr($xmlb,0,4,'utf-8');//截取类型的前四个字
                                    $qtbm=$v['otherpartment'];//其他部门
                                    $sql="insert into hwdbmx(dept,xmlb,xmmc,qtbm,sdj,fdm,dmb,date) values
                                    ('$dept','$xmlb','$value','$qtbm',$sdj,$fdm,$dmb,'$now')";
                                }
                                //费用明细
                                elseif($value=="经营费用"){
                                    $xmlb=$v['projectclass'];
                                    $xmmc=$v['projectname'];
                                    $sql="insert into fymx(dept,xmlb,xmmc,sdj,fdm,dmb,date) values
                                    ('$dept','$xmlb','$xmmc',$sdj,$fdm,$dmb,'$now')";
                                }
                                //资金调拨明细
                                elseif($value=="资金调成总"){
                                    $dfbm=$v['otherdepartment'];//对方部门
                                    $hm=$v['accountname'];//户名
                                    $khh=$v['accountbank'];//开户行
                                    $sum=is_numeric($v['amount'])?$v['amount']:0;//金额
                                    $sql="insert into zjdbmx(depart_id,in_depart,accountname,accountbank,amount,createtime)
                                    values('$dept','$dfbm','$hm','$khh',$sum,'$now')";
                                }
                                //应收款明细
                                elseif($value=="收回欠款"){
                                    $khmc=$v['customname'];//客户名称
                                    $class=$v['class'];//类别
                                    $xzqk=is_numeric($v['newarrear'])?$v['newarrear']:0;//新增欠款
                                    $shqk=is_numeric($v['recoverarrear'])?$v['recoverarrear']:0;//收回欠款
                                    switch ($class){
                                        case 0:
                                            $type='防盗门';
                                            break;
                                        case 1:
                                            $type='三代机';
                                            break;
                                        case 2:
                                            $type='地面波';
                                            break;
            
                                    }
                                    $sql="insert into yszkmx(depart_id,khmc,type,increase,takeback,createtime) values
                                    ('$dept','$khmc','$type',$xzqk,$shqk,'$now')";
                                }
                                //其他明细
                                elseif($value=="经营部资金调入"||$value=="代收款"||$value=="代支采购货款"||$value=="代支其他部门"){
                                    //项目类型
                                    $xmlb=$td['type_detail'];
                                    $xmlb=mb_substr($xmlb,0,4,'utf-8');//截取类型的前四个字
                                    $xm=$v['project'];//项目
                                    $sum=is_numeric($v['amount'])?$v['amount']:0;//金额
                                    $xmlx = mb_substr($td['type_detail'],7,10,'utf-8');	//存入其他收入明细的项目类型
                                    $sql="insert into qtmx(dept,xmlb,xmmc,xm,sum,date,xmlx) values
                                    ('$dept','$xmlb','$xmmc','$xm',$sum,'$now','$xmlx')";
                                }
                                $tempRet=M()->execute($sql);
                                /*                             if(!$tempRet){
                                 //插入失败，回滚删除的明细数据
                                 M()->rollback();
                                 echo '{"resultcode":1,"resultmsg":"保存失败，Mysql数据库发生了未知错误"}';
                                 return;
                                 } */
                        }
                        //插入成功则提交事务
                        //M()->commit();
                    }
                }
            }
        }
        

         
        
        //计算并更新数据
        //无期初数据计算出问题！！！sql 返回 null
        foreach($data as $keytr=>$tr){
            $tr=$tr['tr'];
                foreach($tr as $keytd=>$td){
                    $product=trim($td["product"]);
                    $type=trim($td["type"]);
                    $type_detail=trim($td["type_detail"]);
                    $value=trim($td["value"]);
                    $sql="";
                    if($product=="防盗门")
                        $p="fdm";
                    elseif($product=="三代机")
                        $p="sdj";
                    elseif($product=="地面波"){
                        $p="dmb";
                    }
                    if($type_detail=="现金结存现金结存"&&$keytd ==3)
                        $sql="call count_xsrblr_xjjc('$dept','$now')";
                   elseif($type_detail=="应收款结存"&&is_numeric($value))
                             $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-(select ifnull((SELECT sum($p) from xsrblr where type_detail='资产类现金收入收回欠款'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+(select ifnull((SELECT $p from xsrbqc  where type_detail='应收款结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value;";
                   elseif($type_detail=="预收账款结存"&&is_numeric($value))
                   $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='资产类现金收入增加预收款'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-	(select ifnull((SELECT sum($p) from xsrblr where type_detail='资产类现金支出减少预收款'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+(select ifnull((SELECT $p from xsrbqc  where type_detail='预收账款结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   elseif($type_detail=="暂存款结存"&&is_numeric($value))
                   $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='资产类现金收入增加暂存款'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-(select ifnull((SELECT sum($p) from xsrblr where type_detail='资产类现金支出减少暂存款'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+	(select ifnull((SELECT $p from xsrbqc  where type_detail='暂存款结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   elseif($type_detail=="当月销售收入累计"&&is_numeric($value))
                   $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'),0) as value)+	(select ifnull((SELECT sum($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'),0) as value)) as value";
                   
                   elseif ($type_detail =='当月销售成本累计'&&is_numeric($value))
                   $sql="SELECT IFNULL((SELECT sum($p) as value FROM xsrblr WHERE type_detail='有效支出销售成本' and dept='$dept' AND DATE between date_add('$now', interval - day('$now') + 1 day) and '$now'),0) AS value";
                   
                   elseif($type_detail=="当月毛利累计"&&is_numeric($value))
                   $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'),0) as value)+	(select ifnull((SELECT sum($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'),0) as value)-	(select ifnull((SELECT sum($p) FROM xsrblr WHERE type_detail='有效支出销售成本' and dept='$dept' AND DATE between date_add('$now', interval - day('$now') + 1 day) and '$now'),0) as value)) as value";
                   elseif(($type_detail=="当日销售收入")&&is_numeric($value))
                   $sql="select ((select ifnull((SELECT ($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date='$now'),0) as value)+(select ifnull((SELECT ($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date='$now'),0) as value)) as value";
                   //利率计算没有计算成本
                   elseif($type_detail=="当月毛利率" && $keytd !=0)
                   $sql="select ifnull( concat(round(
                   ((
                   SELECT sum($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'
                   )
                   +(
                   SELECT sum($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'
                   ) 
                   -(
                   SELECT sum($p) FROM xsrblr WHERE type_detail='有效支出销售成本' and dept='$dept' AND DATE between date_add('$now', interval - day('$now') + 1 day) and '$now'
                   ))/
                   ((
                   SELECT sum($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'
                   )
                   +(
                   SELECT sum($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day) and '$now'
                   ))*100,2),'%'),'∞') as value
                   ";
                   
                   elseif ($type_detail == '当日销售成本' && is_numeric($value))
                   $sql = "SELECT IFNULL((SELECT ($p) as value FROM xsrblr WHERE type_detail='有效支出销售成本' and dept='$dept' AND DATE='$now'),0) AS value";
                   
                   elseif($type_detail == '当日毛利' && is_numeric($value))
                   $sql="select ((select ifnull((SELECT ($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date='$now'),0) as value)+
		(select ifnull((SELECT ($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date='$now'),0) as value)-
		(select ifnull((SELECT ($p) as value FROM xsrblr WHERE type_detail='有效支出销售成本' and dept='$dept' AND DATE='$now'),0) as value))as value";
                   
                   elseif($type_detail=="当日毛利率" && $keytd !=0)
                   $sql="select ifnull( concat(round(
                   ((
                   SELECT ($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date='$now'
                   )
                   +(
                   SELECT ($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date='$now'
                   )
                   -(
                   SELECT ($p) as value FROM xsrblr WHERE type_detail='有效支出销售成本' and dept='$dept' AND DATE='$now'
                   ))/
                   ((
                   SELECT ($p) from xsrblr where type_detail='应收款新增'   and dept='$dept' and date='$now'
                   )
                   +(
                   SELECT ($p) from xsrblr where type_detail='损益类现金收入当日销现'   and dept='$dept' and date='$now'
                   ))*100,2),'%'),'∞') as value";
                   
                   elseif($type_detail=="有效结存"&&is_numeric($value))
                   $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail like '有效收入%'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-	(select ifnull((SELECT sum($p) from xsrblr where type_detail like '有效支出%'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+	(select ifnull((SELECT $p from xsrbqc  where type_detail='有效结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   
                   elseif($type_detail=="送货结存"&&is_numeric($value))
                    $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='有效支出送货支出'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-	(select ifnull((SELECT sum($p) from xsrblr where type_detail='有效收入送货收回'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+(select ifnull((SELECT $p from xsrbqc  where type_detail='送货结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   
                   elseif($type_detail=="无效结存"&&is_numeric($value))
                     $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail like '无效收入%'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-(select ifnull((SELECT sum($p) from xsrblr where type_detail like '无效支出%'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+	(select ifnull((SELECT $p from xsrbqc  where type_detail='无效结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   //不区分有效无效
                   elseif($type_detail=="暂存商品结存"&&is_numeric($value))
                       $sql="select ((select ifnull((SELECT sum($p) from xsrblr where (type_detail='有效收入增加暂存商品'  or type_detail='无效收入增加暂存商品' )  and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-	(select ifnull((SELECT sum($p) from xsrblr where (type_detail='有效支出减少暂存商品'  or type_detail='无效支出减少暂存商品' )and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+(select ifnull((SELECT $p from xsrbqc  where type_detail='暂存商品结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   
                   elseif($type_detail=="铺货结存"&&is_numeric($value))
                    $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='有效支出增加铺货商品'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-	(select ifnull((SELECT sum($p) from xsrblr where type_detail='有效收入减少铺货商品'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+(select ifnull((SELECT $p from xsrbqc  where type_detail='铺货结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value	";
                   
                   elseif($type_detail=="待处理商品结存"&&is_numeric($value))
                    $sql="select ((select ifnull((SELECT sum($p) from xsrblr where type_detail='有效支出增加待处理商品'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)-(select ifnull((SELECT sum($p) from xsrblr where type_detail='有效收入减少待处理商品'   and dept='$dept' and date BETWEEN  date_add('$now', interval - day('$now') + 1 day)  and '$now'),0) as value)+(select ifnull((SELECT $p from xsrbqc  where type_detail='待处理商品结存'   and dept='$dept' and date=DATE_FORMAT('$now','%Y-%m')),0) as value)) as value";
                   
                   if($sql!=""){
                       $temp=M()->query($sql);
                       $temp=$temp[0]['value'];
/*                        if(($type_detail=="当月毛利率"||$type_detail=="当日毛利率")&&$temp>0)
                           $temp='100%'; */
                               
                       $data[$keytr]['tr'][$keytd]['value']=$temp;
                      
                   }
                }
            }
        $jsonData['data']=$data;
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $result=$redis->set("report-$dept-$now-XSRBLR",json_encode($jsonData));
     if($js==''){
        if($result)
            echo '{"resultcode":0,"resultmsg":"保存成功"}';
            else
                echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
        }
       
    }
    
    //销售日报查询
 function search($token=""){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
       $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379"); 
        $redis->auth(C('REDIS_PWD'));
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);
      $now=TODAY;
        $dept=$userinfo['dept_id'];
        $redis->set("report-$dept-XSRBLR",$js);
       $json=$redis->get("report-$dept-$now-XSRBLR"); 
    //今天没有填报过数据就计算模板（模板加结存）

		if($json!=null)
            echo $json;
		else
		{   
			$this->submit($js,$token);
			$js=$redis->get("report-$dept-$now-XSRBLR");
		    echo $js;
		}
   
 
    }
    
    public function test(){
       
    $now_hour=date("H");
    if((int)$now_hour<4)
        define("TODAY",date("Ymd",strtotime("-1 day")));
    else
        define("TODAY",date("Ymd"));
        
    }
    
    public function test1($date='',$dept='',$type ='',$biao='')
	{
		$redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379"); 
        $redis->auth(C('REDIS_PWD'));
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/$biao.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);
		if($type =='set')
		{
	        $redis->set("report-$dept-$date-$biao",$js);
			$json=$redis->get("report-$dept-$date-$biao"); 
			echo $json;
		}
		if($type =='get')
		{
			$json=$redis->get("report-$dept-$date-$biao"); 
			echo $json;
		}

    }
  
}


?>