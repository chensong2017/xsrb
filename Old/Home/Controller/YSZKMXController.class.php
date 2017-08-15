<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class YSZKMXController extends RestController
{    
    public function search($token ='',$date='',$pagesize ='',$page ='',$type ='',$id ='')
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
            $date = date("Ymd");
        } else {
            $date = date("Ymd", strtotime($date));
        }
        // 分页
        if ($page <= 0) {
            $page = 1;
        }
        if ($pagesize <= 0) {
            $pagesize = 20;
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
        $title = json_decode('{"page":1,"pagesize":20,"total":2,"data":[{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"类别","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"对方部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"新增欠款","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"收回欠款","rowspan":1,"colspan":1,"type":""}]}]}',true);
        $dept = M()->query($sql);
        foreach ($dept as $k1 => $v1)
        {
            $title5 = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"类别"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"收回欠款"}]}',true);
            $title4 = json_decode('{"tr":[{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"类别"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"收回欠款"}]}',true);
            $yszkmx = M()->query('select * from yszkmx where depart_id ='.$v1['id'].' and createtime ='.$date);
            if (count($yszkmx))
            {
                foreach ($yszkmx as $k2 => $v2)
                {
                    if ($k2 ==0)
                    {
                        $title5['tr'][0]['value'] = $v1['dname'];
                        $title5['tr'][0]['rowspan'] = count($yszkmx);
                        $title5['tr'][1]['value'] = $v2['type'];
                        $title5['tr'][2]['value'] = $v2['khmc'];
                        $title5['tr'][3]['value'] = $v2['increase'];
                        $title5['tr'][4]['value'] = $v2['takeback'];
                        $arr_click[] = $title5;
                    }elseif ($k2 >0)
                    {
                        $title4['tr'][0]['value'] = $v2['type'];
                        $title4['tr'][1]['value'] = $v2['khmc'];
                        $title4['tr'][2]['value'] = $v2['increase'];
                        $title4['tr'][3]['value'] = $v2['takeback'];
                        $arr_click[] = $title4;
                    }
                }
            }else 
            {
                $title5['tr'][0]['value'] = $v1['dname'];
                $arr_click[] = $title5;
            }
        }
        if ($type =='')
        {
            $newarr = array_slice($arr_click, ($page-1)*$pagesize, $pagesize);
            for($i=0;$i<count($newarr);$i++)
            {
                $title['data'][] = $newarr[$i];
            }
            $title['page'] = $page;
            $title['pagesize'] = $pagesize;
            $title['total'] = ceil(count($arr_click)/$pagesize);
            echo json_encode($title);
        }
        if ($type =='excel')
        {
            $title1['data'] = $arr_click;
            return $title1;
        }
    }
    
    public function uploadExcel($date ='',$bumen_id = '')
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
        if ($bumen_id !='' && isset($bumen_id))
        {
            $dept_id = array(
                array('id' =>$bumen_id)
            );
        }
        foreach($dept_id as $k1 => $v1)
        {
            $cx = M()->query("select * from xsrb_excel where `biao` ='yszkmx' and dept_id =".$v1['id']." and `date` =".$date);
            if (!count($cx))
            {
                //new一个phpexcel
                $objPHPExcel = new \PHPExcel();
                //设置excel标题
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','类别');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','对方部门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','新增欠款');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','收回欠款');
                
                $i =2;
                $json = $this->search('',$date,'','','excel',$v1['id']);

                foreach ($json['data'] as $k2 => $v2)
                {
                    foreach ($v2['tr'] as $k3 => $v3)
                    {
                        if ($v3['rowspan'] >1)
                        {
                            $hebing = 'A'.$i.':'.'A'.($v3['rowspan']+$i-1);      //合并那些单元格
                            $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $v3['value']);
                        }
                        else
                        {
                            if (count($v2['tr']) ==5)
                            {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k3).$i, $v3['value']);
                            }else 
                            {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('B')+$k3).$i, $v3['value']);
                            }
                        }
                    }
                    $i++;
                }
                //背景色
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:E1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:E1')->getFill()->getStartColor()->setARGB("0099CCFF");  //浅蓝色
                $objPHPExcel->getActiveSheet()->freezePane('A2');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$v1['id'].'-yszkmx-'.$date.'.xls';
                
                if ($bumen_id !='')
                {
                    $fileName = $v1['id'].'-yszkmx-'.$date.'.xls';
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
	//			$revurl = uploadfile_ali_160112($keys);
	//			$xls = json_decode($revurl,true);
				$keys = "http://xsrb.wsy.me:801/files/".$v1['id'].'-yszkmx-'.$date.'.xls';
				$cxo = M()->query("select * from xsrb_excel where `biao` ='yszkmx' and dept_id =".$v1['id']." and `date` =".$date);
				if(!count($cxo))
				{
					$cxo =1;
					if ($keys !='')
					{
						//当前部门的文件下载地址存入数据库
						$sql3 = "insert into xsrb_excel(`dept_id`,`biao`,`date`,`url`) values(".$v1['id'].",'yszkmx',$date,'$keys')";
						M()->execute($sql3);
					}
				}
				$ret = -1;
            }
        }
         if($ret ==1)
            return '{"resultcode":1,"resultmsg":"应收账款明细报表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"应收账款明细报表上传失败"}';		
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
                    $sql = "select * from xsrb_excel where biao ='yszkmx' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
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
    						'excel_url'=>C('Controller_url')."/YSZKMX/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
}