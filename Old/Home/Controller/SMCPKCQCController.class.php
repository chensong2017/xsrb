<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 *数码产品表期初存储
 * @author Administrator
 *
 */
class SMCPKCQCController extends RestController{
    
  /**
   * 数码产品库存表期初提交
   */ 
    public function submit($token=''){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $jsonData=json_decode(file_get_contents("php://input"),true);
       
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        //默认部门为admin
        $dept=$userinfo['dept_id'];
        $now=date("Ym");
        $key="report-$dept-$now-SMCPKCQC";
       
        $px=1;
        $data=$jsonData['data'];
        $sql="replace into smcpkcbqc(yxjc,wxjc,lx,xh,cpgg,date,dept,px) values ";
        foreach($data as $key=>$tr){
            $tds=$tr['tr'];
             //第一行不入库
            if($key>0){
                $sql.="(";
                foreach($tds as $td){
                    $product_type=trim($td['product_type']);
                    $product=trim($td['product']);
                    $value=$td['value'];
                  if(is_numeric($value))
                      $sql.="$value,";  
                }
                $sql.="'$product_type','$product_type','$product',date_format(now(),'%Y-%m'),'$dept','$px'),";
				$px++;
            }
        }
        $sql=substr($sql,0,strlen($sql)-1);
        M()->execute($sql);
        $now=date("Ym");
        $key="report-$dept-$now-SMCPKCQC";
        $result=$redis->set($key,json_encode($jsonData));

        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/SMCPKC.txt");
        $handle=fopen($filename,'r');
        $json=fread($handle, filesize($filename));
        fclose($handle);
        
        $gx = new SMCPKCController();
        $gx -> submit($json,$token,'gx');
		
        if($result)
           echo '{"resultcode":0,"resultmsg":"保存成功"}';
        else 
           echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
    }
    //数码产品库存表期初查询
    public function search($token=""){
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
        $dept=$userinfo['dept_id'];
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/SMCPKCQC.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);
        $redis->set("report-$dept-SMCPKCQC",$js);
        $now=date("Ym");
        $key="report-$dept-$now-SMCPKCQC";
        $json=$redis->get($key);
     if($json!=null)
            echo $json;
          else   
            echo $js; 
    
        
    }
    
    public function test(){
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
     
       $dept="admin";
       $now=date("Ym");
       $month=date("Ym");
         $key="report-".$dept."-".$now."-SMCPKCQC";
        $json=json_decode($redis->get($key),true);
        $SMCPKCQC=json_decode($redis->get("report-admin-$month-SMCPKCQC"),true);
        $qcjs=$SMCPKCQC['QC'];
        print_r($qcjs);
       // echo json_encode($json["JS"]); 
      /*  echo md5("三代机 三合一(大机) 	海星(大机)有效结存")."<br>";
       echo md5("三代机三合一(大机)有效结存"); */
       $key="report-$dept-$now-SMCPKCQC";
       $json=$redis->get($key);
       echo $json;
    }
}


?>