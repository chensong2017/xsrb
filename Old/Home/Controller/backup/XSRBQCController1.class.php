<?php
namespace Home\Controller;
use Think\Controller;
//销售日报表期初
class XSRBQCController extends Controller{
    
    //销售日报表期初提交
    public function submit(){
        header("Access-Control-Allow-Origin: *");
       $jsonData=json_decode(file_get_contents("php://input"),true);
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
       //当前月
        $now=date("Ym");
        $dept="admin";
        $key="report-$dept-$now-XSRBQC";
        $temp=array();
       //提交的json数据
        $temp["TJ"]=$jsonData;
       //计算的json对应的数据
        $data=$jsonData['data'];
        foreach($data as $tr){
            $tds=$tr['tr'];
            
            foreach($tds as $td){
                $type=trim($td['type']);
                $type_detail=trim($td['type_detail']);
                $product=trim($td['product']);
                $value=$td['value'];
                $key1=md5($type_detail.$product);
                //如果value 数据合法则存入数据库
                if(is_numeric($value)&&$value>0)
                    $temp['QC']["$key1"]=$value;
            }
        }
        
        $result=$redis->set($key,json_encode($temp));
        if($result)
           echo '{"resultcode":0,"resultmsg":"保存成功"}';
        else 
           echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
    }
    
    public function search(){
        header("Access-Control-Allow-Origin: *");
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
       $js='{"data":[{"tr":[{"dataType":0,"value":"业务名称","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":""},{"dataType":0,"value":"项目类别","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":""},{"dataType":0,"value":"项目名称","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"防盗门","rowspan":1,"colspan":1,"product":"防盗门","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"数码产品","rowspan":1,"colspan":1,"product":"数码产品","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product":"三代机","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"电控产品","rowspan":1,"colspan":1,"product":"电控产品","type":"业务名称","type_detail":"项目名称"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"现金结存","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"现金结存","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"现金结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"现金结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"现金结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"现金结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"现金结存"}]},{"tr":[{"dataType":0,"value":"应收款","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":""},{"dataType":0,"value":"应收款","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":""},{"dataType":0,"value":"应收款结存","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":"应收款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"应收款","type_detail":"应收款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"应收款","type_detail":"应收款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"应收款","type_detail":"应收款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"应收款","type_detail":"应收款结存"}]},{"tr":[{"dataType":0,"value":"预收账款结存","rowspan":1,"colspan":1,"product":"","type":"预收账款结存","type_detail":""},{"dataType":0,"value":"预收账款结存","rowspan":1,"colspan":1,"product":"","type":"预收账款结存","type_detail":""},{"dataType":0,"value":"预收账款结存","rowspan":1,"colspan":1,"product":"","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"预收账款结存","type_detail":"预收账款结存"}]},{"tr":[{"dataType":0,"value":"暂存款结存","rowspan":1,"colspan":1,"product":"","type":"暂存款结存","type_detail":""},{"dataType":0,"value":"暂存款结存","rowspan":1,"colspan":1,"product":"","type":"暂存款结存","type_detail":""},{"dataType":0,"value":"暂存款结存","rowspan":1,"colspan":1,"product":"","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"暂存款结存","type_detail":"暂存款结存"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效结存"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"送货结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"送货结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"送货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"送货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"送货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"送货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"送货结存"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"暂时商品结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"暂时商品结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"暂时商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"暂时商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"暂时商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"暂时商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"暂时商品结存"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"铺货结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"铺货结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"铺货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"铺货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"铺货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"铺货结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"铺货结存"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"待处理商品结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"待处理商品结存","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"待处理商品结存"}]}]}';
       //当前月
        $now=date("Ym");
        $dept="admin";
        $key="report-$dept-$now-XSRBQC";
        $redis->set("report-$dept-XSRBQC",$js);
        $json=json_decode($redis->get($key),true);
        if($json!=null)
            echo json_encode($json['TJ']); 
        else   
            echo $redis->get("report-$dept-XSRBQC"); 
    }
    
    public function test(){
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
         
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