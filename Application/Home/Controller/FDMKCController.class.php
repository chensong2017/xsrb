<?php
namespace Home\Controller;

use Think\Controller\RestController;

/**
 * 防盗门库存表
 * @author Administrator
 *
 */
class FDMKCController extends RestController{
         
    /**
     * 防盗门库存表提交和计算
     * 解析json 基础数据插入mysql
     * 计算数据
     * 计算完后的json存入redis
     * @param json
     * @return json
     */
    public function submit($js='',$token="",$type =''){
        header("Access-Control-Allow-Origin: *");
		//echo "jinruhanshu";
        //验证token
        $userinfo = checktoken($token);
		
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        
        if ($type =='')
        {
            $jsonData=json_decode(file_get_contents("php://input"),true); 
        }
        
        if(empty($jsonData))
            $jsonData=json_decode($js,true);
        
        $data=$jsonData['data'];
        //redis时间键
        $day=TODAY;
        $month=date("Ym");
        //部门
        $dept=$userinfo['dept_id'];
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
          
        //解析json 数据存入mysql
        $sql="replace into fdmkcb(dbsr,qtsr,cdsc,qhsc,wgm,bfzc,dbzc,qtzc,
            yxjc,yxzr,wxbfzc,wxqtzc,wxjc,product,dept,date) values ";
       
        foreach($data as $keytr=>$tr){
            
            $tr=$tr['tr'];
            //表头和合计行数据不入库
            if($keytr>3){
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
                $sql.="'$product','$dept',$day),";
            }
        }
        
        $sql=substr($sql,0,strlen($sql)-1);
        M()->execute($sql);
        $sql="update fdmkcb set yxjc=dbsr+qtsr-cdsc-qhsc-wgm-bfzc-dbzc-qtzc,
        wxjc=yxzr-wxbfzc-wxqtzc where date='$day' and dept='$dept'";
        M()->execute($sql);
        
        //计算并更新表格数据
        foreach($data as $keytr=>$tr){
           $tr=$tr['tr'];
           foreach($tr as $keytd=>$td){
               $product=trim($td["product"]);
               $type=trim($td["type"]);
               $type_detail=trim($td["type_detail"]);
               $value=trim($td["value"]);
               $sql="";
               $p1="";//动态sql 参数列名
               $p2="";//动态sql 参数product 或合计标记
               //计算结存标志
               $flag=0;
               
               //第三行合计计算
               if($keytr==3)
                 $p2="合计";
                else 
                  $p2=$product;
                
               if($type_detail=="调拨收入")
                   $p1='dbsr';
               elseif($type_detail=="其他收入")
                   $p1='qtsr';
               elseif($type_detail=="成都生产")
                   $p1='cdsc';
               elseif($type_detail=="齐河生产")
                   $p1='qhsc';
               elseif($type_detail=="外购门")
                   $p1='wgm';
               elseif($type_detail=="报废支出"&&$type=="有效收支")
                   $p1='bfzc';
               elseif($type_detail=="调拨支出")
                   $p1='dbzc';
               elseif($type_detail=="其他支出"&&$type=="有效收支")
                   $p1='qtzc';
               elseif($type_detail=="有效结存"){
                   $p1='yxjc';
                   $flag=1;
               }
               elseif($type_detail=="有效转入")
                   $p1='yxzr';
               elseif($type_detail=="报废支出"&&$type=="无效收支")
                    $p1='wxbfzc';
               elseif($type_detail=="其他支出"&&$type=="无效收支")
                    $p1='wxqtzc';
               elseif($type_detail=="无效结存"){
                     $p1='wxjc';
                     $flag=1;
               }
           
                if($p1!=""&&$p2!==""){
              
                    //计算合计行数据
                    if($p2=='合计'){
                        //计算非结存数据
                        if(!$flag){
                            $sql="select sum($p1) as value from fdmkcb where date='$day' and dept='$dept' ";
                        }
                        //计算结存数据
                        else{
                            $sql=" select (
                                select sum($p1)  from fdmkcb where  date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day' and dept='$dept' 
                                )+ifnull((
                                select sum($p1)   from fdmkcbqc where date=date_format('$day','%Y-%m') and dept='$dept'
                                ),0) as value ";  
                        }
                    }
                    //计算有效结存无效结存列
                    else{
                        if($flag){
                            $sql=" select (
                            select sum($p1) from  fdmkcb where date BETWEEN  date_add('$day', interval - day('$day') + 1 day) and '$day' and dept='$dept' 
                            and product='$p2'
                            )+ifnull((
                            select sum($p1) from  fdmkcbqc where date=date_format('$day','%Y-%m') and dept='$dept' 
                            and product='$p2'
                            ),0) as value ";
                        }
                    }
                    if($sql!=""){
                        $temp=M()->query($sql);
                        $temp=$temp[0]['value'];
                        $data[$keytr]['tr'][$keytd]['value']=$temp;
                    }
                }
                
               
       }
    }
        
        $jsonData['data']=$data;
         
        $json=json_encode($jsonData);
		//echo "shuchu".$json;
        //计算redis键名
        $now=TODAY;
        $key="report-$dept-$now-FDMKC";
        
        //存入redis
        $result=$redis->set($key,$json);
		//var_dump($result);return;
        if($js==''){
        if($result)
            echo '{"resultcode":0,"resultmsg":"保存成功"}';
            else
                echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
        }
    }
      
	 /**
     * 查询当天的日报如果没有填报则计算模板输出
     */
    public function search($token=""){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
		//print_r($userinfo);
        if(!$userinfo)
        {
			
            $this->response(retmsg(-2),'json');
            return;
        }
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/FDMKC.txt");
        $handle=fopen($filename,'r');
		$js=fread($handle, filesize($filename));
		//echo $js;
		fclose($handle);
		$redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $now=TODAY;
        $dept=$userinfo['dept_id'];
        $key="report-FDMKC";
       //如果模板不存在就存入模板
	   //$test=$redis->get($key);
	   //echo $test;
	   //var_dump($test);return;
        if($redis->get($key)==null)
		{
			$redis->set($key,$js);
		}
        $json=$redis->get("report-$dept-$now-FDMKC");
		//echo $json;var_dump($json);return;
        //今天没有填报过数据就计算模板（模板加结存）
		if($json!=null)
		{
			echo $json;
		}
            
		else
		{   
			$this->submit($js,$token);
			//echo "jieshuhanshu";
			$js=$redis->get("report-$dept-$now-FDMKC");
		    echo $js;
		}
 
		
		
    } 
    	
    public function test(){
        echo  TODAY;
        ECHO C('REDIS_PWD');
    }
    
}


?>