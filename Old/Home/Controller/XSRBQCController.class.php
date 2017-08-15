<?php
namespace Home\Controller;
use Think\Controller\RestController;
/**
 * 销售日报表期初
 */
class XSRBQCController extends RestController{
    
    //销售日报表期初提交
    public function submit($token=""){
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
       //当前月
        $now=date("Ym");
        $dept=$userinfo['dept_id'];
        $key="report-$dept-$now-XSRBQC";
       
        $data=$jsonData['data'];
        $sql="replace into xsrbqc(fdm,sdj,dmb,type,type_detail,date,dept) values ";
        foreach($data as $key=>$tr){
            $tds=$tr['tr'];
             //第一行不入库
            if($key>0){
                $sql.="(";
                foreach($tds as $td){
                    $type=trim($td['type']);
                    $type_detail=trim($td['type_detail']);
                    $product=trim($td['product']);
                    $value=$td['value'];
                  if(is_numeric($value))
                      $sql.="$value,";  
                }
                $sql.="'$type','$type_detail',date_format(now(),'%Y-%m'),'$dept'),";
            }
        }
        $sql=substr($sql,0,strlen($sql)-1);
        M()->execute($sql);
        $now=date("Ym");
        $key="report-$dept-$now-XSRBQC";
        $result=$redis->set($key,json_encode($jsonData));

		$date = date("Ymd");
        $json = $redis->get("report-$dept-$date-XSRBLR");
        if ($json =='')
        {
            $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");
            $handle=fopen($filename,'r');
            $json=fread($handle, filesize($filename));
            fclose($handle);
        }
        
        $gx = new XSRBLRController();
        $gx -> submit($json,$token,'gx','','','');
		
        if($result)
           echo '{"resultcode":0,"resultmsg":"保存成功"}';
        else 
           echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
    }
    
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
      $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBQC.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);
       //当前月
         $now=date("Ym");
        $dept=$userinfo['dept_id'];
        $key="report-$dept-$now-XSRBQC";
        $redis->set("report-$dept-XSRBQC",$js);
        $json=$redis->get($key);
       if($json!=null)
            echo $json; 
        else    
            echo $redis->get("report-$dept-XSRBQC");  
        
    }
    
    public function test(){
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
         
        $dept="admin";
        $now=date("Ym");
        $month=date("Ym");
        $key="report-".$dept."-".$now."-XSRBQC";
        $json=json_decode($redis->get($key),true);
        $SMCPKCQC=json_decode($redis->get("report-admin-$month-XSRBQC"),true);
        $qcjs=$SMCPKCQC['QC'];
        print_r($qcjs);
        echo md5("预收账款结存防盗门");
    
    }
    
}


?>