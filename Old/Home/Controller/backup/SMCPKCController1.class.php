<?php
namespace Home\Controller;

use Think\Controller;
class SMCPKCController extends Controller
{
    //数码产品库存日报提交
    public function submit(){
     
        header("Access-Control-Allow-Origin: *");
        $jsonData=json_decode(file_get_contents("php://input"),true); 
        $data=$jsonData['data'];
        
        //redis时间键
        $day=date("Ymd");
        $month=date("Ym");
        
        $dept="admin";
      
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
        
       
       //查询当前月结存
         $temp=$day-1;//前一天
          $yjc=json_decode($redis->get("report-$dept-$temp-SMCPKC-YJC"),true);
        
        //获取期初数据
        $SMCPKCQC=json_decode($redis->get("report-admin-$month-SMCPKCQC"),true);
        $qcjs=$SMCPKCQC['QC'];
        //print_r($qcjs);
       //三代机小计计算结果
       $sdjxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //地面波小计计算结果
       $dmbxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //一代机小计计算结果
       $ydjxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0,);
       //DVB小计计算结果
       $DVBxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //高频头小计计算结果
       $gptxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //天线小计计算结果
       $txxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //充电器小计计算结果
       $cdqxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //其他产品小计计算结果
       $qtxj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
       //合计计算结果
       $hj=array("yxdbsr"=>0,"yxwgsr"=>0,"yxxszc"=>0,"yxdbzc"=>0,"yxhhzc"=>0,"yxzcsp"=>0,
           "yxqtzc"=>0,"yxjc"=>0,"wxhhsh"=>0,"wxdbzc"=>0,"wxqtzc"=>0,"wxjc"=>0);
      
       //遍历每一个单元格计算列数据
        foreach($data as $keytr=>$tr){
           $tr=$tr['tr'];
           $yxjc=0;//每一行的有效结存
           $wxjc=0;//每一行的无效结存
            foreach($tr as $keytd=>$td){
                $product_type=trim($td["product_type"]);
                $product=trim($td["product"]);
                $type=trim($td["type"]);
                $type_detail=trim($td["type_detail"]);
                $value=$td["value"];
                $key1="";
                $qc=0;
                $key1=md5($product_type.$product.$type_detail);
                $qc=$qcjs["$key1"];
                $yjcTemp=$yjc["$key1"];
                //计算当前表的有效无效
                if($type=="有效"&&$type_detail!="有效结存"){
                    if($type_detail=="调拨收入"||$type_detail=="外购收入"||$type_detail=="暂存商品收入/支出")
                     $yxjc+=$value;
                     else 
                         $yxjc-=$value;
                }
                if($type_detail=="有效结存"){
                    $yjc["$key1"]+=$yxjc;
                    $yxjc+=$yjcTemp;
                    $yxjc+=$qc;
                }
                if($type_detail=="无效结存"){
                    $yjc["$key1"]+=$wxjc;
                    $wxjc+=$yjcTemp;
                     $wxjc+=$qc;
                }
                if($type=="无效"&&$type_detail!="无效结存"){
                     if($type_detail=="换货收回")
                        $wxjc+=$value;
                     else 
                         $wxjc-=$value;               
                }
      
                //更新当月有效结存
               // $redis->set($month.$product_type.$product."dyyxjc",$yx);
                //三代机小计计算
                if($product_type=="三代机"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $sdjxj['yxdbsr']+=$value;
                        elseif($type_detail=="外购收入")
                            $sdjxj['yxwgsr']+=$value; 
                        elseif($type_detail=="销售支出")
                            $sdjxj['yxxszc']+=$value;
                        elseif($type_detail=="调拨支出")
                            $sdjxj['yxdbzc']+=$value;
                        elseif($type_detail=="换货支出")
                             $sdjxj['yxhhzc']+=$value;
                        elseif($type_detail=="暂存商品收入/支出")
                             $sdjxj['yxzcsp']+=$value;
                        elseif($type_detail=="其他支出")
                             $sdjxj['yxqtzc']+=$value; 
                        elseif($type_detail=="有效结存")
                             $sdjxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $sdjxj['wxhhsh']+=$value;
                        elseif($type_detail=="调拨支出")
                            $sdjxj['wxdbzc']+=$value;
                        elseif($type_detail=="其他支出")
                            $sdjxj['wxqtzc']+=$value;
                        elseif($type_detail=="无效结存")
                             $sdjxj['wxjc']+=$wxjc;
                    }
                }
                
                //地面波小计计算
                elseif($product_type=="地面波"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $dmbxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $dmbxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $dmbxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $dmbxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $dmbxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $dmbxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $dmbxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $dmbxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $dmbxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $dmbxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $dmbxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $dmbxj['wxjc']+=$wxjc;
                    }
                }
                
                //一代机小计计算
                elseif($product_type=="一代机"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $ydjxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $ydjxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $ydjxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $ydjxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $ydjxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $ydjxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $ydjxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $ydjxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $ydjxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $ydjxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $ydjxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $dmbxj['wxjc']+=$wxjc;
                    }  
                }   
                
                //DVB小计计算
                elseif($product_type=="DVB"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $DVBxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $DVBxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $DVBxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $DVBxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $DVBxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $DVBxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $DVBxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $DVBxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $DVBxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $DVBxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $DVBxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $DVBxj['wxjc']+=$wxjc;
                    }
                }
                
                //高频头小计计算
                elseif($product_type=="高频头"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $gptxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $gptxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $gptxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $gptxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $gptxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $gptxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $gptxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $gptxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $gptxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $gptxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $gptxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $DVBxj['wxjc']+=$wxjc;
                    }
                }
                
                //天线小计计算
                elseif($product_type=="天线"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $txxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $txxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $txxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $txxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $txxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $txxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $txxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $txxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $txxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $txxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $txxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $txxj['wxjc']+=$wxjc;
                    }
                }
                
                //充电器小计计算
                elseif($product_type=="充电器"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $cdqxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $cdqxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $cdqxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $cdqxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $cdqxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $cdqxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $cdqxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $cdqxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $cdqxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $cdqxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $cdqxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $cdqxj['wxjc']+=$wxjc;
                    }
                }
                
                //其他产品小计计算
                elseif($product_type=="其他产品"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $qtxj['yxdbsr']+=$value;
                            elseif($type_detail=="外购收入")
                            $qtxj['yxwgsr']+=$value;
                            elseif($type_detail=="销售支出")
                            $qtxj['yxxszc']+=$value;
                            elseif($type_detail=="调拨支出")
                            $qtxj['yxdbzc']+=$value;
                            elseif($type_detail=="换货支出")
                            $qtxj['yxhhzc']+=$value;
                            elseif($type_detail=="暂存商品收入/支出")
                            $qtxj['yxzcsp']+=$value;
                            elseif($type_detail=="其他支出")
                            $qtxj['yxqtzc']+=$value;
                            elseif($type_detail=="有效结存")
                            $qtxj['yxjc']+=$yxjc;
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $qtxj['wxhhsh']+=$value;
                            elseif($type_detail=="调拨支出")
                            $qtxj['wxdbzc']+=$value;
                            elseif($type_detail=="其他支出")
                            $qtxj['wxqtzc']+=$value;
                            elseif($type_detail=="无效结存")
                            $qtxj['wxjc']+=$wxjc;
                    }
                }
                //更新有效结存
                $temp_type_detail=$data[$keytr]['tr'][$keytd]['type_detail'];
                if(trim($temp_type_detail)=="有效结存")
                    $data[$keytr]['tr'][$keytd]['value']=$yxjc;
                //更新无效结存
                elseif(trim($temp_type_detail)=="无效结存")
                     $data[$keytr]['tr'][$keytd]['value']=$wxjc;    
            }
        }
        
        $jsonData['data']=$data;
        //更新小计数据
        foreach($data as $keytr=>$tr){
            $tr=$tr['tr'];
            foreach($tr as $keytd=>$td){
                $product_type=trim($td["product_type"]);
                $type=trim($td["type"]);
                $type_detail=trim($td["type_detail"]);
                $value=trim($td["value"]);
                
                //更新三代机小计
                if($product_type=="三代机小计"){
                    if( $sdjxj['yxjc'])
                        echo $sdjxj['yxjc'];
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxqtzc'];
                            elseif($type_detail=="有效结存")                              
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxjc'];
                            
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                 $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxqtzc'];
                            elseif($type_detail=="无效结存") 
                                 $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxjc'];
                    }
                }
                
                //更新地面波小计
                if($product_type=="地面波小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['yxjc'];
                            
                
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['wxqtzc'];
                            elseif($type_detail=="无效结存") 
                                $data[$keytr]['tr'][$keytd]['value']=$dmbxj['wxjc'];
                    }
                }
                
                //更新一代机小计
                if($product_type=="一代机小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['yxjc'];
                
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['wxqtzc'];
                            elseif($type_detail=="无效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$ydjxj['wxjc'];
                    }
                }
                
                //更新DVB小计
                if($product_type=="DVB小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['yxjc'];
                            
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['wxqtzc'];
                            elseif($type_detail=="无效结存") 
                                $data[$keytr]['tr'][$keytd]['value']=$DVBxj['wxjc'];
                            
                    }
                }
                
                //更新高频头小计
                if($product_type=="高频头小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['yxjc'];
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['wxqtzc'];
                            elseif($type_detail=="无效结存") 
                                $data[$keytr]['tr'][$keytd]['value']=$gptxj['wxjc'];
                    }
                }
                
                //更新天线小计
                if($product_type=="天线小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['yxjc'];
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['wxqtzc'];
                            elseif($type_detail=="无效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$txxj['wxjc'];
                    }
                }
                
                //更新充电器小计
                if($product_type=="充电器小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                 $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                 $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['yxjc'];
                
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['wxqtzc'];
                            elseif($type_detail=="无效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$cdqxj['wxjc'];
                    }
                }
                
                //更新其他产品小计
                if($product_type=="其他产品小计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['yxjc'];
                
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['wxqtzc'];
                            elseif($type_detail=="无效结存")
                                $data[$keytr]['tr'][$keytd]['value']=$qtxj['wxjc'];
                    }
                }
                
                //更新合计
                if($product_type=="合计"){
                    if($type=="有效"){
                        if($type_detail=="调拨收入")
                            $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxdbsr']+$dmbxj['yxdbsr']+$ydjxj['yxdbsr']
                            +$DVBxj['yxdbsr']+$gptxj['yxdbsr']+$txxj['yxdbsr']+$cdqxj['yxdbsr']+$qtxj['yxdbsr'];
                            elseif($type_detail=="外购收入")
                            $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxwgsr']+$dmbxj['yxwgsr']+$ydjxj['yxwgsr']
                            +$DVBxj['yxwgsr']+$gptxj['yxwgsr']+$txxj['yxwgsr']+$cdqxj['yxwgsr']+$qtxj['yxwgsr'];
                            elseif($type_detail=="销售支出")
                            $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxxszc']+$dmbxj['yxxszc']+$ydjxj['yxxszc']
                            +$DVBxj['yxxszc']+$gptxj['yxxszc']+$txxj['yxxszc']+$cdqxj['yxxszc']+$qtxj['yxxszc'];
                            elseif($type_detail=="调拨支出")
                            $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxdbzc']+$dmbxj['yxdbzc']+$ydjxj['yxdbzc']
                            +$DVBxj['yxdbzc']+$gptxj['yxdbzc']+$txxj['yxdbzc']+$cdqxj['yxdbzc']+$qtxj['yxdbzc'];
                            elseif($type_detail=="换货支出")
                             $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxhhzc']+$dmbxj['yxhhzc']+$ydjxj['yxhhzc']
                            +$DVBxj['yxhhzc']+$gptxj['yxhhzc']+$txxj['yxhhzc']+$cdqxj['yxhhzc']+$qtxj['yxhhzc'];
                            elseif($type_detail=="暂存商品收入/支出")
                             $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxzcsp']+$dmbxj['yxzcsp']+$ydjxj['yxzcsp']
                            +$DVBxj['yxzcsp']+$gptxj['yxzcsp']+$txxj['yxzcsp']+$cdqxj['yxzcsp']+$qtxj['yxzcsp'];
                            elseif($type_detail=="其他支出")
                             $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxqtzc']+$dmbxj['yxqtzc']+$ydjxj['yxqtzc']
                            +$DVBxj['yxqtzc']+$gptxj['yxqtzc']+$txxj['yxqtzc']+$cdqxj['yxqtzc']+$qtxj['yxqtzc'];
                            elseif($type_detail=="有效结存")
                            $data[$keytr]['tr'][$keytd]['value']=$sdjxj['yxjc']+$dmbxj['yxjc']+$ydjxj['yxjc']
                            +$DVBxj['yxjc']+$gptxj['yxjc']+$txxj['yxjc']+$cdqxj['yxjc']+$qtxj['yxjc'];
                
                    }
                    elseif($type=="无效"){
                        if($type_detail=="换货收回")
                            $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxhhsh']+$dmbxj['wxhhsh']+$ydjxj['wxhhsh']
                            +$DVBxj['wxhhsh']+$gptxj['wxhhsh']+$txxj['wxhhsh']+$cdqxj['wxhhsh']+$qtxj['wxhhsh'];
                            elseif($type_detail=="调拨支出")
                             $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxdbzc']+$dmbxj['wxdbzc']+$ydjxj['wxdbzc']
                            +$DVBxj['wxdbzc']+$gptxj['wxdbzc']+$txxj['wxdbzc']+$cdqxj['wxdbzc']+$qtxj['wxdbzc'];
                            elseif($type_detail=="其他支出")
                             $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxqtzc']+$dmbxj['wxqtzc']+$ydjxj['wxqtzc']
                            +$DVBxj['wxqtzc']+$gptxj['wxqtzc']+$txxj['wxqtzc']+$cdqxj['wxqtzc']+$qtxj['wxqtzc'];
                            elseif($type_detail=="无效结存")
                             $data[$keytr]['tr'][$keytd]['value']=$sdjxj['wxjc']+$dmbxj['wxjc']+$ydjxj['wxjc']
                            +$DVBxj['wxjc']+$gptxj['wxjc']+$txxj['wxjc']+$cdqxj['wxjc']+$qtxj['wxjc'];
                    }
                }
                
            }
        }
        
        
        $jsonData['data']=$data;
   
        $json=json_encode($jsonData);
        //计算redis键名
        $now=date("Ymd");
        $key="report-$dept-$now-SMCPKC";
        
        //存入redis
        $result=$redis->set($key,$json);
        $result=$redis->set("report-$dept-$now-SMCPKC-YJC",json_encode($yjc));
       // echo $redis->get("report-$dept-$now-SMCPKC");
       //echo $json;
        if($result)
            echo "{'resultcode':0,'resultmsg':'保存成功'}";
        else 
            echo "{'resultcode':0,'resultmsg':'保存失败'}"; 
        
    }
    
    //数码产品库存日报查询
    function search(){
        header("Access-Control-Allow-Origin: *");
        $js='{"data":[{"tr":[{"dataType":0,"value":"数码产品库存表","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"名称","rowspan":1,"colspan":"3","product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"有效","rowspan":1,"colspan":"8","product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"类型","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"产品型号","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"产品规格","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"外购收入","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"销售支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"换货支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"暂存商品收入/支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"有效结存","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"换货收回","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效结存","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 三代机小计","rowspan":1,"colspan":"3","product_type":"三代机小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":"23","colspan":1,"product_type":"三代机","product":"","type":"","type_detail":""},{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"三合一（大机）","rowspan":1,"colspan":1,"product_type":"","product":"三合一（大机）","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"三合一（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海星（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海星（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"金品（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"金品（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海旋风（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海旋风（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海霸王（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海神号（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海神号（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海视（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海视（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海天（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海天（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海思（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海思（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"科斯特A款（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"科斯特A款（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"梦幻（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"梦幻（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"飓风（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"飓风（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"星光（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星光（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"星火（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星火（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"星空（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"星空（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"创新号（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"创新号（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他定位机（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他定位机（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"其他定位机（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无卡定位机（大机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（大机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无卡定位机（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定位机（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"海霸王-D（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"海霸王-D（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无卡定做机（小机）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"无卡定做机（小机）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"智能卡","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"智能卡","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"三代机","product":"智能卡","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"地面波","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 地面波小计","rowspan":1,"colspan":"3","product_type":"地面波小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"地面波","rowspan":1,"colspan":1,"product_type":"地面波","product":"三合一（大机）","type":"","type_detail":""},{"dataType":0,"value":"地面波","rowspan":1,"colspan":1,"product_type":"","product":"三合一（大机）","type":"","type_detail":""},{"dataType":0,"value":"地面波","rowspan":1,"colspan":1,"product_type":"","product":"地面波","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"地面波","product":"地面波","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 一代机小计","rowspan":1,"colspan":"3","product_type":"一代机小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机","rowspan":"9","colspan":1,"product_type":"一代机","product":"地面波","type":"","type_detail":""},{"dataType":0,"value":"一代机双","rowspan":1,"colspan":1,"product_type":"","product":"地面波","type":"","type_detail":""},{"dataType":0,"value":"琦鑫仿澜起（Mxv）","rowspan":1,"colspan":1,"product_type":"","product":"琦鑫仿澜起（Mxv）","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mxv）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机双","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"琦鑫仿澜起（Mk）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫仿澜起（Mk）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机双","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"一代机双中天（Mk）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机双中天（Mk）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机单","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"琦鑫（Jx）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"琦鑫（Jx）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机单","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"中天（Jk）","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"中天（Jk）","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"其他","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他方案(QT)","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"其他方案(QT)","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"一代机抽屉","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机抽屉","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"一代机接口板","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机接口板","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"一代机","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"一代机主板","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"一代机","product":"一代机主板","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"DVB","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" DVB小计","rowspan":1,"colspan":"3","product_type":"DVB小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"DVB","rowspan":1,"colspan":1,"product_type":"DVB","product":"琦鑫仿澜起（Mxv）","type":"","type_detail":""},{"dataType":0,"value":"DVB","rowspan":1,"colspan":1,"product_type":"","product":"琦鑫仿澜起（Mxv）","type":"","type_detail":""},{"dataType":0,"value":"DVB","rowspan":1,"colspan":1,"product_type":"","product":"DVB","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"DVB","product":"DVB","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"高频头","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 高频头小计","rowspan":1,"colspan":"3","product_type":"高频头小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"高频头","rowspan":1,"colspan":1,"product_type":"高频头","product":"DVB","type":"","type_detail":""},{"dataType":0,"value":"高频头","rowspan":1,"colspan":1,"product_type":"","product":"DVB","type":"","type_detail":""},{"dataType":0,"value":"高频头","rowspan":1,"colspan":1,"product_type":"","product":"高频头","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"高频头","product":"高频头","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"天线","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 天线小计","rowspan":1,"colspan":"3","product_type":"天线小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"天线","rowspan":"4","colspan":1,"product_type":"天线","product":"高频头","type":"","type_detail":""},{"dataType":0,"value":"天线","rowspan":1,"colspan":1,"product_type":"","product":"高频头","type":"","type_detail":""},{"dataType":0,"value":"0.35天线","rowspan":1,"colspan":1,"product_type":"","product":"0.35天线","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.35天线","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"天线","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"0.45天线","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"0.45天线","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"天线","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"自产天线","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"自产天线","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"天线","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"外购天线","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"天线","product":"外购天线","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"充电器","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 充电器小计","rowspan":1,"colspan":"3","product_type":"充电器小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"充电器","rowspan":"2","colspan":1,"product_type":"充电器","product":"0.35天线","type":"","type_detail":""},{"dataType":0,"value":"充电器","rowspan":1,"colspan":1,"product_type":"","product":"0.35天线","type":"","type_detail":""},{"dataType":0,"value":"充电器","rowspan":1,"colspan":1,"product_type":"","product":"充电器","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"充电器","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"其他电控","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他电控","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"充电器","product":"其他电控","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"其他产品","rowspan":1,"colspan":"15","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":" 其他产品小计","rowspan":1,"colspan":"3","product_type":"其他产品小计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品小计","product":"","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"其他产品","rowspan":"5","colspan":1,"product_type":"其他产品","product":"充电器","type":"","type_detail":""},{"dataType":0,"value":"充电宝","rowspan":1,"colspan":1,"product_type":"","product":"充电器","type":"","type_detail":""},{"dataType":0,"value":"充电宝","rowspan":1,"colspan":1,"product_type":"","product":"充电宝","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"充电宝","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"网络播放器","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"网络播放器","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"网络播放器","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"两轮电动车","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"两轮电动车","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"两轮电动车","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"三轮电动车","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"三轮电动车","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"三轮电动车","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"电池","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"电池","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"外购收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"销售支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"换货支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"有效","type_detail":"有效结存 "},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"无效","type_detail":"换货收回"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"无效","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"其他产品","product":"电池","type":"无效","type_detail":"无效结存"}]},{"tr":[{"dataType":0,"value":"合计","rowspan":1,"colspan":"3","product_type":"合计","product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"外购收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"销售支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"暂存商品收入/支出 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"有效","type_detail":"有效结存 "},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"无效","type_detail":"换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"无效","type_detail":"调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"无效","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product_type":"合计","product":"","type":"无效","type_detail":"无效结存"}]}]}';
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
        $now=date("Ymd");
        $dept="admin";
        $key="report-$dept";
        $redis->set($key,$js);
        $json=$redis->get("report-$dept-$now-SMCPKC");
         if($json!=null)
            echo $json;
        else   
        echo $redis->get($key); 
 
    }
    
    public function test(){
        $redis = new \Redis();
        $redis->connect("192.111.111.4","6379");
        $now=date("Ymd");
        $dept="admin";
        $val=json_decode($redis->get("report-$dept-$now-SMCPKC-YJC"),true);
       
       print_r($val);
    }
  
    
}

?>