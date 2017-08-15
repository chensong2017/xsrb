<?php
namespace Home\Controller;
use Think\Controller;
//销售日报录入
class XSRBLRController extends Controller{
    
    //销售日报表提交
    public function submit(){
       header("Access-Control-Allow-Origin: *");
        $jsonData=json_decode(file_get_contents("php://input"),true); 
        $data=$jsonData['data'];
        
        //echo json_encode($jsonData);
        //redis时间键
        $day=date("Ymd");
        $month=date("Ym");
        
        $dept="admin";
      
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
       //查询当前月结存
         $temp=$day-1;//前一天
         $yjc=json_decode($redis->get("report-$dept-$temp-XSRBLR-YJC"),true);
         
         //获取期初数据
         $XSRBLR=json_decode($redis->get("report-admin-$month-XSRBQC"),true);
         $qc=$XSRBLR['QC'];
         
         $xjjc=0;//现金结存
         $xjsr=0;//现金收入
         $xjzc=0;//现金支出
         $sylxjsr=0;//损益类现金收入
         $zclxjsr=0;//资产类现金收入
         $fylxjzc=0;//费用类现金支出
         $zclxjzc=0;//资产类现金支出
         //应收款结存 
         $yskjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //新增
         $xz=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //收回欠款 
         $shqk=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //预收帐款结存 
         $yszkjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //暂存款结存 
         $zckjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当月销售收入累计
         $dyxssrlj=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当月销售成本累计
         $dyxscblj=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当月毛利累计
         $dymllj=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当月毛利率
         $dymll=array('fdm'=>'100%','smcp'=>'100%','sdj'=>'100%','dkcp'=>'100%');
         //当日销售收入
         $drxssr=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当日销售成本
         $drxscb=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当日毛利
         $drml=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //当日毛利率
         $drmll=array('fdm'=>'100%','smcp'=>'100%','sdj'=>'100%','dkcp'=>'100%');
         
         //商品业务
         //有效结存
         $yxjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //送货结存
         $shjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //无效结存
         $wxjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //暂存商品结存
         $zcspjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //铺货商品结存
         $phjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //待处理商品结存 
         $dclspjc=array('fdm'=>0,'smcp'=>0,'sdj'=>0,'dkcp'=>0);
         //遍历单元格计算数据(当天当前表的数据)
         foreach($data as $keytr=>$tr){
             $tr=$tr['tr'];
             $yxjc=0;//每一行的有效结存
             $wxjc=0;//每一行的无效结存
             foreach($tr as $keytd=>$td){
                 $product=trim($td["product"]);
                 $type_detail=trim($td["type_detail"]);
                 $value=$td["value"];
                 //计算现金收入支出
                 if(strstr($type_detail,"损益性现金收入")||strstr($type_detail,"资产类现金收入"))
                     $xjsr+=$value;
                 elseif(strstr($type_detail,"费用类现金支出")||strstr($type_detail,"资产类现金支出"))
                    $xjzc+=$value;
                 
                 
                 if($product=="防盗门"){
                     //计算应收款
                    if($type_detail=="资产类现金收入收回欠款")
                        $yskjc['fdm']-=$value;
                    elseif($type_detail=="应收款新增") 
                        $yskjc['fdm']+=$value;
                    //计算 预收账款结存
                    elseif($type_detail=="资产类现金支出减少预收款")
                         $yszkjc['fdm']-=$value;
                    elseif($type_detail=="资产类现金收入增加预收款")
                         $yszkjc['fdm']+=$value;
                    //计算暂存款结存
                    elseif($type_detail=="资产类现金支出减少暂存款")
                         $zckjc['fdm']-=$value;
                    elseif($type_detail=="资产类现金收入增加暂存款")
                         $zckjc['fdm']+=$value;
                    //计算当日销售收入
                    if($type_detail=="损益性现金收入当日销现"||$type_detail=="应收款新增")
                        $drxssr['fdm']+=$value;
                     //计算商品业务有效结存
                    elseif(strstr($type_detail,"有效支出"))
                         $yxjc['fdm']-=$value;
                    elseif(strstr($type_detail,"有效收入"))
                         $yxjc['fdm']+=$value;
                    //计算送货结存
                    elseif(strstr($type_detail,"送货支出"))
                         $shjc['fdm']-=$value;
                    elseif(strstr($type_detail,"送货收回"))
                         $shjc['fdm']+=$value;
                     //计算无效结存 
                    elseif(strstr($type_detail,"无效收入"))
                         $wxjc['fdm']+=$value;
                    elseif(strstr($type_detail,"无效支出"))
                         $wxjc['fdm']-=$value;
                     //计算暂存商品结存
                    elseif(strstr($type_detail,"增加暂存商品"))
                         $zcspjc['fdm']+=$value;
                    elseif(strstr($type_detail,"减少暂存商品"))
                         $zcspjc['fdm']-=$value;
                     //计算铺货结存 
                    elseif(strstr($type_detail,"增加铺货商品"))
                         $phjc['fdm']+=$value;
                    elseif(strstr($type_detail,"减少铺货商品"))
                         $phjc['fdm']-=$value;
                   //计算待处理商品结存
                    elseif(strstr($type_detail,"增加待处理商品"))
                         $dclspjc['fdm']+=$value;
                    elseif(strstr($type_detail,"减少待处理商品"))
                         $dclspjc['fdm']-=$value;
                 }
                 elseif($product=="数码产品"){
                     if($type_detail=="资产类现金收入收回欠款")
                        $yskjc['smcp']-=$value;
                    elseif($type_detail=="应收款新增") 
                        $yskjc['smcp']+=$value;
                        //计算 预收账款结存
                        elseif($type_detail=="资产类现金支出减少预收款")
                         $yszkjc['smcp']-=$value;
                    elseif($type_detail=="资产类现金收入增加预收款")
                         $yszkjc['smcp']+=$value;
                        //计算暂存款结存
                         elseif($type_detail=="资产类现金支出减少暂存款")
                         $zckjc['smcp']-=$value;
                    elseif($type_detail=="资产类现金收入增加暂存款")
                         $zckjc['smcp']+=$value;
                        //计算当日销售收入
                      if($type_detail=="损益性现金收入当日销现"||$type_detail=="应收款新增")
                        $drxssr['smcp']+=$value;
                        
                        //计算商品业务有效结存
                        elseif(strstr($type_detail,"有效支出 "))
                        $yxjc['smcp']-=$value;
                        elseif(strstr($type_detail,"有效收入"))
                        $yxjc['smcp']+=$value;
                        //计算送货结存
                        elseif(strstr($type_detail,"送货支出 "))
                        $shjc['smcp']-=$value;
                        elseif(strstr($type_detail,"送货收回"))
                        $shjc['smcp']+=$value;
                        //计算无效结存
                        elseif(strstr($type_detail,"无效收入"))
                        $wxjc['smcp']+=$value;
                        elseif(strstr($type_detail,"无效支出"))
                        $wxjc['smcp']-=$value;
                        //计算暂存商品结存
                        elseif(strstr($type_detail,"增加暂存商品"))
                        $zcspjc['smcp']+=$value;
                        elseif(strstr($type_detail,"减少暂存商品"))
                        $zcspjc['smcp']-=$value;
                        //计算铺货结存
                        elseif(strstr($type_detail,"增加铺货商品"))
                        $phjc['smcp']+=$value;
                        elseif(strstr($type_detail,"减少铺货商品"))
                        $phjc['smcp']-=$value;
                        //计算待处理商品结存
                        elseif(strstr($type_detail,"增加待处理商品"))
                        $dclspjc['smcp']+=$value;
                        elseif(strstr($type_detail,"减少待处理商品"))
                        $dclspjc['smcp']-=$value;
                 }
                 elseif($product=="三代机"){
                     if($type_detail=="资产类现金收入收回欠款")
                        $yskjc['sdj']-=$value;
                    elseif($type_detail=="应收款新增") 
                        $yskjc['sdj']+=$value;
                        //计算 预收账款结存
                        elseif($type_detail=="资产类现金支出减少预收款")
                         $yszkjc['sdj']-=$value;
                    elseif($type_detail=="资产类现金收入增加预收款")
                         $yszkjc['sdj']+=$value;
                        //计算暂存款结存
                       elseif($type_detail=="资产类现金支出减少暂存款")
                         $zckjc['sdj']-=$value;
                    elseif($type_detail=="资产类现金收入增加暂存款")
                         $zckjc['sdj']+=$value;
                        //计算当日销售收入
                    if($type_detail=="损益性现金收入当日销现"||$type_detail=="应收款新增")
                        $drxssr['sdj']+=$value;
                        
                        //计算商品业务有效结存
                        elseif(strstr($type_detail,"有效支出 "))
                        $yxjc['sdj']-=$value;
                        elseif(strstr($type_detail,"有效收入"))
                        $yxjc['sdj']+=$value;
                        //计算送货结存
                        elseif(strstr($type_detail,"送货支出 "))
                        $shjc['sdj']-=$value;
                        elseif(strstr($type_detail,"送货收回"))
                        $shjc['sdj']+=$value;
                        //计算无效结存
                        elseif(strstr($type_detail,"无效收入"))
                        $wxjc['sdj']+=$value;
                        elseif(strstr($type_detail,"无效支出"))
                        $wxjc['sdj']-=$value;
                        //计算暂存商品结存
                        elseif(strstr($type_detail,"增加暂存商品"))
                        $zcspjc['sdj']+=$value;
                        elseif(strstr($type_detail,"减少暂存商品"))
                        $zcspjc['sdj']-=$value;
                        //计算铺货结存
                        elseif(strstr($type_detail,"增加铺货商品"))
                        $phjc['sdj']+=$value;
                        elseif(strstr($type_detail,"减少铺货商品"))
                        $phjc['sdj']-=$value;
                        //计算待处理商品结存
                        elseif(strstr($type_detail,"增加待处理商品"))
                        $dclspjc['sdj']+=$value;
                        elseif(strstr($type_detail,"减少待处理商品"))
                        $dclspjc['sdj']-=$value;
                 }
                 elseif($product=="电控产品"){
                     if($type_detail=="资产类现金收入收回欠款")
                        $yskjc['dkcp']-=$value;
                    elseif($type_detail=="应收款新增") 
                        $yskjc['dkcp']+=$value;
                        //计算 预收账款结存
                        elseif($type_detail=="资产类现金支出减少预收款")
                         $yszkjc['dkcp']-=$value;
                    elseif($type_detail=="资产类现金收入增加预收款")
                         $yszkjc['dkcp']+=$value;
                        //计算暂存款结存
                        elseif($type_detail=="资产类现金支出减少暂存款")
                         $zckjc['dkcp']-=$value;
                    elseif($type_detail=="资产类现金收入增加暂存款")
                         $zckjc['dkcp']+=$value;
                        //计算当日销售收入
                     if($type_detail=="损益性现金收入当日销现"||$type_detail=="应收款新增")
                        $drxssr['dkcp']+=$value;
                        
                        //计算商品业务有效结存
                        elseif(strstr($type_detail,"有效支出 "))
                        $yxjc['dkcp']-=$value;
                        elseif(strstr($type_detail,"有效收入"))
                        $yxjc['dkcp']+=$value;
                        //计算送货结存
                        elseif(strstr($type_detail,"送货支出 "))
                        $shjc['dkcp']-=$value;
                        elseif(strstr($type_detail,"送货收回"))
                        $shjc['dkcp']+=$value;
                        //计算无效结存
                        elseif(strstr($type_detail,"无效收入"))
                        $wxjc['dkcp']+=$value;
                        elseif(strstr($type_detail,"无效支出"))
                        $wxjc['dkcp']-=$value;
                        //计算暂存商品结存
                        elseif(strstr($type_detail,"增加暂存商品"))
                        $zcspjc['dkcp']+=$value;
                        elseif(strstr($type_detail,"减少暂存商品"))
                        $zcspjc['dkcp']-=$value;
                        //计算铺货结存
                        elseif(strstr($type_detail,"增加铺货商品"))
                        $phjc['dkcp']+=$value;
                        elseif(strstr($type_detail,"减少铺货商品"))
                        $phjc['dkcp']-=$value;
                        //计算待处理商品结存
                        elseif(strstr($type_detail,"增加待处理商品"))
                        $dclspjc['dkcp']+=$value;
                        elseif(strstr($type_detail,"减少待处理商品"))
                        $dclspjc['dkcp']-=$value;
                 }           
             }
         }
         
         $xjjc=$xjsr-$xjzc;
         $dyxssrlj=$drxssr;
         $drml=$drxssr;
        
         print_r($drxssr);
         //更新数据并加上期初和月累计并更新月累计
         foreach($data as $keytr=>$tr){
             $tr=$tr['tr'];
             foreach($tr as $keytd=>$td){
                 $product=trim($td["product"]);
                 $type=trim($td["type"]);
                 $type_detail=trim($td["type_detail"]);
                 $value=trim($td["value"]);
                
                 if($type_detail=="现金结存现金结存"&&is_numeric($value)){
                     $key1=md5($type_detail);
                    //加上月累计
                     $temp=$yjc["$key1"];
                     $yjc["$key1"]+=$xjjc;
                     $xjjc+=$temp;
                     //加上期初
                     //计算期初的现金结存
                     $fdm=md5("现金结存防盗门");
                     $smcp=md5("现金结存数码产品");
                     $sdj=md5("现金结存三代机");
                     $dkcp=md5("现金结存电控产品");
                     $xjjcqc=$qc["$fdm"]+$qc["$smcp"]+$qc["$sdj"]+$qc["$dkcp"];
                     $xjjc+=$xjjcqc;
                     
                     $data[$keytr]['tr'][$keytd]['value']=$xjjc;
                 }
                 
                 elseif($type_detail=="应收款结存"&&is_numeric($value)){
                    if($product=="防盗门"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$yskjc['fdm'];
                        $yskjc['fdm']+=$temp;
                       //加上期初
                      
                        $yskjc['fdm']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$yskjc['fdm'];
                    }
                    elseif($product=="数码产品"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$yskjc['smcp'];
                        $yskjc['smcp']+=$temp;
                        //加上期初
                        $yskjc['smcp']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$yskjc['smcp'];
                    }
                    elseif($product=="三代机"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$yskjc['sdj'];
                        $yskjc['sdj']+=$temp;
                        //加上期初
                        $yskjc['sdj']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$yskjc['sdj'];
                    }
                    elseif($product=="电控产品"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$yskjc['dkcp'];
                        $yskjc['dkcp']+=$temp;
                        //加上期初
                        $yskjc['dkcp']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$yskjc['dkcp'];
                    }
                    
                    
                 }
                 
                 elseif($type_detail=="预收账款结存"&&is_numeric($value)){
                     if($product=="防盗门"){
                        
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$yszkjc['fdm'];
                         $yszkjc['fdm']+=$temp;
                         //加上期初
                 
                         $yszkjc['fdm']+=$qc["$key1"];
                         
                         $data[$keytr]['tr'][$keytd]['value']= $yszkjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $yszkjc['smcp'];
                          $yszkjc['smcp']+=$temp;
                         //加上期初
                          $yszkjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']= $yszkjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $yszkjc['sdj'];
                          $yszkjc['sdj']+=$temp;
                         //加上期初
                          $yszkjc['sdj']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']= $yszkjc['sdj'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $yszkjc['dkcp'];
                          $yszkjc['dkcp']+=$temp;
                         //加上期初
                          $yszkjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']= $yszkjc['dkcp'];
                     }
                 
                 
                 }
                 
                 elseif($type_detail=="暂存款结存"&&is_numeric($value)){
                    if($product=="防盗门"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$zckjc['fdm'];
                        $zckjc['fdm']+=$temp;
                       //加上期初
                      
                        $zckjc['fdm']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$zckjc['fdm'];
                    }
                    elseif($product=="数码产品"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$zckjc['smcp'];
                        $zckjc['smcp']+=$temp;
                       //加上期初
                      
                        $zckjc['smcp']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$zckjc['smcp'];
                    }
                    elseif($product=="三代机"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$zckjc['sdj'];
                        $zckjc['sdj']+=$temp;
                       //加上期初
                      
                        $zckjc['sdj']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$zckjc['sdj'];
                    }
                    elseif($product=="电控产品"){
                       //加上月累计
                        $key1=md5($type_detail.$product);
                        $temp=$yjc["$key1"];
                        $yjc["$key1"]+=$zckjc['dkcp'];
                        $zckjc['dkcp']+=$temp;
                       //加上期初
                      
                        $zckjc['dkcp']+=$qc["$key1"];
                        $data[$keytr]['tr'][$keytd]['value']=$zckjc['dkcp'];
                    }
                  
                 }
                 elseif($type_detail=="当月销售收入累计"&&is_numeric($value)){

                     if($product=="防盗门"){
                        //加上月累计
                        
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $dyxssrlj['fdm'];
                         $dyxssrlj['fdm']+=$temp;
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['fdm'];
                         echo  "jc:".$yjc["$key1"]."<br>"."temp:".$temp;
                         
                         
                     }
                     
                     elseif($product=="数码产品"){
                        //加上月累计
                        $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $dyxssrlj['smcp'];
                         $dyxssrlj['smcp']+=$temp;
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['smcp'];
                     }
                     elseif($product=="三代机"){
                        //加上月累计
                        $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $dyxssrlj['sdj'];
                         $dyxssrlj['sdj']+=$temp;
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['sdj'];
                     }
                     elseif($product=="电控产品"){
                        //加上月累计
                        $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+= $dyxssrlj['dkcp'];
                         $dyxssrlj['dkcp']+=$temp;
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['dkcp'];
                     }
                        
                 }
                 elseif($type_detail=="当月毛利累计"&&is_numeric($value)){
                     $dymll=$dyxssrlj;
                     if($product=="防盗门"){
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['fdm'];
                     }
                      
                     elseif($product=="数码产品"){
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['smcp'];
                     }
                     elseif($product=="三代机"){
                        
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['sdj'];
                     }
                     elseif($product=="电控产品"){
                      
                         $data[$keytr]['tr'][$keytd]['value']=$dyxssrlj['dkcp'];
                     }
                     
                     }
                   
                 elseif($type_detail=="当月毛利率"||$type_detail=="当日毛利率"){
                     if($product=="防盗门"){
                        if($drxssr['fdm']>0)
                            $data[$keytr]['tr'][$keytd]['value']="100%";
                     }
                     elseif($product=="数码产品"){
                       if($drxssr['smcp']>0)
                            $data[$keytr]['tr'][$keytd]['value']="100%";
                     }
                     elseif($product=="三代机"){
                       if($drxssr['sdj']>0)
                            $data[$keytr]['tr'][$keytd]['value']="100%";
                     }
                     elseif($product=="电控产品"){
                         if($drxssr['dkcp']>0)
                             $data[$keytr]['tr'][$keytd]['value']="100%";
                     }
                 }
                 
                 elseif($type_detail=="当日销售收入"||$type_detail=="当日毛利"){
                     if($product=="防盗门"){
                        
                             $data[$keytr]['tr'][$keytd]['value']=$drxssr['fdm'];
                     }
                     elseif($product=="数码产品"){
                        
                             $data[$keytr]['tr'][$keytd]['value']=$drxssr['smcp'];
                     }
                     elseif($product=="三代机"){
                         
                             $data[$keytr]['tr'][$keytd]['value']=$drxssr['sdj'];
                     }
                     elseif($product=="电控产品"){
                        
                             $data[$keytr]['tr'][$keytd]['value']=$drxssr['dkcp'];
                     }
                 }
                 
                 elseif($type_detail=="有效结存"){

                     if($product=="防盗门"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$yxjc['fdm'];
                         $yxjc['fdm']+=$temp;
                         //加上期初
                     
                         $yxjc['fdm']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$yxjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$yxjc['smcp'];
                         $yxjc['smcp']+=$temp;
                         //加上期初
                     
                         $yxjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$yxjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$yxjc['sdj'];
                         $yxjc['sdj']+=$temp;
                         //加上期初
                     
                         $yxjc['sdj']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$yxjc['sdj'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$yxjc['dkcp'];
                         $yxjc['dkcp']+=$temp;
                         //加上期初
                     
                         $yxjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$yxjc['dkcp'];
                     }
                              
                 }
                 
                 elseif($type_detail=="无效结存"){
                 
                     if($product=="防盗门"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$wxjc['fdm'];
                         $wxjc['fdm']+=$temp;
                         //加上期初
                          
                         $wxjc['fdm']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$wxjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$wxjc['smcp'];
                         $wxjc['smcp']+=$temp;
                         //加上期初
                          
                         $wxjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$wxjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$wxjc['sdj'];
                         $wxjc['sdj']+=$temp;
                         //加上期初
                          
                         $wxjc['sdj']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$wxjc['sdj'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$wxjc['dkcp'];
                         $wxjc['dkcp']+=$temp;
                         //加上期初
                          
                         $wxjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$wxjc['dkcp'];
                     }
                 
                 }
            
                 elseif($type_detail=="送货结存"){
                      
                     if($product=="防盗门"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$shjc['fdm'];
                         $shjc['fdm']+=$temp;
                         //加上期初
                 
                         $shjc['fdm']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$shjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$shjc['smcp'];
                         $shjc['smcp']+=$temp;
                         //加上期初
                 
                         $shjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$shjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$shjc['sdj'];
                         $shjc['sdj']+=$temp;
                         //加上期初
                 
                         $shjc['sdj']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$shjc['sdj'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$shjc['dkcp'];
                         $shjc['dkcp']+=$temp;
                         //加上期初
                 
                         $shjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$shjc['dkcp'];
                     }
                      
                 }
                 
                 elseif($type_detail=="暂存商品结存"){
                 
                     if($product=="防盗门"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$zcspjc['fdm'];
                         $zcspjc['fdm']+=$temp;
                         //加上期初
                          
                         $zcspjc['fdm']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$zcspjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$zcspjc['smcp'];
                         $zcspjc['smcp']+=$temp;
                         //加上期初
                          
                         $zcspjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$zcspjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$zcspjc['sdj'];
                         $zcspjc['sdj']+=$temp;
                         //加上期初
                          
                         $zcspjc['sdj']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$zcspjc['sdj'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$zcspjc['dkcp'];
                         $zcspjc['dkcp']+=$temp;
                         //加上期初
                          
                         $zcspjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$zcspjc['dkcp'];
                     }
                 
                 }
                 
                 
                 elseif($type_detail=="铺货结存"){
                      
                     if($product=="防盗门"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$phjc['fdm'];
                         $phjc['fdm']+=$temp;
                         //加上期初
                 
                         $phjc['fdm']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$phjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$phjc['smcp'];
                         $phjc['smcp']+=$temp;
                         //加上期初
                 
                         $phjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$phjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$phjc['sdj'];
                         $phjc['sdj']+=$temp;
                         //加上期初
                 
                         $phjc['sdj']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$phjc['sdj'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$phjc['dkcp'];
                         $phjc['dkcp']+=$temp;
                         //加上期初
                 
                         $phjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$phjc['dkcp'];
                     }
                      
                 }
                 
                 elseif($type_detail=="待处理商品结存"){
                 
                     if($product=="防盗门"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$dclspjc['fdm'];
                         $dclspjc['fdm']+=$temp;
                         //加上期初
                          
                         $dclspjc['fdm']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$dclspjc['fdm'];
                     }
                     elseif($product=="数码产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$dclspjc['smcp'];
                         $dclspjc['smcp']+=$temp;
                         //加上期初
                          
                         $dclspjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$dclspjc['smcp'];
                     }
                     elseif($product=="三代机"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$dclspjc['smcp'];
                         $dclspjc['smcp']+=$temp;
                         //加上期初
                          
                         $dclspjc['smcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$dclspjc['smcp'];
                     }
                     elseif($product=="电控产品"){
                         //加上月累计
                         $key1=md5($type_detail.$product);
                         $temp=$yjc["$key1"];
                         $yjc["$key1"]+=$dclspjc['dkcp'];
                         $dclspjc['dkcp']+=$temp;
                         //加上期初
                          
                         $dclspjc['dkcp']+=$qc["$key1"];
                         $data[$keytr]['tr'][$keytd]['value']=$dclspjc['dkcp'];
                     }
                 
                 }
                 
                 
             }
         }
         
         $jsonData['data']=$data;
          
         $json=json_encode($jsonData);
         //计算redis键名
         $now=date("Ymd");
         $key="report-$dept-$now-XSRBLR";
         
         //存入redis
         $result=$redis->set($key,$json);
         $result=$redis->set("report-$dept-$now-XSRBLR-YJC",json_encode($yjc));
         //echo $json;
        if($result)
           echo '{"resultcode":0,"resultmsg":"保存成功"}';
        else 
           echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}'; 
    }
    
    //销售日报查询
 function search(){
        header("Access-Control-Allow-Origin: *");
      
       $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
     $js='{"data":[{"tr":[{"dataType":0,"value":"业务名称","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":""},{"dataType":0,"value":"项目类别","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":"项目类别"},{"dataType":0,"value":"项目名称","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":"项目类别项目名称"},{"dataType":0,"value":"防盗门","rowspan":1,"colspan":1,"product":"防盗门","type":"业务名称","type_detail":"项目类别项目名称"},{"dataType":0,"value":"数码产品","rowspan":1,"colspan":1,"product":"数码产品","type":"业务名称","type_detail":"项目类别项目名称"},{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product":"三代机","type":"业务名称","type_detail":"项目类别项目名称"},{"dataType":0,"value":"电控产品","rowspan":1,"colspan":1,"product":"电控产品","type":"业务名称","type_detail":"项目类别项目名称"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"损益性现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入"},{"dataType":0,"value":"当日销现","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入当日销现"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"损益性现金收入当日销现"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"损益性现金收入当日销现"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"损益性现金收入当日销现"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"损益性现金收入当日销现"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"损益性现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入"},{"dataType":0,"value":"报废收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入报废收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"损益性现金收入报废收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"损益性现金收入报废收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"损益性现金收入报废收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"损益性现金收入报废收入"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"损益性现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入"},{"dataType":0,"value":"运费收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入运费收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"损益性现金收入运费收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"损益性现金收入运费收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"损益性现金收入运费收入"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"损益性现金收入运费收入"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"损益性现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入"},{"dataType":4,"value":"其他收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"损益性现金收入其他收入","child":{"child_data":[{"project":"项目","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"损益性现金收入其他收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"损益性现金收入其他收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"损益性现金收入其他收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"损益性现金收入其他收入"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":4,"value":"收回欠款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入收回欠款","child":{"child_data":[{"customname":"客户名称","class":"类别","newarrear":"新增欠款","recoverarrear":"收回欠款"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入收回欠款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":0,"value":"职工还借","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入职工还借"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":0,"value":"收押金","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入收押金"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入收押金"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入收押金"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入收押金"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入收押金"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":0,"value":"增加预收款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入增加预收款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":0,"value":"增加暂存款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入增加暂存款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":4,"value":"经营部资金调入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入经营部资金调入","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":4,"value":"代收款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入代收款","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入代收款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入代收款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入代收款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入代收款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入"},{"dataType":0,"value":"维修费","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金收入维修费"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金收入维修费"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金收入维修费"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金收入维修费"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金收入维修费"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"费用类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"费用类现金支出"},{"dataType":5,"value":"经营费用","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"费用类现金支出经营费用","child":{"child_data":[{"projectclass":"项目类别","projectname":"项目名称","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"},{"projectclass":"经营费","projectname":"办公费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"财务费用","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"差旅费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"差旅费补助","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"调拨费用","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"返利","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"其他","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"其他运费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"市内交通费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"水电气费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"税金","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"维修费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"销售运费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"行政管理费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"业务招待费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"邮电费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"经营费","projectname":"租赁费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"车辆费","projectname":"车杂费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"车辆费","projectname":"车修费","door":0,"digital":0,"genera":0,"electronic":0},{"projectclass":"车辆费","projectname":"车油费","door":0,"digital":0,"genera":0,"electronic":0}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"费用类现金支出经营费用"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"费用类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"费用类现金支出"},{"dataType":5,"value":"车辆费用","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"费用类现金支出车辆费用","child":{"child_data":[{"projectclass":"项目类别","projectname":"项目名称","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"费用类现金支出车辆费用"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":5,"value":"资金调成总","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出资金调成总","child":{"child_data":[{"otherdepartment":"对方部门","accountname":"户名","accountbank":"开户行","amount":"金额"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出资金调成总"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":5,"value":"资金调经营部","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出资金调经营部"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"职工借款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出职工借款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"支押金","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出支押金"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支押金"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出支押金"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出支押金"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出支押金"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"支付职工浮动薪酬","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"减少预收款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出减少预收款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"减少暂存款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出减少暂存款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":5,"value":"代支采购货款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出代支采购货款","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出代支采购货款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":5,"value":"代支其他部门","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出代支其他部门","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出代支其他部门"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"增加固定资产","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出增加固定资产"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"增加低易品与待摊费用","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"支付工资","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出支付工资"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"支付预提","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出支付预提"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"支付外购款","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出支付外购款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付外购款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出支付外购款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付外购款"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出支付外购款"}]},{"tr":[{"dataType":0,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出"},{"dataType":0,"value":"待处理","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"资产类现金支出待处理"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"现金业务","type_detail":"资产类现金支出待处理"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"现金业务","type_detail":"资产类现金支出待处理"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"现金业务","type_detail":"资产类现金支出待处理"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"现金业务","type_detail":"资产类现金支出待处理"}]},{"tr":[{"dataType":0,"value":"应收款","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":""},{"dataType":0,"value":"应收款","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":"应收款"},{"dataType":5,"value":"新增","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":"应收款新增"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"应收款","type_detail":"应收款新增"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"应收款","type_detail":"应收款新增"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"应收款","type_detail":"应收款新增"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"应收款","type_detail":"应收款新增"}]},{"tr":[{"dataType":6,"value":"现金业务","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":""},{"dataType":6,"value":"现金结存","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"现金结存"},{"dataType":6,"value":"现金结存","rowspan":1,"colspan":1,"product":"","type":"现金业务","type_detail":"现金结存现金结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":"4","product":"防盗门","type":"现金业务","type_detail":"现金结存现金结存"}]},{"tr":[{"dataType":6,"value":"应收款","rowspan":1,"colspan":"2","product":"","type":"应收款","type_detail":""},{"dataType":6,"value":"应收款结存","rowspan":1,"colspan":1,"product":"","type":"应收款","type_detail":"应收款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"应收款","type_detail":"应收款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"应收款","type_detail":"应收款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"应收款","type_detail":"应收款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"应收款","type_detail":"应收款结存"}]},{"tr":[{"dataType":6,"value":"预收账款结存","rowspan":1,"colspan":"3","product":"","type":"预收账款结存","type_detail":""},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"预收账款结存","type_detail":"预收账款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"预收账款结存","type_detail":"预收账款结存"}]},{"tr":[{"dataType":6,"value":"暂存款结存","rowspan":1,"colspan":"3","product":"","type":"暂存款结存","type_detail":""},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"暂存款结存","type_detail":"暂存款结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"暂存款结存","type_detail":"暂存款结存"}]},{"tr":[{"dataType":6,"value":"销售情况","rowspan":"8","colspan":1,"product":"","type":"销售情况","type_detail":""},{"dataType":6,"value":"当月","rowspan":"4","colspan":1,"product":"","type":"销售情况","type_detail":"当月"},{"dataType":6,"value":"销售收入累计","rowspan":1,"colspan":1,"product":"","type":"销售情况","type_detail":"当月销售收入累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"销售情况","type_detail":"当月销售收入累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"销售情况","type_detail":"当月销售收入累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"销售情况","type_detail":"当月销售收入累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"销售情况","type_detail":"当月销售收入累计"}]},{"tr":[{"dataType":6,"value":"销售成本累计","rowspan":1,"colspan":1,"product":"","type":"销售成本累计","type_detail":"当月销售成本累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"销售成本累计","type_detail":"当月销售成本累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"销售成本累计","type_detail":"当月销售成本累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"销售成本累计","type_detail":"当月销售成本累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"销售成本累计","type_detail":"当月销售成本累计"}]},{"tr":[{"dataType":6,"value":"毛利累计","rowspan":1,"colspan":1,"product":"","type":"毛利累计","type_detail":"当月毛利累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"毛利累计","type_detail":"当月毛利累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"毛利累计","type_detail":"当月毛利累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"毛利累计","type_detail":"当月毛利累计"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"毛利累计","type_detail":"当月毛利累计"}]},{"tr":[{"dataType":6,"value":"毛利率","rowspan":1,"colspan":1,"product":"","type":"毛利率","type_detail":"当月毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"毛利率","type_detail":"当月毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"毛利率","type_detail":"当月毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"毛利率","type_detail":"当月毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"毛利率","type_detail":"当月毛利率"}]},{"tr":[{"dataType":6,"value":"当日","rowspan":"4","colspan":1,"product":"","type":"当日","type_detail":"当日销售收入"},{"dataType":6,"value":"当日销售收入","rowspan":1,"colspan":1,"product":"","type":"当日","type_detail":"当日销售收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"当日","type_detail":"当日销售收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"当日","type_detail":"当日销售收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"当日","type_detail":"当日销售收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"当日","type_detail":"当日销售收入"}]},{"tr":[{"dataType":6,"value":"当日销售成本","rowspan":1,"colspan":1,"product":"","type":"销售情况 ","type_detail":"当日销售成本"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"销售情况 ","type_detail":"当日销售成本"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"销售情况 ","type_detail":"当日销售成本"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"销售情况 ","type_detail":"当日销售成本"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"销售情况 ","type_detail":"当日销售成本"}]},{"tr":[{"dataType":6,"value":"当日毛利","rowspan":1,"colspan":1,"product":"","type":"销售情况 ","type_detail":"当日毛利"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"销售情况 ","type_detail":"当日毛利"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"销售情况 ","type_detail":"当日毛利"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"销售情况 ","type_detail":"当日毛利"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"销售情况 ","type_detail":"当日毛利"}]},{"tr":[{"dataType":6,"value":"当日毛利率","rowspan":1,"colspan":1,"product":"","type":"销售情况 ","type_detail":"当日毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"销售情况 ","type_detail":"当日毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"销售情况 ","type_detail":"当日毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"销售情况 ","type_detail":"当日毛利率"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"销售情况 ","type_detail":"当日毛利率"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入"},{"dataType":4,"value":"外购入库","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入外购入库","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支入外购入库"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支入外购入库"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支入外购入库"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支入外购入库"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入"},{"dataType":4,"value":"调拨收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入调拨收入","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支入调拨收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支入调拨收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支入调拨收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支入调拨收入"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入"},{"dataType":0,"value":"送货收回","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入送货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支入送货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支入送货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支入送货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支入送货收回"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入"},{"dataType":0,"value":"减少铺货商品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入减少铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支入减少铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支入减少铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支入减少铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支入减少铺货商品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入"},{"dataType":0,"value":"减少待处理商品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支入减少待处理商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支入减少待处理商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支入减少待处理商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支入减少待处理商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支入减少待处理商品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收入"},{"dataType":0,"value":"增加暂存商品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收入增加暂存商品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收入"},{"dataType":0,"value":"调价升值","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收入调价升值"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收入"},{"dataType":0,"value":"盘盈","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收入盘盈"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出"},{"dataType":0,"value":"销售成本","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出销售成本"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收出销售成本"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收出销售成本"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收出销售成本"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收出销售成本"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出"},{"dataType":5,"value":"调拨支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出调拨支出","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收出调拨支出"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收出调拨支出"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收出调拨支出"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收出调拨支出"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出"},{"dataType":0,"value":"换货支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出换货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收出换货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收出换货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收出换货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收出换货支出"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出"},{"dataType":0,"value":"送货支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出送货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收出送货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收出送货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收出送货支出"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收出送货支出"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效收出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出"},{"dataType":0,"value":"增加铺货商品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效收出增加铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效收出增加铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效收出增加铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效收出增加铺货商品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效收出增加铺货商品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出"},{"dataType":0,"value":"增加待处理商品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支出增加待处理商品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出"},{"dataType":0,"value":"减少暂存商品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支出减少暂存商品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出"},{"dataType":0,"value":"报废支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支出报废支出"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出"},{"dataType":0,"value":"调价降值","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支出调价降值"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"有效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出"},{"dataType":0,"value":"盘亏","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"有效支出盘亏"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入"},{"dataType":4,"value":"调拨收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入调拨收入","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效收入调拨收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效收入调拨收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效收入调拨收入"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效收入调拨收入"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入"},{"dataType":0,"value":"换货收回","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入换货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效收入换货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效收入换货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效收入换货收回"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效收入换货收回"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入"},{"dataType":0,"value":"增加暂存品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入增加暂存品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效收入增加暂存品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效收入增加暂存品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效收入增加暂存品"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效收入增加暂存品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入"},{"dataType":0,"value":"调价升值","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效收入调价升值"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效收入调价升值"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效收入","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入"},{"dataType":0,"value":"盘盈","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效收入盘盈"},{"dataType":2,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效收入盘盈"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出"},{"dataType":5,"value":"调拨支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出调拨支出","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","digital":"数码产品","genera":"三代机","electronic":"电控产品"}]}},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效支出调拨支出"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效支出调拨支出"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效支出调拨支出"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效支出调拨支出"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出"},{"dataType":0,"value":"报废支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效支出报废支出"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效支出报废支出"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出"},{"dataType":0,"value":"减少暂存品","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出减少暂存品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效支出减少暂存品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效支出减少暂存品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效支出减少暂存品"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效支出减少暂存品"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出"},{"dataType":0,"value":"调价降值","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效支出调价降值"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效支出调价降值"}]},{"tr":[{"dataType":0,"value":"商品业务","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":0,"value":"无效支出","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出"},{"dataType":0,"value":"盘亏","rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"无效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效支出盘亏"},{"dataType":3,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效支出盘亏"}]},{"tr":[{"dataType":6,"value":"商品业务","rowspan":"6","colspan":1,"product":"","type":"商品业务","type_detail":""},{"dataType":6,"value":"有效结存","rowspan":1,"colspan":"2","product":"","type":"商品业务","type_detail":"有效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"","type":"商品业务","type_detail":"有效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"有效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"有效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"有效结存"}]},{"tr":[{"dataType":6,"value":"送货结存","rowspan":1,"colspan":"2","product":"","type":"商品业务","type_detail":"送货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"送货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"送货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"送货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"送货结存"}]},{"tr":[{"dataType":6,"value":"无效结存","rowspan":1,"colspan":"2","product":"","type":"商品业务","type_detail":"无效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"无效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"无效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"无效结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"无效结存"}]},{"tr":[{"dataType":6,"value":"暂存商品结存","rowspan":1,"colspan":"2","product":"","type":"商品业务","type_detail":"暂存商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"暂存商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"暂存商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"暂存商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"暂存商品结存"}]},{"tr":[{"dataType":6,"value":"铺货结存","rowspan":1,"colspan":"2","product":"","type":"商品业务","type_detail":"铺货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"铺货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"铺货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"铺货结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"铺货结存"}]},{"tr":[{"dataType":6,"value":"待处理商品结存","rowspan":1,"colspan":"2","product":"","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"数码产品","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"商品业务","type_detail":"待处理商品结存"},{"dataType":6,"value":0,"rowspan":1,"colspan":1,"product":"电控产品","type":"商品业务","type_detail":"待处理商品结存"}]}]} 
     ';
      $now=date("Ymd");
        $dept="admin";
        $redis->set("report-$dept-XSRBLR",$js);
        $json=$redis->get("report-$dept-$now-XSRBLR");
         if($json!=null)
            echo $json;
         else     
        echo $redis->get("report-$dept-XSRBLR");  
 
    }
    
    public function test(){
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
        $now=date("Ymd");
        $month=date("Ym");
        $dept="admin";
        $val=json_decode($redis->get("report-$dept-$month-XSRBQC-YJC"),true);
       
       print_r($val);
      
      
    }
  
}


?>