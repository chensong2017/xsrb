<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 * 数码产品库存表
 * @author Administrator
 *
 */
class NewSMCPKCController extends RestController{
    
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
		if(!empty($jsonData)){
                check_submit_time();
            }
        if(empty($jsonData))
            $jsonData=json_decode($js,true);
        $data=$jsonData['data'];
        //redis时间键
        $day=TODAY;
        $month=date("Ym");
        //部门默认admin
        $dept=$userinfo['dept_id'];
        $px =1;   //对数码产品规格进行排序的
        //解析json 数据存入mysql
        //2017-2-13添加产品品牌brand去掉重复列cpxh cpgg为规格和型号的拼接
        $sql="replace into new_smcpkcb(dbsr,wgsr,xszc,dbzc,hhzc,zcspsrzc,qtzc,yxjc,hhsh,wxdbzc,
            wxqtzc,wxjc,lx,brand,cpxh,cpgg,date,dept,px) values ";
       
        foreach($data as $keytr=>$tr){
            
            $tr=$tr['tr'];
            //表头和小计行数据不入库
            if($keytr>5){
                $sql.="(";
                foreach($tr as $keytd=>$td){
                    $product_type=trim($td["product_type"]);
                    $product=explode(",",trim($td["product"]));
                    $cpxh=$product[0];
                    $cpgg=$product[1];
                    $type=trim($td["type"]);
                    $type_detail=trim($td["type_detail"]);
                    $value=trim($td["value"]);
                    $brand=trim($td["brand"]);
                    if($value==null)
                        $value=0;
                    if(is_numeric($value)){
                    
                        $sql.="$value,";
                    }
                }
                
            $sql.="'$product_type','$brand','$cpxh','$cpgg','$day','$dept','$px'),";
			$px++;
            }
            
        }
        $sql=substr($sql,0,strlen($sql)-1);
       M()->execute($sql);
       $sql="update new_smcpkcb set yxjc=dbsr+wgsr-xszc-dbzc-hhzc-zcspsrzc-qtzc, 
           wxjc=hhsh-wxdbzc-wxqtzc where date=date('$day') and dept='$dept'";
       M()->execute($sql);
      
       //更新有效无效结存数据
       foreach($data as $keytr=>$tr){
           $tr=$tr['tr'];
           foreach($tr as $keytd=>$td){
               $product_type=trim($td["product_type"]);
               $product=explode(",",trim($td["product"]));
               $cpxh=$product[0];
               $cpgg=$product[1];
               $type=trim($td["type"]);
               $type_detail=trim($td["type_detail"]);
               $brand=trim($td["brand"]);
               $value=trim($td["value"]);
               $p="";
               if($keytr==7){
              $test=1;
               }
               if($product_type=="门配"||$product_type=="机顶盒"||$product_type=="国贸配件"||
                   $product_type=="三代机"||$product_type=="地面波"||$product_type=="智能卡"||
                   $product_type=="高频头"||$product_type=="天线"||$product_type=="电控产品")
               {     
                  if($type_detail=="有效结存")
                      $p="yxjc";
                  elseif($type_detail=="无效结存")
                      $p="wxjc";
                      if($p!=""){
                          
                              $sql=" select ifnull(sum($p),0)+ 
                              ifnull((
                              select $p from new_smcpkcbqc where date=date_format('$day','%Y-%m')
                              and lx='$product_type' and brand='$brand' and xh='$cpxh' and cpgg='$cpgg' and dept='$dept'
                              ),0) as value
                              from new_smcpkcb where  date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day' 
                              and lx='$product_type' and brand='$brand' and cpxh='$cpxh' and cpgg='$cpgg'  and dept='$dept' ";
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
                elseif($product_type=="机顶盒小计")
                     $p2="机顶盒";
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
                        $sql=" select sum($p1) as value from new_smcpkcb where lx='$p2' and
                        date=date('$day') and dept='$dept' ";
                        //计算结存
                        if($flag==1)
                            $sql="select ifnull((select
                            (select sum($p1) as value from new_smcpkcb where lx='$p2' and
                             date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day'  and dept='$dept')+
                            (
                              select ifnull((select sum($p1) as value from new_smcpkcbqc where lx='$p2' and
                            date=date_format('$day','%Y-%m') and dept='$dept'),0)
                            )
                            as value),0) as value";
                    }
                    //计算合计
                    else{
                        
                        if($flag==0)
                        $sql=" select sum($p1) as value from new_smcpkcb where
                        date=date('$day') and dept='$dept' ";
                        //计算结存
                        elseif($flag==1)
                        $sql=" select ifnull((select
                        (select sum($p1) as value from new_smcpkcb where
                         date BETWEEN date_add('$day', interval - day('$day') + 1 day) and '$day'  and dept='$dept')+
                        (
                          select ifnull((select sum($p1) as value from new_smcpkcbqc where
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
        $json=json_encode($jsonData,JSON_UNESCAPED_UNICODE);
         if($day == TODAY){
             $result = M()->execute("replace into new_smcpkcb_json(dept,`date`,`json`)value($dept,'$day','$json')");
         }

        if($js==''){
            if($result)
                echo '{"resultcode":0,"resultmsg":"保存成功"}';
            else
                echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
        }else{
            return $json;
        }
    }
    
    /**
     * 查询当天的日报如果没有填报则计算模板输出
     */
    public function search($token=""){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        /* if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        } */

        $now=TODAY;
        $dept=$userinfo['dept_id'];
        $query = M()->query("select json from new_smcpkcb_json where dept = $dept and date='$now'");
        $js = $query[0]['json'];
        if (empty($js)){
            //模板数据
            $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/NEW_SMCPKC.txt");
            $handle=fopen($filename,'r');
            $js=fread($handle, filesize($filename));
            fclose($handle);
            $js = $this->submit($js,$token,'gx');
        }
        echo $js;
    }
}


?>