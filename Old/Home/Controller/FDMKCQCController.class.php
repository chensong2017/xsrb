<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 *防盗门库存表期初存储
 * @author Administrator
 *
 */
class FDMKCQCController extends RestController{
    
  /**
   * 防盗门库存表期初提交
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
        //部门
         $dept=$userinfo['dept_id'];
        $now=date("Ym");
        $key="report-$dept-$now-FDMKCQC";
       
       
        $data=$jsonData['data'];
        $sql="replace into fdmkcbqc(yxjc,wxjc,product,date,dept) values ";
        foreach($data as $key=>$tr){
            $tds=$tr['tr'];
             //第一二行不入库
            if($key>1){
                $sql.="(";
                foreach($tds as $td){
                   
                    $product=trim($td['product']);
                    $value=$td['value'];
                  if(is_numeric($value))
                      $sql.="$value,";  
                }
                $sql.="'$product',date_format(now(),'%Y-%m'),'$dept'),";
            }
        }
        $sql=substr($sql,0,strlen($sql)-1);
        M()->execute($sql);
        $now=date("Ym");
        $key="report-$dept-$now-FDMKCQC";
        $result=$redis->set($key,json_encode($jsonData));

        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/FDMKC.txt");
        $handle=fopen($filename,'r');
        $json=fread($handle, filesize($filename));
        fclose($handle);
        
        $gx = new FDMKCController();
        $gx -> submit($json,$token,'gx');
		
        if($result)
           echo '{"resultcode":0,"resultmsg":"保存成功"}';
        else 
           echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
    }
    //fangdaomen库存表期初查询
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
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/FDMKCQC.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);
        $redis->set("report-$dept-FDMKCQC",$js);
        $now=date("Ym");
        $key="report-$dept-$now-FDMKCQC";
        $json=$redis->get($key);
         if($json!=null)
            echo $json;
          else     
           echo $js; 
    
        
    }
    
    public function test(){
        
        /* $redis = new \Redis();
        $redis->connect($this->url,"6379");
     
       $dept="admin";
       $now=date("Ym");
       $month=date("Ym");
         $key="report-".$dept."-".$now."-FDMKCQC";
        $json=json_decode($redis->get($key),true);
        $FDMKCQC=json_decode($redis->get("report-admin-$month-FDMKCQC"),true);
        $qcjs=$FDMKCQC['QC'];
        //print_r($qcjs);
       // echo json_encode($json["JS"]); 
      /*  echo md5("三代机 三合一(大机) 	海星(大机)有效结存")."<br>";
       echo md5("三代机三合一(大机)有效结存"); */
      /*  $key="report-$dept-$now-FDMKCQC";
       $json=$redis->get($key); */
       //echo $json;
       //echo $key; */
       $redis = new \Redis();
       $redis->connect(C('REDIS_URL'),"6379");
       
       
    }
}


?>