<?php
namespace Home\Controller;

use Think\Controller\RestController;
require_once 'XSRBUtil.php';
//防盗门库存汇总表
class FDMKCHZController extends RestController{
    /**
     * 查询防盗门库存表汇总数据
     * @param string $token
     * @param number $page
     * @param number $pageSize
     * @param boolean $flag
     * 优化部分：先查询出结存然后在循环结存数据更新其他部分（目前是先查询其他数据更新结存）
     */
    public function search($token='',$startDate='',$endDate='',$page=1,$pageSize=1,$deptId='',$pId='',$flag=false){
        set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo&&!$flag)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id']; 
        //上级部门id
        $pid=$userinfo['pid'];
        if($deptId&&$pId&&$flag){
            $dept_id=$deptId;
            $pid=$pId;
        }
        /* $dept_id=25;
        $pid=22; */
        $startDate=empty($startDate)?date("Y-m-d"):$startDate;
        $endDate=$startDate;
        //响应数据
        $resposeData=array();
        
        //根据权限查询分页数量
        $sql_total="select count(*) as total from xsrb_department where 1=1 ";
        //查询待统计的部门（销售非生产部分）的id，如登陆身份是片区则需要查询该片区下面所有销售部门的汇总数据如是总部则查询所有部门
        $sql_depts="select id,dname from xsrb_department where 1=1 ";
        //如果为总部查询所有部门
        $offset=$page-1;
        if($pid==0){
            /* $sql_total.=" and id!=1 and pid!=1";
            $sql_depts.=" and id!=1 and pid!=1 limit $offset,$pageSize"; */
            $sql_total.=" and pid!=1";
            $sql_depts.=" and pid!=1 ORDER BY pid DESC,qt1 limit $offset,$pageSize";
        }
        //如果为片区则查出该片区下的所有部门
        elseif($pid==1){
            $sql_total.=" and pid='$dept_id'  ";
            $sql_depts.=" and pid='$dept_id' ORDER BY pid DESC,qt1 limit $offset,$pageSize";
        }
        //具体某个部门的数据
        else{
            $sql_total.=" and id='$dept_id' ";
            $sql_depts.=" and id='$dept_id' ";
        }
        $depts=M()->query($sql_depts);
        $total=M()->query($sql_total);
        $total=$total[0]['total'];
        if($total<$pageSize){
            $total=1;
        }  
        elseif($total%$pageSize==0){
            $total=$total/$pageSize;
        }   
        else{
            $total=$total/$pageSize+1;
        }   
        $total=(int)$total;
        //表头部分
        $json_head=[
            //第一行
            [
                ["value"=>"","rowspan"=>1,"colspan"=>18]
            ],
            //第二行
            [
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"有效商品","rowspan"=>1,"colspan"=>11],["value"=>"无效商品","rowspan"=>1,"colspan"=>5]
            ],
            //第三行
            [
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"收入","rowspan"=>1,"colspan"=>3],["value"=>"支出","rowspan"=>1,"colspan"=>7],
                ["value"=>"结存","rowspan"=>2,"colspan"=>1],["value"=>"收入","rowspan"=>2,"colspan"=>1],
                ["value"=>"支出","rowspan"=>1,"colspan"=>3],["value"=>"结存","rowspan"=>2,"colspan"=>1],
            ],
            //第四行
            [
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"调拨收入","rowspan"=>1,"colspan"=>1],["value"=>"送货收回","rowspan"=>1,"colspan"=>1],
                ["value"=>"其他收入","rowspan"=>1,"colspan"=>1],["value"=>"直发销售支出","rowspan"=>1,"colspan"=>1],
                ["value"=>"库房销售支出","rowspan"=>1,"colspan"=>1],["value"=>"调拨支出","rowspan"=>1,"colspan"=>1],
                ["value"=>"暂存商品","rowspan"=>1,"colspan"=>1],["value"=>"铺货商品","rowspan"=>1,"colspan"=>1],
                ["value"=>"送货支出","rowspan"=>1,"colspan"=>1],["value"=>"其他支出","rowspan"=>1,"colspan"=>1],
                ["value"=>"报废支出","rowspan"=>1,"colspan"=>1],["value"=>"调拨支出","rowspan"=>1,"colspan"=>1],
                ["value"=>"其他支出","rowspan"=>1,"colspan"=>1],
            ],
            //第五行
                [
                ["value"=>"合计","rowspan"=>1,"colspan"=>2],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],["value"=>"0","rowspan"=>1,"colspan"=>1],
                ["value"=>"0","rowspan"=>1,"colspan"=>1],
                ],
        ];
        
        $resposeData=array();
        //查询统计数据部分
        foreach ($depts as $obj_dept){
            //初始化表头合计值
            foreach($json_head[4] as $key=>$val){
                //数据从第二列开始
                if($key>0){
                    $json_head[4][$key]['value']=0;
                }
            }
            
            $obj_deptId=$obj_dept["id"];
            $dept_data=array();
            //查询按非结存数据
            $sql_data="select 1 as sort, 'bumentj'as col1,zhizaobm as col2,type,sum(dbsr) as dbsr ,sum(zcsr) as zcsr, sum(phsr) as phsr, sum(shsh) as shsh, sum(qtsr) as qtsr, sum(zfxszc) as zfxszc, 
                sum(kfxszc) as kfxszc, sum(dbzc) as dbzc, sum(zczc) as zczc,sum(phzc) as phzc,sum(shzc) as shzc,sum(qtzc) as qtzc  
                from spzmx where dept='$obj_deptId' and date='$startDate' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') group by zhizaobm,type 
                UNION
                select 2 as sort ,'daleitj'as col1,dalei as col2,type,sum(dbsr) as dbsr,sum(zcsr) as zcsr, sum(phsr) as phsr, sum(shsh) as shsh, sum(qtsr) as qtsr, sum(zfxszc) as zfxszc, 
                sum(kfxszc) as kfxszc, sum(dbzc) as dbzc, sum(zczc) as zczc,sum(phzc) as phzc,sum(shzc) as shzc,sum(qtzc) as qtzc 
                from spzmx where dept='$obj_deptId' and date='$startDate' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') group by dalei,type 
                UNION
                select 3 as sort ,'biaomianyqtj'as col1,biaomianyq as col2,type,sum(dbsr) as dbsr,sum(zcsr) as zcsr, sum(phsr) as phsr, sum(shsh) as shsh, sum(qtsr) as qtsr, sum(zfxszc) as zfxszc, 
                sum(kfxszc) as kfxszc, sum(dbzc) as dbzc, sum(zczc) as zczc,sum(phzc) as phzc,sum(shzc) as shzc,sum(qtzc) as qtzc  
                from spzmx where dept='$obj_deptId' and date='$startDate' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') group by biaomianyq,type
                UNION
                select 4 as sort, 'menkuangtj'as col1,menkuang as col2,type,sum(dbsr) as dbsr,sum(zcsr) as zcsr, sum(phsr) as phsr, sum(shsh) as shsh, sum(qtsr) as qtsr, sum(zfxszc) as zfxszc, 
                sum(kfxszc) as kfxszc, sum(dbzc) as dbzc, sum(zczc) as zczc,sum(phzc) as phzc,sum(shzc) as shzc,sum(qtzc) as qtzc 
                from spzmx where dept='$obj_deptId' and date='$startDate' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') group by menkuang,type 
                ORDER BY sort,col2,type desc";
          $ret_data=M()->query($sql_data);
          /* $filename=str_replace('\\','/',realpath(__DIR__)."/test.txt");
          $handle=fopen($filename,'a');
          fwrite($handle, $sql_data);
          fwrite($handle, "\n"); */
          $firstDay=date('Ym01',strtotime($startDate));
          $month=date('Y-m',strtotime($startDate));
          //查询结存数据
         /*  $sql_jc="select 1 as sort,'bumentj' as col1,zhizaobm as col2,type,ifnull(sum(qichusl),0)+(
          ifnull((select sum(jiecun) from spzmx where dept='$obj_deptId' and date between '$firstDay' and '$endDate' and zhizaobm=spzmxqc.zhizaobm and type=spzmxqc.type),0)
          ) as jiecun
          from spzmxqc where dept='$obj_deptId' and date='$month'  group by zhizaobm,type
          UNION
          select 2 as sort,'daleitj' as col1,dalei as col2,type,ifnull(sum(qichusl),0)+(
          ifnull((select sum(jiecun) from spzmx where dept='$obj_deptId' and date between '$firstDay' and '$endDate' and dalei=spzmxqc.dalei and type=spzmxqc.type),0)
          ) as jiecun
          from spzmxqc where dept='$obj_deptId' and date='$month'  group by dalei,type
          UNION
          select 3 as sort,'biaomianyqtj' as col1,biaomianyq as col2,type,ifnull(sum(qichusl),0)+(
          ifnull((select sum(jiecun) from spzmx where dept='$obj_deptId' and date between '$firstDay' and '$endDate' and biaomianyq=spzmxqc.biaomianyq and type=spzmxqc.type),0)
          ) as jiecun
          from spzmxqc where dept='$obj_deptId' and date='$month'  group by biaomianyq,type
          UNION
          select 4 as sort,'menkuangtj' as col1,menkuang as col2,type,ifnull(sum(qichusl),0)+(
          ifnull((select sum(jiecun) from spzmx where dept='$obj_deptId' and date between '$firstDay' and '$endDate' and menkuang=spzmxqc.menkuang and type=spzmxqc.type),0)
          ) as jiecun
          from spzmxqc where dept='$obj_deptId' and date='$month'  group by menkuang,type
          ORDER BY sort,col2,type desc;"; */
          $sql_jc="SELECT sort,col1,col2,type,ifnull((SELECT jiecun FROM (SELECT 1 AS sort,'bumentj' AS col1,zhizaobm AS col2,type,ifnull(sum(qichusl), 0) + (ifnull((SELECT sum(jiecun) FROM spzmx WHERE  dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套')  and zhizaobm not in ('舍零','返利','送货运费收入') AND date BETWEEN '$firstDay' AND '$endDate' AND zhizaobm = spzmxqc.zhizaobm AND type = spzmxqc.type),0)) AS jiecun FROM spzmxqc WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date = '$month'  GROUP BY zhizaobm,type UNION SELECT 2 AS sort,'daleitj' AS col1,dalei AS col2,type,ifnull(sum(qichusl), 0) + (ifnull((SELECT sum(jiecun) FROM spzmx WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date BETWEEN '$firstDay' AND '$endDate' AND dalei = spzmxqc.dalei AND type = spzmxqc.type),0)) AS jiecun FROM spzmxqc WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date = '$month'  GROUP BY dalei,type UNION SELECT 3 AS sort,'biaomianyqtj' AS col1,biaomianyq AS col2,type,ifnull(sum(qichusl), 0) + (ifnull((SELECT sum(jiecun) FROM spzmx WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date BETWEEN '$firstDay' AND '$endDate' AND biaomianyq = spzmxqc.biaomianyq AND type = spzmxqc.type),0)) AS jiecun FROM spzmxqc WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date = '$month'  GROUP BY biaomianyq,type UNION SELECT 4 AS sort,'menkuangtj' AS col1,menkuang AS col2,type,ifnull(sum(qichusl), 0) + (ifnull((SELECT sum(jiecun) FROM spzmx WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date BETWEEN '$firstDay' AND '$endDate' AND menkuang = spzmxqc.menkuang AND type = spzmxqc.type),0)) AS jiecun FROM spzmxqc WHERE dept = '$obj_deptId' and `dalei` not in ('锁体','锁芯','锁把','配件','门套') and zhizaobm not in ('舍零','返利','送货运费收入') AND date = '$month'  GROUP BY menkuang,type ORDER BY sort,col2,type DESC) AS t2 WHERE t2.sort = spzmx_neikong.sort AND t2.col1 = spzmx_neikong.col1 AND t2.col2 = spzmx_neikong.col2 AND t2.type = spzmx_neikong.type),0) AS jiecun FROM spzmx_neikong
          ";
          /* fwrite($handle, $sql_jc);
          fclose($handle); */
          $ret_jc=M()->query($sql_jc);
          if(empty($ret_data)&&$flag&&empty($ret_jc)){
              continue;
          }
          
          //循环更新结存的其他项
          //$ret_jc包含了期初数据比$ret_data更广泛
          foreach($ret_jc as $key=>$val){
              foreach($ret_data as $key_data=>$val_data){
                  if($val['col2']==$val_data['col2']&&$val['type']==$val_data['type']){
                      $ret_jc[$key]['dbsr']=$val_data['dbsr'];
                      $ret_jc[$key]['zcsr']=$val_data['zcsr'];
                      $ret_jc[$key]['phsr']=$val_data['phsr'];
                      $ret_jc[$key]['shsh']=$val_data['shsh'];
                      $ret_jc[$key]['qtsr']=$val_data['qtsr'];
                      $ret_jc[$key]['zfxszc']=$val_data['zfxszc'];
                      $ret_jc[$key]['kfxszc']=$val_data['kfxszc'];
                      $ret_jc[$key]['dbzc']=$val_data['dbzc'];
                      $ret_jc[$key]['zczc']=$val_data['zczc'];
                      $ret_jc[$key]['phzc']=$val_data['phzc'];
                      $ret_jc[$key]['shzc']=$val_data['shzc'];
                      $ret_jc[$key]['qtzc']=$val_data['qtzc'];
                      break;
                  }
              }
          }
         
          //循环更新结存并添加在spzmx表中当天未插入的数据
         /*  foreach ($ret_jc as $key=>$val){
              $offset=-1;//插入行位置
              foreach($ret_data as $key_data=>$val_data){
                  if($val['col2']==$val_data['col2']&&$val['type']==$val_data['type']){
                     $ret_data[$key_data]['jiecun']=$val['jiecun'];
                      break;
                  }
                   elseif ($val['col1']==$val_data['col1']){
                      if($ret_data[$key_data]['col1']!=$ret_data[$key_data+1]['col1']){
                          $offset=$key_data;
                          break;
                      }
                  }  
              }
              if($offset!=-1){
                  //插入有效数据行（sql默认排序有效在前）
                  array_splice($ret_data, $offset+1,0,[["col1"=>$val['col1'],
                      "col2"=>$val['col2'],"type"=>$val['type'],"jiecun"=>$val['jiecun'],
                  ]]);
              }
          }   */
          if(empty($ret_data)){
              $ret_data=$ret_jc;
          }
          //按部门或者大类或者门或者表面要求合并有效和无效数据成一个数组（对sql结果集的多行进行合并列）
          //sql结果集默认有效数据排在前面，对无效数据数组索引加前缀wx_
          
          $temp_array=array();
          foreach ($ret_jc as $key=>$val){
              //判断是否存在 index存在的位置
              $index=-1;
              foreach($temp_array as $tkey=>$tval){
                  if($tval['col2']==$val['col2']&&$tval['type']!=$val['type']){
                      $index=$tkey;
                      break;
                  }    
              }
              if($index==-1){
                  if($val['type']==1){
                      array_push($temp_array,$val);
                  }
                 else{
                     foreach ($val as $vkey=>$value){
                         if(is_numeric($value)&&$vkey!="type"){
                             $temp_row['wx_'.$vkey]=$value;
                         }
                         else{
                             $temp_row[$vkey]=$value;
                         }
                     }
                     array_push($temp_array,$temp_row);
                 }
              }
              else{
                  foreach ($val as $vkey=>$value){
                      if(is_numeric($value)&&$vkey!="type"){
                          $temp_array[$index]['wx_'.$vkey]=$value;
                      }
                  }
                  
              } 
          }
          //首列跨行数
          $bm_rowspan=0;
          $dl_rowspan=0;
          $bmyq_rowspan=0;
          $mk_rowspan=0;
          //每个部门的
          $temp_ret=array();
          //遍历temp_array封装成需要的json格式
          //$temp_ret目标数组结果
          foreach ($temp_array as $key=>$row){
              $temp_ret[$key][0]=["rowspan"=>1,"colspan"=>1,"value"=>$row['col2']];//部门、大类、表面方式、门框
              $temp_ret[$key][1]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['dbsr'])?0:$row['dbsr']];//调拨收入
              $temp_ret[$key][2]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['shsh'])?0:$row['shsh']];//送货收回
              $temp_ret[$key][3]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['qtsr'])?0:$row['qtsr']];//其他收入
              $temp_ret[$key][4]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['zfxszc'])?0:$row['zfxszc']];//直发销售支出
              $temp_ret[$key][5]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['kfxszc'])?0:$row['kfxszc']];//库房销售支出
              $temp_ret[$key][6]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['dbzc'])?0:$row['dbzc']];//调拨支出
              $zcsp=empty($row['zczc']-$row['zcsr'])?0:$row['zczc']-$row['zcsr'];//支出-收入??
              $temp_ret[$key][7]=["rowspan"=>1,"colspan"=>1,"value"=>$zcsp];//暂存商品
              $phsp=empty($row['phzc']-$row['phsr'])?0:$row['phzc']-$row['phsr'];//支出-收入??
              $temp_ret[$key][8]=["rowspan"=>1,"colspan"=>1,"value"=>$phsp];//铺货商品
              $temp_ret[$key][9]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['shzc'])?0:$row['shzc']];//送货支出
              $temp_ret[$key][10]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['qtzc'])?0:$row['qtzc']];//其他支出
              $temp_ret[$key][11]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['jiecun'])?0:$row['jiecun']];//结存
              //无效
              $temp_ret[$key][12]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['wx_dbsr'])?0:$row['wx_dbsr']];//收入
              $temp_ret[$key][13]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['wx_qtzc'])?0:$row['wx_qtzc']];//报废支出
              $temp_ret[$key][14]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['wx_dbzc'])?0:$row['wx_dbzc']];//调拨支出
              $wx_qtzc=$row['wx_zfxszc']+$row['wx_kfxszc']+$row['wx_zczc']+$row['wx_phzc']+$row['wx_shzc']
              -$row['wx_shsh']-$row['wx_zcsr']-$row['wx_phsr']-$row['wx_qtsr'];
              $temp_ret[$key][15]=["rowspan"=>1,"colspan"=>1,"value"=>$wx_qtzc];//其他支出  
              $temp_ret[$key][16]=["rowspan"=>1,"colspan"=>1,"value"=>empty($row['wx_jiecun'])?0:$row['wx_jiecun']];//结存
              switch($row['col1']){
                  //按部门统计合计行
                  case "bumentj":
                      $json_head[4][1]['value']+=empty($row['dbsr'])?0:$row['dbsr'];//调拨收入
                      $json_head[4][2]['value']+=empty($row['shsh'])?0:$row['shsh'];//送货收回
                      $json_head[4][3]['value']+=empty($row['qtsr'])?0:$row['qtsr'];//其他收入
                      $json_head[4][4]['value']+=empty($row['zfxszc'])?0:$row['zfxszc'];//直发销售支出
                      $json_head[4][5]['value']+=empty($row['kfxszc'])?0:$row['kfxszc'];//库房销售支出
                      $json_head[4][6]['value']+=empty($row['dbzc'])?0:$row['dbzc'];//调拨支出
                      $json_head[4][7]['value']+=$zcsp;//暂存商品
                      $json_head[4][8]['value']+=$phsp;//铺货商品  
                      $json_head[4][9]['value']+=empty($row['shzc'])?0:$row['shzc'];//送货支出
                      $json_head[4][10]['value']+=empty($row['qtzc'])?0:$row['qtzc'];//其他支出
                      $json_head[4][11]['value']+=empty($row['jiecun'])?0:$row['jiecun'];//结存
                      //无效
                      $json_head[4][12]['value']+=empty($row['wx_dbsr'])?0:$row['wx_dbsr'];//收入
                      $json_head[4][13]['value']+=empty($row['wx_qtzc'])?0:$row['wx_qtzc'];//报废支出
                      $json_head[4][14]['value']+=empty($row['wx_dbzc'])?0:$row['wx_dbzc'];//调拨支出
                      $json_head[4][15]['value']+=$wx_qtzc;//其他支出  
                      $json_head[4][16]['value']+=empty($row['wx_jiecun'])?0:$row['wx_jiecun'];//结存
                      $bm_rowspan++;
                          break;
                  case "daleitj":
                      $dl_rowspan++;
                      break;
                  case "biaomianyqtj":
                      $bmyq_rowspan++;
                      break;
                  case "menkuangtj":
                      $mk_rowspan++;
                      break;
              } 
          }
          
          $json_head[0][0]['value']=$obj_dept['dname'];//设置部门名称
          //设置首列跨行
          if(!empty($temp_ret)){
              array_unshift($temp_ret[0],array("value"=>"按部门统计","colspan"=>1,"rowspan"=>$bm_rowspan));
              array_unshift($temp_ret[$bm_rowspan],array("value"=>"按大类统计","colspan"=>1,"rowspan"=>$dl_rowspan));
              array_unshift($temp_ret[$bm_rowspan+$dl_rowspan],array("value"=>"按表面要求统计","colspan"=>1,"rowspan"=>$bmyq_rowspan));
              array_unshift($temp_ret[$bm_rowspan+$dl_rowspan+$bmyq_rowspan],array("value"=>"按门框统计","colspan"=>1,"rowspan"=>$mk_rowspan));
          }
          array_push($resposeData,['head'=>$json_head,'data'=>$temp_ret]); 
         
        }
        if($flag)
            return $resposeData;
        else
            $this->response(retmsg(0,array("totalPage"=>$total,"page"=>$page,"content"=>$resposeData)),'json');
    }
    
    //根据响应格式生成
    public function toExcel3($printData,$dept='',$date='',$flag=false){
        set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $rowPos=1;//excel行号
        //循环打印每个部门的数据
        foreach($printData as $deptRow=>$deptVal){
            //循环打印表头
            foreach($deptVal['head'] as $headRow=>$headVal){
                    //$rowPos=$deptRow*24+1+$headRow;
                    //第一行
                    if($headRow==0){
                        $objPHPExcel->getActiveSheet()->setCellValue("A$rowPos",$headVal[0]['value']);
                        $objPHPExcel->getActiveSheet()->mergeCells("A$rowPos:R$rowPos");
                        $objPHPExcel->getActiveSheet()->getStyle("A$rowPos:R$rowPos")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $rowPos++;
                    }
                     //第二行
                    if($headRow==1){
                        $objPHPExcel->getActiveSheet()->setCellValue("A$rowPos","");
                        $objPHPExcel->getActiveSheet()->setCellValue("B$rowPos","");
                        $objPHPExcel->getActiveSheet()->setCellValue("C$rowPos","有效商品");
                        //合并C到M
                        $objPHPExcel->getActiveSheet()->mergeCells("C$rowPos:M$rowPos");
                        $objPHPExcel->getActiveSheet()->setCellValue("N$rowPos","无效商品");
                        //合并N到R
                        $objPHPExcel->getActiveSheet()->mergeCells("N$rowPos:R$rowPos");
                        $rowPos++;
                    }
                    //第三行
                    if($headRow==2){
                        $objPHPExcel->getActiveSheet()->setCellValue("A$rowPos","");
                        $objPHPExcel->getActiveSheet()->setCellValue("B$rowPos","");
                        $objPHPExcel->getActiveSheet()->setCellValue("C$rowPos","收入");
                        $objPHPExcel->getActiveSheet()->mergeCells("C$rowPos:E$rowPos");
                        $objPHPExcel->getActiveSheet()->getStyle("C$rowPos:E$rowPos")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue("F$rowPos","支出");
                        $objPHPExcel->getActiveSheet()->mergeCells("F$rowPos:L$rowPos");
                        $objPHPExcel->getActiveSheet()->setCellValue("M$rowPos","结存");
                        $pos=$rowPos+1;
                        $objPHPExcel->getActiveSheet()->mergeCells("M$rowPos:M$pos");
                        $objPHPExcel->getActiveSheet()->setCellValue("N$rowPos","收入");
                        $objPHPExcel->getActiveSheet()->mergeCells("N$rowPos:N$pos");
                        $objPHPExcel->getActiveSheet()->getStyle("N$rowPos:N$pos")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $objPHPExcel->getActiveSheet()->setCellValue("O$rowPos","支出");
                        $objPHPExcel->getActiveSheet()->mergeCells("O$rowPos:Q$rowPos");
                        $objPHPExcel->getActiveSheet()->setCellValue("R$rowPos","结存");
                        $objPHPExcel->getActiveSheet()->mergeCells("R$rowPos:R$pos");
                        $rowPos++;
                    }
                    //第四行
                    if($headRow==3){
                        foreach($headVal as $key=>$value){
                           $pos=$index[$key].$rowPos;
                           //结存收入跨行
                           if($key>=12){
                               $pos=$index[$key+2].$rowPos;
                           }
                           $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                        }
                        $rowPos++;
                    }
                    //第五行
                    if($headRow==4){
                        foreach($headVal as $key=>$value){
                            //合计单元格跨列
                            if($key==0){
                                $objPHPExcel->getActiveSheet()->setCellValue("A$rowPos","合计");
                                $objPHPExcel->getActiveSheet()->mergeCells("A$rowPos:B$rowPos");
                            }else{
                                $pos=$index[$key+1].$rowPos;
                                $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                            }
                        }
                        $rowPos++;
                    }
             }   
            //循环打印数据部分
           foreach($deptVal['data'] as $dataRow=>$dataVal){ 
                //$rowPos=$deptRow*24+5+$dataRow;//加上表头4行
                $tds=count($dataVal);
                //合并行
                if($tds==18&&$dataVal[0]['rowspan']!=1){
                    $offset=$rowPos+$dataVal[0]['rowspan']-1;
                    $dest="A".$offset;
                    $objPHPExcel->getActiveSheet()->mergeCells("A$rowPos:$dest");
                }
                foreach ($dataVal as $key=>$value){
                    //统计方式行
                    if($tds==18){
                        $pos=$index[$key].$rowPos;
                    }
                    else {
                        $test=2;
                        $pos=$index[$key+1].$rowPos;
                    }
                        
                $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                }
                $rowPos++;
             }  
        }
        // 直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        //定时调度上传
       if($flag){
           $dept_id=$dept['dept_id'];
           return \XSRBUtil::uploadExcel($dept_id, $objWriter,'FDMKCHZ',$date);
       }else 
           return $objWriter;
    }
    
    //部门横向生成，行数固定 20170330版本
    public function toExcel20170331($printData,$dept='',$date='',$flag=false){
        set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        $objPHPExcel = new \PHPExcel();
        
        $pageSize=10;//每个excel生成10个部门的数据
        $total=count($printData);//总计部门数
        $fileCount=0;//生成的excle数量，每个excel生成10个部门的数据
        if($total<$pageSize)
            $fileCount=1;
            elseif($total%$pageSize==0)
                $fileCount=$total/$pageSize;
            else
                $fileCount=$total/$pageSize+1;
        $fileCount=(int)$fileCount;
        //每一张excel打印10个部门
        for($i=0;$i<$fileCount;$i++){
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($i);
            //开始部门在$printData的下标位置
            $fromDept=$i*$pageSize;
            $destDept=$fromDept+$pageSize;
            for($j=$fromDept;$j<$destDept;$j++){
                if(!isset($printData[$j])){
                    break;
                }
                $deptVal=$printData[$j];
                $colPosInt=($j-$fromDept)*18;//$j-$fromDept 表示在$fromDept
                
                //循环打印表头
                foreach($deptVal['head'] as $headRow=>$headVal){
                    //$rowPos=$deptRow*24+1+$headRow;
                    //第一行
                    if($headRow==0){
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."1",$headVal[0]['value']);
                        $det=$colPosInt+17;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."1:".$det."1");
                        $objPHPExcel->getActiveSheet()->getStyle($colPos."1:".$det."1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        //$rowPos++;
                    }
                    //第二行
                    if($headRow==1){
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."2","");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+1);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."2","");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+2);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."2","有效商品");
                        //合并C到M
                        $det=$colPosInt+12;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."2";
                        $from=$colPos."2";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        //n到r
                        $det=$colPosInt+17;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."2";
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+13);
                        $colPos=$colPos."2";
                        $objPHPExcel->getActiveSheet()->setCellValue("$colPos","无效商品");
                        //合并N到R
                        $objPHPExcel->getActiveSheet()->mergeCells("$colPos:$det");
                        //$rowPos++;
                    }
                    //第三行
                    if($headRow==2){
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+1);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+2);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","收入");
                        $det=$colPosInt+2;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."3";
                        $from=$colPos."3";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        $objPHPExcel->getActiveSheet()->getStyle("$from:$det")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+5);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","支出");
                        $det=$colPosInt+11;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."3";
                        $from=$colPos."3";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+12);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","结存");
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."3:".$colPos."4");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+13);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","收入");
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."3:".$colPos."4");
                        $objPHPExcel->getActiveSheet()->getStyle($colPos."3:".$colPos."4")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+14);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","支出");
                        $det=$colPosInt+16;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."3";
                        $from=$colPos."3";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+17);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","结存");
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."3:".$colPos."4");
                    }
                     //第四行
                    if($headRow==3){
                        foreach($headVal as $key=>$value){
                            $pos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key);
                            //结存收入跨行
                            if($key>=12){
                                $pos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key);
                            }
                            $pos=$pos."4";
                            if($pos=="BW4"){
                                $test=4;
                            }
                            $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                        }
                    }
                    //第五行
                    if($headRow==4){
                        foreach($headVal as $key=>$value){
                            //合计单元格跨列
                            if($key==0){
                                $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                                $objPHPExcel->getActiveSheet()->setCellValue($colPos."5","合计");
                                $det=$colPosInt+1;
                                $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                                $det=$det."5";
                                $from=$colPos."5";
                                $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                            }else{
                                $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+1+$key);
                                $pos=$colPos."5";
                                $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                            }
                        }
                    }
                }
                 //循环打印数据部分
                 foreach($deptVal['data'] as $dataRow=>$dataVal){
                    $rowPos=$dataRow+6;//加上表头6行
                    $tds=count($dataVal);
                    //合并行
                    if($tds==18&&$dataVal[0]['rowspan']!=1){
                        $offset=$dataVal[0]['rowspan']-1;
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                        $dest="$colPos".($rowPos+$offset);
                        $from=$colPos."$rowPos";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$dest");
                    }
                    foreach ($dataVal as $key=>$value){
                        //统计方式行
                        if($tds==18){
                           $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key);
                        }
                        else {
                           $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key+1);
                        }
                        $pos=$colPos."$rowPos";
                        $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                    }
                 }  
            } 
        }
            // 直接输出到浏览器
            $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
            //定时调度上传
            if($flag){
                $dept_id=$dept['dept_id'];
                return \XSRBUtil::uploadExcel($dept_id, $objWriter,'FDMKCHZ',$date);
            }else
                return $objWriter;
        
    }
    //部门横向生成，行数固定 20170331版本（dzj）
    public function toExcel($printData,$dept='',$date='',$flag=false){
        set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        $objPHPExcel = new \PHPExcel();
        
        $pageSize=10;//每个excel生成10个部门的数据
        $total=count($printData);//总计部门数
        $fileCount=0;//生成的excle数量，每个excel生成10个部门的数据
        if($total<$pageSize)
            $fileCount=1;
            elseif($total%$pageSize==0)
                $fileCount=$total/$pageSize;
            else
                $fileCount=$total/$pageSize+1;
        $fileCount=(int)$fileCount;
        //每一张excel打印10个部门
        for($i=0;$i<$fileCount;$i++){
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex($i);
            //开始部门在$printData的下标位置
            $fromDept=$i*$pageSize;
            $destDept=$fromDept+$pageSize;
            for($j=$fromDept;$j<$destDept;$j++){
                if(!isset($printData[$j])){
                    break;
                }
                $deptVal=$printData[$j];
                $colPosInt=($j-$fromDept)*16+2;//$j-$fromDept 表示在$fromDept，在这个基础上添加了两列左表头
                
                //循环打印表头
                foreach($deptVal['head'] as $headRow=>$headVal){
                    //$rowPos=$deptRow*24+1+$headRow;
                    //第一行
                    if($headRow==0){
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."1",$headVal[0]['value']);
                        $det=$colPosInt+15;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."1:".$det."1");
                        $objPHPExcel->getActiveSheet()->getStyle($colPos."1:".$det."1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        //$rowPos++;
                    }
                     //第二行
                    if($headRow==1){
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."2","有效商品");
                        //合并C到M
                        $det=$colPosInt+10;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."2";
                        $from=$colPos."2";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        //n到r
                        $det=$colPosInt+15;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."2";
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+11);
                        $colPos=$colPos."2";
                        $objPHPExcel->getActiveSheet()->setCellValue("$colPos","无效商品");
                        //合并N到R
                        $objPHPExcel->getActiveSheet()->mergeCells("$colPos:$det");
                        //$rowPos++;
                    }
                    //第三行
                    if($headRow==2){
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","收入");
                        $det=$colPosInt+2;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."3";
                        $from=$colPos."3";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        $objPHPExcel->getActiveSheet()->getStyle("$from:$det")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+3);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","支出");
                        $det=$colPosInt+9;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."3";
                        $from=$colPos."3";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+10);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","结存");
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."3:".$colPos."4");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+11);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","收入");
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."3:".$colPos."4");
                        $objPHPExcel->getActiveSheet()->getStyle($colPos."3:".$colPos."4")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+12);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","支出");
                        $det=$colPosInt+12;
                        $det=\PHPExcel_Cell::stringFromColumnIndex($det);
                        $det=$det."3";
                        $from=$colPos."3";
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$det");
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+15);
                        $objPHPExcel->getActiveSheet()->setCellValue($colPos."3","结存");
                        $objPHPExcel->getActiveSheet()->mergeCells($colPos."3:".$colPos."4");
                    }
                      //第四行
                    if($headRow==3){
                        foreach($headVal as $key=>$value){
							if ($key>1) //添加判断重新计算key，因为中间部门不需要添加统计列
							{
								$key1=$key-2;
							}
							else
							{
								$key1=$key;
							}
                            $pos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key1);
							//echo "   cishu:".$colPosInt+$key;
                            //结存收入跨行
                            if($key>=12){
                                $pos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key);
                            }
                            $pos=$pos."4";
                            if($pos=="BW4"){
                                $test=2;
                            }
                            $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                        }
						//return;
                    }
                    //第五行
                    if($headRow==4){
						//var_dump($headVal);return;
                        foreach($headVal as $key=>$value){
                            //合计单元格跨列
                            if($key==0){
                                $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt-2);
								if($colPosInt==2)
								{
									$objPHPExcel->getActiveSheet()->setCellValue($colPos."5","合计");
									$det=$colPosInt+1-2;
									$det=\PHPExcel_Cell::stringFromColumnIndex($det);
									$det=$det."5";
									$from=$colPos."5";
									$objPHPExcel->getActiveSheet()->mergeCells("$from:$det"); 
								}
                            }
							else
							{
								if ($key>0)//添加判断重新计算key，因为中间部门不需要添加统计列
								{
									$key1=$key-2;
								}
								else
								{
									$key1=$key;
								}
                                $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+1+$key1);
                                $pos=$colPos."5";
                                $objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
                            }
                        }
                    }
                }
                //循环打印数据部分
				//echo json_encode($deptVal['data']);return;
				foreach($deptVal['data'] as $dataRow=>$dataVal){
                    $rowPos=$dataRow+6;//加上表头6行
                    $tds=count($dataVal);
                    //合并行
                    if($tds==18&&$dataVal[0]['rowspan']!=1){
                        $offset=$dataVal[0]['rowspan']-1;
                        $colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt-2);
						if ($colPos=="A")//判断是否是A列，如果是才进行这样的操作，因为表头只有一个
						{
							$dest="$colPos".($rowPos+$offset);
							$from=$colPos."$rowPos";
							$objPHPExcel->getActiveSheet()->mergeCells("$from:$dest");
						} 
                    } 
                    foreach ($dataVal as $key=>$value){
                        if($tds==18){
							if($key>1)//添加判断重新计算key，因为中间部门不需要添加统计列
							{
								$key1=$key-2;
								$colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key1);
								$pos=$colPos."$rowPos";
								$objPHPExcel->getActiveSheet()->setCellValue($pos,empty($value['value'])?0:$value['value']); 
							}
							else
							{
								if($key==0)//添加判断重新计算key，因为中间部门不需要添加统计列
								{
									$pos="A".$rowPos; //组合左表头
								}
								else
								{
									$pos="B".$rowPos;//组合左表头
								}
								$objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
							}
                        }
                        else 
						{
							if($key>0)//添加判断重新计算key，因为中间部门不需要添加统计列
							{
								$key1=$key-1;
								$colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key1);
								$pos=$colPos."$rowPos";
								$objPHPExcel->getActiveSheet()->setCellValue($pos,empty($value['value'])?0:$value['value']); 
							}
							else
							{
								//$colPos=\PHPExcel_Cell::stringFromColumnIndex($colPosInt+$key1);
								$pos="B".$rowPos;//组合左表头
								$objPHPExcel->getActiveSheet()->setCellValue($pos,$value['value']);
							}
                        }
                    }
                 }
            } 
        }
            // 直接输出到浏览器
            $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
            //定时调度上传
            if($flag){
                $dept_id=$dept['dept_id'];
                return \XSRBUtil::uploadExcel($dept_id, $objWriter,'FDMKCHZ',$date);
            }else
                return $objWriter;
        
    }
    public function printExcel($token='',$startDate='',$endDate=''){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        //部门id
        $dept_id= $userinfo['dept_id'];
        set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
        $printData=$this->search($token,$startDate,$endDate,1,1000,'','',true);
        //print_r($printData);return;
        $objWriter=$this->toExcel($printData);
        //输出到浏览器
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $filename="FDMKCHZ-$startDate-$dept_id.xls";
        header("Content-Disposition:attachment;filename=$filename");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
    
    public function printCsv($token='',$startDate='',$endDate=''){
        header("Access-Control-Allow-Origin: *");
        set_time_limit(600);
        $printData=$this->search($token,$startDate,$endDate,1,1000,true);
        //循环打印每个部门的数据
        $str = "";
        foreach($printData as $deptRow=>$deptVal){
            //循环打印表头部分
            foreach($deptRow['head'] as $headRow=>$headVal){
                foreach ($headRow as $key=>$val){
                    $value=$val['value'];
                    $str.="$key,";
                    if($val['colspan']>1){
                        for($i=0;$i<$val['colspan'];$i++){
                            $str.=",";
                        }
                    }
                }
                $str .= "\n";
            }
            //循环打印数据部分
            foreach($deptRow['data'] as $dataRow=>$dataVal){
            
            }
            $str = iconv("utf-8", "gbk", $str);
        }

        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=SMCPKCBTJ.csv");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo  $str;
    }
    
    public function downloadExcel($token='',$startDate='',$endDate=''){
        set_time_limit(600);
        header("Access-Control-Allow-Origin: *");
        
        //验证token
        $userinfo = checktoken($token);
       if(!$userinfo)
         {
         $this->response(retmsg(-2),'json');
         return;
         } 
        //部门id
        $dept_id= $userinfo['dept_id'];
        //上级部门id
        $pid=$userinfo['pid'];
        $qt1=$userinfo['qt1'];
       /*  $qt1=1;
        $dept_id=25;
        $pid=22; */
        if ($startDate == '' || strtotime($startDate) >= strtotime(date('Ymd'))) {
            $startDate = date("Ymd",strtotime("-1 day"));
        }else{
            $startDate = date("Ymd", strtotime($startDate));
        }
        //如果是片区或者是总部则直接访问上传的excle
        //如果是片区或者是总部则直接访问上传的excle
        if($qt1==0||$dept_id==1){
             //查询上传excel的地址
             $sql_addr="select url from xsrb_excel where dept_id='$dept_id' and date='$startDate' and biao='FDMKCHZ' ";
             $ret_addr=M()->query($sql_addr);
			 if($_SERVER['SERVER_NAME'] =='172.16.10.252' && count($ret_addr))
			{
				$downloadUrl ="http://172.16.10.252/files/"."FDMKCHZ"."-".$startDate."-".$dept_id.".xls" ;
			}
			else
			{
				$downloadUrl=$ret_addr[0]['url'];
			}
             
        } 
        if(empty($downloadUrl)){
            $downloadUrl=XSRB_IP.__CONTROLLER__."/printExcel/token/$token/startDate/$startDate/endDate/$endDate";
        }
      
      $this->response(retmsg(0,array("downloadUrl"=>$downloadUrl)),'json');
        
    }
    
    /**
     * 定时调度上传各部门的excel
     */
    public function uploadExcel($date=TODAY){
        header("Access-Control-Allow-Origin: *");
        set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
         $dept=array();
          $biao='FDMKCHZ';
        $sql="select id,pid from xsrb_department where qt1 in (0,1)  and id not in(
             select dept_id from xsrb_excel where date='$date' and biao='$biao') ";
         $depts=M()->query($sql);
		 $ret=1;
         foreach ($depts as $row){
             $dept['dept_id']=$row['id'];
             $dept['pid']=$row['pid'];
             $printData=$this->search("",$date,$date,1,1000,$dept['dept_id'],$dept['pid'],true);
             $ret=$this->toExcel($printData,$dept,$date,true); 
         }
         if($ret)
            return '{"resultcode":1,"resultmsg":"防盗门库存汇总表统计表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"防盗门库存汇总表统计表上传失败"}';
     }
    public function test(){
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        $filename="FDMKCHZ-$startDate-$dept_id.xls";
        header("Content-Disposition:attachment;filename=$filename");
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }
  
}


?>