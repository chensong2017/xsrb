<?php
namespace Home\Controller;

use Think\Controller\RestController;

include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class FDMKCBMXController extends RestController
{
    //销售日报明细
    public function search($token ='',$date ='',$page ='',$pagesize ='',$type ='',$id ='')
    {
        header("Access-Control-Allow-Origin: *");
        
        //type==''表示为查询
        if ($type =='')
        {
            //token检测
	    $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;             }
            $dept_id = $userinfo['dept_id'];
        }
        //type ='excel'表示为导出excel
        if ($type =='excel')
        {
            $dept_id = $id;
        }
        //日期选择
        if ($date == '') {
            $date = date("Ymd");
        } else {
            $date = date("Ymd", strtotime($date));
        }
        $syue = date('Y-m',strtotime($date));
        // 分页
        if ($page <= 0) {
            $page = 1;
        }
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname from xsrb_department where pid != 1 and qt1 !=0 ";
        } else
        {
            // 片区部门
            $sql = "select id,dname from xsrb_department where qt2 like '%." . $dept_id . "'";
        
            // 判断部门(非片区and总部)的查询
            $pagination = M()->query($sql);
            $count = count($pagination); //部门数
            //部门查询时,只显示本部门
            if ($count == 0)
            {
                $sql = "select * from xsrb_department where id =" . $dept_id;
            }
        }
        $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/mx_json1.txt"),'r');
        $mx_json1=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/mx_json1.txt")));
        fclose($handle);
        $count = count(M()->query($sql));   //计算总共页数
        if ($type =='')
        {
            $arr = array();
            $dept = M()->query($sql.' limit '.($page-1).',1');
            
            //temp模版	
            $temp = json_decode($mx_json1,true);
            $fdmkcbmx = M()->query("select * from fdmkcb where dept =".$dept[0]['id']." and `date` between '".date("Ym01",strtotime($date))."' and '".$date."' order by date asc" );
            if (count($fdmkcbmx))
            {
                $qc = M()->query("select * from fdmkcbqc where `dept` = ".$dept[0]['id']." and `date` = '".$syue."'");
                foreach ($qc as $kq => $vq)
                {
                    $ar[$vq['product'].'有效收支有效结存'] = $vq['yxjc'];
                    $ar[$vq['product'].'无效收支无效结存'] = $vq['wxjc'];
                }
                $jt = M()->query("select product from fdmkcb where date ='$date' and dept =".$dept[0]['id']);
                $exist = count($jt);
                foreach ($fdmkcbmx as $k1=>$v1)     //时间内的数据
                {
                    $arr[$v1['product'].'调拨收入'] = $exist ? $v1['dbsr']:0;
                    $arr[$v1['product'].'其他收入'] = $exist ? $v1['qtsr']:0;
                    $arr[$v1['product'].'销售量成都生产'] = $exist ? $v1['cdsc']:0;
                    $arr[$v1['product'].'销售量齐河生产'] = $exist ? $v1['qhsc']:0;
                    $arr[$v1['product'].'销售量外购门'] = $exist ? $v1['wgm']:0;
                    $arr[$v1['product'].'有效收支报废支出'] = $exist ? $v1['bfzc']:0;
                    $arr[$v1['product'].'有效收支调拨支出'] = $exist ? $v1['dbzc']:0;
                    $arr[$v1['product'].'有效收支其他支出'] = $exist ? $v1['qtzc']:0;
                    $ar[$v1['product'].'有效收支有效结存'] += $v1['yxjc'];
                    $arr[$v1['product'].'无效收支有效转入'] = $exist ? $v1['yxzr']:0;
                    $arr[$v1['product'].'无效收支报废支出'] = $exist ? $v1['wxbfzc']:0;
                    $arr[$v1['product'].'无效收支其他支出'] = $exist ? $v1['wxqtzc']:0;
                    $ar[$v1['product'].'无效收支无效结存'] += $v1['wxjc'];

                    $arr[$v1['product'].'有效收支有效结存'] = $ar[$v1['product'].'有效收支有效结存'];
                    $arr[$v1['product'].'无效收支无效结存'] = $ar[$v1['product'].'无效收支无效结存'];
                }
            }
            //计算过的数据赋值 给temp模版
            foreach ($temp['data'] as $k2=>$v2)
            {
                foreach ($v2['tr'] as $k3=>$v3)
                {
                    if ($v3['value'] ==='depart')   //部门
                    {
                        $temp['data'][$k2]['tr'][$k3]['value'] = $dept[0]['dname'];
                    }
                    if ($arr[$v3['product'].$v3['type'].$v3['type_detail']] != '')
                    {
                        $temp['data'][$k2]['tr'][$k3]['value'] = $arr[$v3['product'].$v3['type'].$v3['type_detail']];
                    }
                }
            }
            unset($arr);unset($ar);
            $temp['page'] = $page;
            $temp['total'] = $count;
            echo json_encode($temp);
        }elseif ($type =='excel')
        {
            $dept = M()->query($sql);
            foreach($dept as $kk => $vk)
            {
                $fdmkcbmx = M()->query("select * from fdmkcb where dept =".$vk['id']." and `date` between '".date("Ym01",strtotime($date))."' and '".$date."' order by date asc");
                if (count($fdmkcbmx))   //判断查询结果
                {
                    $qc = M()->query("select * from fdmkcbqc where `dept` = ".$vk['id']." and `date` = '".$syue."'");
                    foreach ($qc as $kq => $vq)
                    {
                        $ar[$vq['product'].'有效收支有效结存'] = $vq['yxjc'];
                        $ar[$vq['product'].'无效收支无效结存'] = $vq['wxjc'];
                    }
					$jt = M()->query("select product from fdmkcb where date ='$date' and dept =".$dept[0]['id']);
                    if (count($jt))
                    {
                        foreach ($fdmkcbmx as $k1=>$v1)     //时间内的数据
                        {
                            $arr[$v1['product'].'调拨收入'] = $v1['dbsr'];
                            $arr[$v1['product'].'其他收入'] =  $v1['qtsr'];
                            $arr[$v1['product'].'销售量成都生产'] =  $v1['cdsc'];
                            $arr[$v1['product'].'销售量齐河生产'] =  $v1['qhsc'];
                            $arr[$v1['product'].'销售量外购门'] =  $v1['wgm'];
                            $arr[$v1['product'].'有效收支报废支出'] =  $v1['bfzc'];
                            $arr[$v1['product'].'有效收支调拨支出'] =  $v1['dbzc'];
                            $arr[$v1['product'].'有效收支其他支出'] =  $v1['qtzc'];
                            $ar[$v1['product'].'有效收支有效结存'] += $v1['yxjc'];
                            $arr[$v1['product'].'无效收支有效转入'] =  $v1['yxzr'];
                            $arr[$v1['product'].'无效收支报废支出'] =  $v1['wxbfzc'];
                            $arr[$v1['product'].'无效收支其他支出'] =  $v1['wxqtzc'];
                            $ar[$v1['product'].'无效收支无效结存'] += $v1['wxjc'];
        
                            $arr[$v1['product'].'有效收支有效结存'] = $ar[$v1['product'].'有效收支有效结存'];
                            $arr[$v1['product'].'无效收支无效结存'] = $ar[$v1['product'].'无效收支无效结存'];
                        }
                    }else 
                    {
                        foreach ($fdmkcbmx as $k1=>$v1)     //时间内的数据
                        {
                            $arr[$v1['product'].'调拨收入'] = 0;
                            $arr[$v1['product'].'其他收入'] =  0;
                            $arr[$v1['product'].'销售量成都生产'] =  0;
                            $arr[$v1['product'].'销售量齐河生产'] =  0;
                            $arr[$v1['product'].'销售量外购门'] =  0;
                            $arr[$v1['product'].'有效收支报废支出'] =  0;
                            $arr[$v1['product'].'有效收支调拨支出'] =  0;
                            $arr[$v1['product'].'有效收支其他支出'] =  0;
                            $ar[$v1['product'].'有效收支有效结存'] += $v1['yxjc'];
                            $arr[$v1['product'].'无效收支有效转入'] =  0;
                            $arr[$v1['product'].'无效收支报废支出'] =  0;
                            $arr[$v1['product'].'无效收支其他支出'] =  0;
                            $ar[$v1['product'].'无效收支无效结存'] += $v1['wxjc'];
                        
                            $arr[$v1['product'].'有效收支有效结存'] = $ar[$v1['product'].'有效收支有效结存'];
                            $arr[$v1['product'].'无效收支无效结存'] = $ar[$v1['product'].'无效收支无效结存'];
                        }
                    }
                }
                $aa[$vk['dname']] = $arr;
                unset($arr);unset($ar);
            }
            return $aa;
        }
    }
    
    //防盗门库存表明细 导出excel文件并上传
    public function uploadExcel($date ='',$bumen_id ='')
    {
		ini_set('max_execution_time',2000);
        ini_set('memory_limit', "-1");				
        header("Access-Control-Allow-Origin: *");
		if($date =='')
		{
			$date = date("Ymd",strtotime("-1 day"));		//根据昨天的数据生成Excel
		}
		$ret = 1;
        $sql = "select id from xsrb_department where id =1 or qt1 =0 ";    
        $dept_id = M()->query($sql);
        if ($bumen_id !='' && isset($bumen_id))
        {
            $dept_id = array(
                array('id' =>$bumen_id)
            );
        }
		$handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/mx_json2.txt"),'r');
		$mx_json2=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/mx_json2.txt")));
		fclose($handle);		
        foreach ($dept_id as $kde => $vde)
        {
            $cx = M()->query("select * from xsrb_excel where `biao` ='fdmkcbmx' and dept_id =".$vde['id']." and `date` =".$date);
            if (!count($cx))
            {
                //new一个phpexcel
                $objPHPExcel = new \PHPExcel();
                \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
                $i =4;      //设置excel在第4行开始循环
                //设置excel标题
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','防盗门库存表明细');            $objPHPExcel->getActiveSheet()->mergeCells('A1:P1');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2','门类型');            $objPHPExcel->getActiveSheet()->mergeCells('A2:A3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2','门类别');            $objPHPExcel->getActiveSheet()->mergeCells('B2:B3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2','表面处理');            $objPHPExcel->getActiveSheet()->mergeCells('C2:C3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2','调拨收入');            $objPHPExcel->getActiveSheet()->mergeCells('D2:D3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2','其他收入');            $objPHPExcel->getActiveSheet()->mergeCells('E2:E3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2','销售量');            $objPHPExcel->getActiveSheet()->mergeCells('F2:H2');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F3','成都生产');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G3','齐河生产');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H3','外购门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2','报废支出');            $objPHPExcel->getActiveSheet()->mergeCells('I2:I3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2','调拨支出');            $objPHPExcel->getActiveSheet()->mergeCells('J2:J3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2','其他支出');            $objPHPExcel->getActiveSheet()->mergeCells('K2:K3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2','有效结存量');            $objPHPExcel->getActiveSheet()->mergeCells('L2:L3');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2','无效收支');            $objPHPExcel->getActiveSheet()->mergeCells('M2:P2');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M3','有效转入');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N3','报废支出');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O3','其他支出');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P3','无效结存量');
                $json = $this->search('',$date,'','','excel',$vde['id']);
                foreach ($json as $kj => $vj)       //赋值给temp
                {
                    $temp = json_decode($mx_json2,true);
                    foreach ($temp['data'] as $kt => $vt)
                    {
                        foreach ($vt['tr'] as $ke => $ve)
                        {
                            if ($ve['value'] ==='depart')
                            {
                                $temp['data'][$kt]['tr'][$ke]['value'] = $kj;
                            }if ($vj[$ve['product'].$ve['type'].$ve['type_detail']] != '')      //如没有数据就赋值0
                            {
                                $temp['data'][$kt]['tr'][$ke]['value'] = $vj[$ve['product'].$ve['type'].$ve['type_detail']];
                            }
                        }
                    }
                    foreach ($temp['data'] as $k1 => $v1)
                    {
                        foreach ($v1['tr'] as $k2 => $v2)
                        {
                            if ($v2['colspan'] ==16)    //判断跨列的单元格
                            {
                                $hebing = 'A'.$i.':'.'P'.$i;      //合并那些单元格
                                $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $kj);
                            }
                            else
                            {
                                //单元格赋值
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2).$i, $v2['value'] );
                            }
                        }
                        $i++;
                    }
                }
                unset($temp);
                $objPHPExcel->getActiveSheet()->freezePane('A2');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-fdmkcbmx-'.$date.'.xls';

//                 $keys = '/var/www/excel/'.$vde['id'].'-fdmkcbmx-'.$date.'.xls';
                $objWriter1 = new \PHPExcel_Writer_Excel5($objPHPExcel);
                if ($bumen_id !='')
                {
                    $fileName = $vde['id'].'-fdmkcbmx-'.$date.'.xls';
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"$fileName\"");
                    header('Cache-Control: max-age=0');
                    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $objWriter->save('php://output'); //文件通过浏览器下载
                    return;
                }
                $objWriter1->save($keys);
				ob_clean();
                unset($objPHPExcel);
                unset($objWriter1);
                //执行上传的excel文件,返回文件的下载地址
 //               $revurl = uploadfile_ali_160112($keys);
//				$xls = json_decode($revurl,true);
				$keys = "http://xsrb.wsy.me:801/files/".$vde['id'].'-fdmkcbmx-'.$date.'.xls';
				$cxo = M()->query("select * from xsrb_excel where `biao` ='fdmkcbmx' and dept_id =".$vde['id']." and `date` =".$date);
                if(!count($cxo))
				{
					$cxo = 1;
					if ($keys !='')
					{
						//当前部门的文件下载地址存入数据库
						$sql3 = "insert into xsrb_excel(`dept_id`,`biao`,`date`,`url`) values(".$vde['id'].",'fdmkcbmx',$date,'$keys')";
						M()->execute($sql3);
					}
				}
				$ret = -1;
            }
        }
         if($ret ==1)
            return '{"resultcode":1,"resultmsg":"防盗门库存表明细表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"防盗门库存表明细表上传失败"}';		
    }
    
    //下载excel
    public function toexcel($token='',$date='')
    {
        header("Access-Control-Allow-Origin: *");
        switch ($this->_method)
        {
            case 'get':
                {
                    //token检测
                    $userinfo = checktoken($token);
                    if (! $userinfo) {
                        $this->response(retmsg(- 2), 'json');
                        return;
                    }
                    $dept_id = $userinfo['dept_id'];
                    //日期选择
                    if ($date == '') {
                        $date = date("Ymd",strtotime("-1 day"));
                    } else {
                        if ($date >= date('Ymd'))
                            $date = date("Ymd",strtotime("-1 day"));
                        else
                        $date = date("Ymd", strtotime($date));
                    }
                    $sql = "select * from xsrb_excel where biao ='fdmkcbmx' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
                    $result = M()->query($sql);
                    if (count($result))
                    {
						if($_SERVER['SERVER_NAME'] =='172.16.10.252')
						{
							$excel_url ="http://172.16.10.252/files/".$dept_id."-".$result[0]['biao']."-".$date.".xls" ;
						}else
						{
							$excel_url =$result[0]['url'];
						}
    					$arr = array(
    						'excel_url'=>$excel_url
    					);
    					
                    }else 
                    {
                        $arr = array(
    						'excel_url'=>C('Controller_url')."/FDMKCBMX/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
}