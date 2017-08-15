<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 * 数码产品库存表
 * @author Administrator
 *
 */
class SMCPKCController extends RestController{
    
    /**
     * 数码产品库存表提交和计算
     * 解析json 基础数据插入mysql
     * 计算数据
     * 计算完后的json存入redis
     * @param json 
     * @return json
     */
    public function submit($js='',$token='',$type =''){
        header("Access-Control-Allow-Origin: *");
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
        //部门默认admin
        $dept=$userinfo['dept_id'];
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
       $redis->auth(C('REDIS_PWD'));
        $px =1;   //对数码产品规格进行排序的
        //解析json 数据存入mysql
        $sql="replace into smcpkcb(dbsr,wgsr,xszc,dbzc,hhzc,zcspsrzc,qtzc,yxjc,hhsh,wxdbzc,
            wxqtzc,wxjc,lx,cpxh,cpgg,date,dept,px) values ";
       
        foreach($data as $keytr=>$tr){
            
            $tr=$tr['tr'];
            //表头和小计行数据不入库
            if($keytr>4&&count($tr)>13){
                $sql.="(";
                foreach($tr as $keytd=>$td){
                    $product_type=trim($td["product_type"]);
                    $product=trim($td["product"]);
                    $type=trim($td["type"]);
                    $type_detail=trim($td["type_detail"]);
                    $value=trim($td["value"]);
                    if($value==null)
                        $value=0;
                    if(is_numeric($value)){
                    
                        $sql.="$value,";
                    }
                }
                
            $sql.="'$product_type','$product_type','$product','$day','$dept','$px'),";
			$px++;
            }
            
        }
        $sql=substr($sql,0,strlen($sql)-1);
       M()->execute($sql);
       $sql="update smcpkcb set yxjc=dbsr+wgsr-xszc-dbzc-hhzc-zcspsrzc-qtzc, 
           wxjc=hhsh-wxdbzc-wxqtzc where date=date('$day') and dept='$dept'";
       M()->execute($sql);
      
       //更新有效无效结存数据
       foreach($data as $keytr=>$tr){
           $tr=$tr['tr'];
           foreach($tr as $keytd=>$td){
               $product_type=trim($td["product_type"]);
               $product=trim($td["product"]);
               $type=trim($td["type"]);
               $type_detail=trim($td["type_detail"]);
               $value=trim($td["value"]);
               $p="";
               if($product_type=="三代机"||$product_type=="地面波"||$product_type=="智能卡"
                   ||$product_type=="高频头"||$product_type=="天线"||$product_type=="电控产品")
               {     
                  if($type_detail=="有效结存")
                      $p="yxjc";
                  elseif($type_detail=="无效结存")
                      $p="wxjc";
                      if($p!=""){
                          
                              $sql=" select sum($p)+ 
                              ifnull((
                              select $p from smcpkcbqc where date=date_format('$day','%Y-%m')
                              and lx='$product_type' and cpgg='$product' and dept='$dept'
                              ),0) as value
                              from smcpkcb where  date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day' 
                              and lx='$product_type' and cpgg='$product' and dept='$dept' ";
                              $temp=M()->query($sql);
                              $temp=$temp[0]['value'];
                              $data[$keytr]['tr'][$keytd]['value']=$temp; 
                  }
               }
               
           }
    }
       //更新小计数据
       foreach($data as $keytr=>$tr){
           $tr=$tr['tr'];
           foreach($tr as $keytd=>$td){
               $product_type=trim($td["product_type"]);
               $type=trim($td["type"]);
               $type_detail=trim($td["type_detail"]);
               $value=trim($td["value"]);
               $p1="";
               $p2="";
               //计算结存数据标志
               $flag=0;
            
                if($product_type=="三代机小计")
                     $p2="三代机";
                elseif($product_type=="地面波小计")
                     $p2="地面波";
                elseif($product_type=="智能卡小计")
                     $p2="智能卡";
              
                elseif($product_type=="高频头小计")
                     $p2="高频头";
                elseif($product_type=="天线小计")
                     $p2="天线";
                elseif($product_type=="电控产品小计")
                     $p2="电控产品";
               
                elseif($product_type=="合计")
                     $p2="合计";
                else 
                    break;
                if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $p1="dbsr";
                            elseif($type_detail=="外购收入")
                                $p1="wgsr";
                            elseif($type_detail=="销售支出")
                                $p1="xszc";
                            elseif($type_detail=="调拨支出")
                                $p1="dbzc";
                            elseif($type_detail=="换货支出")
                                $p1="hhzc";
                            elseif($type_detail=="暂存商品收入/支出")
                                $p1="zcspsrzc";
                            elseif($type_detail=="其他支出")
                                $p1="qtzc";
                            elseif($type_detail=="有效结存"){                              
                                $p1="yxjc";
                                $flag=1;
                            }
            
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $p1="hhsh";
                            elseif($type_detail=="调拨支出")
                                $p1="wxdbzc";
                            elseif($type_detail=="其他支出")
                                $p1="wxqtzc";
                            elseif($type_detail=="无效结存"){ 
                                 $p1="wxjc";
                                 $flag=1;
                            }
                    }   
                  
                if($p1!=""&&$p2!==""){
                    //计算小计
                    if($p2!="合计"){
                        $sql=" select sum($p1) as value from smcpkcb where lx='$p2' and
                        date=date('$day') and dept='$dept' ";
                        //计算结存
                        if($flag==1)
                            $sql="select ifnull((select
                            (select sum($p1) as value from smcpkcb where lx='$p2' and
                             date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day'  and dept='$dept')+
                            (
                              select ifnull((select sum($p1) as value from smcpkcbqc where lx='$p2' and
                            date=date_format('$day','%Y-%m') and dept='$dept'),0)
                            )
                            as value),0) as value";
                    }
                    //计算合计
                    else{
                        
                        if($flag==0)
                        $sql=" select sum($p1) as value from smcpkcb where
                        date=date('$day') and dept='$dept' ";
                        //计算结存
                        elseif($flag==1)
                        $sql=" select ifnull((select
                        (select sum($p1) as value from smcpkcb where
                         date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day'  and dept='$dept')+
                        (
                          select ifnull((select sum($p1) as value from smcpkcbqc where
                        date=date_format('$day','%Y-%m') and dept='$dept'),0)
                        )
                        as value),0) as value";
                    }
                    $temp=M()->query($sql);
                    $temp=$temp[0]['value'];
                    $data[$keytr]['tr'][$keytd]['value']=$temp;
                }
          
       }
    }
        $jsonData['data']=$data;
         
        $json=json_encode($jsonData);
        //计算redis键名
        $now=TODAY;
        $key="report-$dept-$now-SMCPKC";
        
        //存入redis
        $result=$redis->set($key,$json);
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
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        //模板数据
       $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/SMCPKC.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);
      $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $now=TODAY;
        $dept=$userinfo['dept_id'];
        $key="report-SMCPKC";
        if($redis->get($key)==null)
            $redis->set($key,$js);
        $json=$redis->get("report-$dept-$now-SMCPKC"); 
      //今天没有填报过数据就计算模板（模板加结存）
		if($json!=null)
            echo $json;
		else
		{   
			$this->submit($js,$token);
			$js=$redis->get("report-$dept-$now-SMCPKC");
		    echo $js;
		}
    
   
    }
    public function test(){
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
		var_dump($redis);
        
    }
}


?>