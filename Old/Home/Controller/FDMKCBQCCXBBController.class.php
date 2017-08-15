<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class FDMKCBQCCXBBController extends RestController
{
    //防盗门库存表期初查询表
    public function search($token ='',$date ='',$page ='',$pagesize ='',$type ='',$id ='')
    {
        header("Access-Control-Allow-Origin: *");
        if ($type =='')
        {
            //token检测
			$userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept_id = $userinfo['dept_id'];
        }
        if ($type =='excel')
        {
            $dept_id = $id;
        }
        //日期选择
        if ($date == '') {
            $date = date("Y-m");
        } else {
            $date = date("Y-m", strtotime($date));
        }
        // 分页
        if ($page <= 0)
        {
            $page = 1;
        }
        if ($pagesize <=0)
        {
            $pagesize = 10;
        }
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname,pid from xsrb_department where pid != 1  and pid !=0 order by pid desc,qt1,id ";
        } else
        {
            // 片区部门
            $sql = "select id,dname,pid from xsrb_department where qt2 like '%." . $dept_id . "' order by pid desc,qt1,id";
        
            // 判断部门(非片区and总部)的查询
            $pagination = M()->query($sql);
            $count = count($pagination); //部门数
            //部门查询时,只显示本部门
            if ($count == 0)
            {
                $sql = "select * from xsrb_department where id =" . $dept_id;
            }
        }
		$handle1=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/qc_json1.txt"),'r');
		$qc_json1=fread($handle1, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/qc_json1.txt")));
		fclose($handle1);
		$handle2=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/qc_json2.txt"),'r');
		$qc_json2=fread($handle2, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/qc_json2.txt")));
		fclose($handle2);			
        $count = ceil(count(M()->query($sql))/$pagesize);	//计算页数
        if ($type =='')
        {
            $dept = M()->query($sql.' limit '.($page-1)*$pagesize.','.$pagesize);
			//左标题
            //top标题与content
            $left_title = json_decode($qc_json1,true);
            foreach ($dept as $k1 => $v1)
            {
    			$data = json_decode($qc_json2,true);
				//查询部门属于的大区信息
                if ($v1['pid'] ==0)
                {
                    $v1['pid'] = 1;
                }
                $daqu = M()->query('select dname from xsrb_department where `id` ='.$v1['pid']);
				
				//查询数据库赋值在一个临时数组里面
                $fdmkcbqccxb = M()->query("select * from fdmkcbqc where dept =".$v1['id']." and `date` ='".$date."'");
                if (count($fdmkcbqccxb))
                {
                    foreach ($fdmkcbqccxb as $k2=>$v2)
                    {
                        $arr[$v2['product'].'结存量'] = $v2['yxjc'];
                        $arr[$v2['product'].'无效结存量'] = $v2['wxjc'];
                    }
                    foreach ($data['content'] as $k3 => $v3)
                    {
                        foreach ($v3['tr'] as $k4 => $v4)
                        {
                            if ($arr[$v4['product'].$v4['type_detail']] != '')
                            {
                                $data['content'][$k3]['tr'][$k4]['value'] = $arr[$v4['product'].$v4['type_detail']];
                            }
                        }
                    }
                }
				//设置大区名,部门名称
                $data['topTitle'][0]['tr'][0]['value'] = $daqu[0]['dname'];
                $data['topTitle'][1]['tr'][0]['value'] = $v1['dname'];
                $left_title['data'][] = $data;
                unset($data);
            }
            $left_title['page'] = $page;
            $left_title['total'] = $count;
            $left_title['pagesize'] = $pagesize;
            echo json_encode($left_title);
        }
        
        if($type =='excel')
        {
            
            $dept = M()->query($sql);
            foreach ($dept as $k1 => $v1)
            {
				$handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/qc_json3.txt"),'r');
				$qc_json3=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/qc_json3.txt")));
				fclose($handle);
                $data  = json_decode($qc_json3,true);
				//查询部门属于的大区信息
                if ($v1['pid'] ==0)
                {
                    $v1['pid'] = 1;
                }
                $daqu = M()->query('select dname from xsrb_department where `id` ='.$v1['pid']);
                $fdmkcbqccxb = M()->query("select * from fdmkcbqc where dept =".$v1['id']." and `date` ='".$date."'");
                if (count($fdmkcbqccxb))
                {
                    foreach ($fdmkcbqccxb as $k2=>$v2)
                    {
                        $arr[$v2['product'].'结存量'] = $v2['yxjc'];
                        $arr[$v2['product'].'无效结存量'] = $v2['wxjc'];
                    }
                    foreach ($data['content'] as $k3 => $v3)
                    {
                        foreach ($v3['tr'] as $k4 => $v4)
                        {
                            if ($arr[$v4['product'].$v4['type_detail']] != '')
                            {
                                $data['content'][$k3]['tr'][$k4]['value'] = $arr[$v4['product'].$v4['type_detail']];
                            }
                        }
                    }
                }
                $data['content'][0]['tr'][0]['value'] = $daqu[0]['dname'];
                $data['content'][1]['tr'][0]['value'] = $v1['dname'];
                $left_title[] = $data;
                unset($data);
            }
            return $left_title;
        }
        
    }
    
    //防盗门库存表期初查询表 生成excel文件并上传
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
        $sql = "select id from xsrb_department where id =1 or qt1 =0";
        $dept_id = M()->query($sql);
        $sheet = 0;
        if ($bumen_id !='' && isset($bumen_id))
        {
            $dept_id = array(
                array('id' =>$bumen_id)
            );
        }
        foreach ($dept_id as $kde => $vde)
        {
            $cx = M()->query("select * from xsrb_excel where `biao` ='fdmkcbqccxbb' and dept_id =".$vde['id']." and `date` =".$date);
            if (!count($cx))
            {
                //new一个phpexcel
                $objPHPExcel = new \PHPExcel();
                //设置excel标题
                \PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
                $json = $this->search('',$date,'','','excel',$vde['id']);
                   
                foreach ($json as $kj => $vj)
                {
                    if (is_int($kj/128))
                    {
                        //当超过126个部门的时候,设置下一个sheet页
                        $sheet = floor($kj/128);
                        $objPHPExcel->createSheet($sheet);		//创建一个sheet
                        $objPHPExcel->setactivesheetindex($sheet);
    					
    					//给excel设置左标题(已取消)
                    }
    				//excel列标从A开始循环到IV
                    $currentColumn = 'A';
                    for ($i = 1; $i <= 256; $i++)
                    {
                        $a[] = $currentColumn++;
                    }
    				//单元格赋值
                    foreach ($vj['content'] as $kj1 => $vj1)
                    {
                        foreach ($vj1['tr'] as $kj2 => $vj2)
                        {
                            if ($vj2['colspan'] != 1)
                            {
                                $hebing = $a[$kj*2-256*$sheet].($kj1+1).':'.$a[$kj*2+1-256*$sheet].($kj1+$vj2['rowspan']);
                                $objPHPExcel->getActiveSheet()->mergeCells($hebing);
                                $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( $a[$kj*2-256*$sheet].($kj1+1), $vj2['value'] );
                            }else 
                            {
                                if ($kj2 ==0)
                                $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( $a[$kj*2-256*$sheet].($kj1+1), $vj2['value'] );
                                else 
                                $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( $a[$kj*2+1-256*$sheet].($kj1+1), $vj2['value'] );
                            }
                        }
    
                    }
                }
                $objPHPExcel->getActiveSheet()->freezePane('A2');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-fdmkcbqccxbb-'.$date.'.xls';
                
                if ($bumen_id !='')
                {
                    $fileName = $vde['id'].'-fdmkcbqccxbb-'.$date.'.xls';
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"$fileName\"");
                    header('Cache-Control: max-age=0');
                    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                    $objWriter->save('php://output'); //文件通过浏览器下载
                    return;
                }
                $objWriter1 = new \PHPExcel_Writer_Excel5($objPHPExcel);
                $objWriter1->save($keys);
				ob_clean();
                //执行上传的excel文件,返回文件的下载地址
//                $revurl = uploadfile_ali_160112($keys);
//				$xls = json_decode($revurl,true);
				$keys = "http://xsrb.wsy.me:801/files/".$vde['id'].'-fdmkcbqccxbb-'.$date.'.xls';
                $cxo = M()->query("select * from xsrb_excel where `biao` ='fdmkcbqccxbb' and dept_id =".$vde['id']." and `date` =".$date);            
                if(!count($cxo))
				{
					$cxo =1;
					if ($keys !='')
					{
						//当前部门的文件下载地址存入数据库
						$sql = "insert into xsrb_excel(`dept_id`,`biao`,`date`,`url`) values(".$vde['id'].",'fdmkcbqccxbb','".$date."','$keys')";
						M()->execute($sql);
					}				
				}

				$ret = -1;
            }
        }
         if($ret ==1)
            return '{"resultcode":1,"resultmsg":"防盗门库存表期初查询表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"防盗门库存表期初查询表上传失败"}';		
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
                    $sql = "select * from xsrb_excel where biao ='fdmkcbqccxbb' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
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
    						'excel_url'=>C('Controller_url')."/FDMKCBQCCXBB/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
}