<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 *数码产品表期初存储
 * @author Administrator
 *
 */
class NewSMCPKCQCController extends RestController{
    
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
       if(!empty($jsonData)){
                check_submit_time();
            }
        //默认部门为admin
        $dept=$userinfo['dept_id'];
        //$dept=25;
        $now=date("Ym");
        $key="report-$dept-$now-NEW_SMCPKCQC";
       
        $px=1;
        $data=$jsonData['data'];
        $sql="replace into new_smcpkcbqc(yxjc,wxjc,lx,brand,xh,cpgg,date,dept,px) values ";
        foreach($data as $key=>$tr){
            $tds=$tr['tr'];
             //第一二行不入库
            if($key>1){
                $sql.="(";
                foreach($tds as $td){
                    $product_type=trim($td['product_type']);
                    $product=explode(",",trim($td["product"]));
                    $cpxh=$product[0];
                    $cpgg=$product[1];
                    $brand=trim($td["brand"]);
                    $value=$td['value'];
                  if(is_numeric($value))
                      $sql.="$value,";  
                }
                $sql.="'$product_type','$brand','$cpxh','$cpgg',date_format(now(),'%Y-%m'),'$dept','$px'),";
				$px++;
            }
        }
        $sql=substr($sql,0,strlen($sql)-1);
        $result = M()->execute($sql);
        $today=TODAY;
        $query = M()->query("select json from new_smcpkcb_json where dept = $dept and date='$today'");
        $json = $query[0]['json'];
        if ($json =='')
        {
            $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/NEW_SMCPKC.txt");
            $handle=fopen($filename,'r');
            $json=fread($handle, filesize($filename));
            fclose($handle);
        }
        $gx = new NewSMCPKCController();
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
        $dept=$userinfo['dept_id'];
       //获取模版数据
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/NEW_SMCPKCQC.txt");
        $handle=fopen($filename,'r');
        $js=fread($handle, filesize($filename));
        fclose($handle);

        //查询本月期初数据
        $now=date("Y-m",strtotime(TODAY));
        $query = M()->query("select * from new_smcpkcbqc where dept = $dept and date='$now'");
        if(!count($query)){
            $qcJson = $js;
        }else{
            //若保存过期初数据,组合成json数据
            //归类数据方便json赋值
            foreach($query as $value){
                $result[$value['brand'].$value['xh'].','.$value['cpgg']]['有效结存'] = $value['yxjc'];
                $result[$value['brand'].$value['xh'].','.$value['cpgg']]['无效结存'] = $value['wxjc'];
            }
            $data = json_decode($js,true);
            //json赋值
            foreach($data['data'] as $key => $val){
                foreach ($val['tr'] as $ktr=>$vtr){
                    if ($ktr >=4){
                        if (isset($result[$vtr['brand'].$vtr['product']][$vtr['type_detail']])){
                            $data['data'][$key]['tr'][$ktr]['value'] = $result[$vtr['brand'].$vtr['product']][$vtr['type_detail']];
                        }
                    }
                }
            }
            $qcJson = json_encode($data);
        }

        echo $qcJson;
    }
}


?>