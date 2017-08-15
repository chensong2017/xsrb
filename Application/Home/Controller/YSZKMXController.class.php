<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class YSZKMXController extends RestController
{
    /**
     * 应收账款明细查询
     * @param string $token
     * @param string $date
     * @param string $pagesize
     * @param string $page
     * @param string $type
     * @param string $id
     * @return mixed
     */
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
        $title = json_decode('{"page":1,"pagesize":20,"total":2,"data":[{"tr":[{"dataType":0,"value":"部门","rowspan":2,"colspan":1},{"dataType":0,"value":"类别","rowspan":2,"colspan":1},{"dataType":0,"value":"对方部门","rowspan":2,"colspan":1},{"dataType":0,"value":"直发","rowspan":1,"colspan":2},{"dataType":0,"value":"库房","rowspan":1,"colspan":2},{"dataType":0,"value":"合计","rowspan":1,"colspan":2}]},{"tr":[{"dataType":0,"value":"新增欠款","rowspan":1,"colspan":1},{"dataType":0,"value":"收回欠款","rowspan":1,"colspan":1},{"dataType":0,"value":"新增欠款","rowspan":1,"colspan":1},{"dataType":0,"value":"收回欠款","rowspan":1,"colspan":1},{"dataType":0,"value":"新增欠款","rowspan":1,"colspan":1},{"dataType":0,"value":"收回欠款","rowspan":1,"colspan":1}]}]}',true);
        $dept = M()->query($sql);
        foreach ($dept as $k1 => $v1)
        {
            $title5 = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"类别"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"收回欠款"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"收回欠款"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"收回欠款"}]}',true);
            $title4 = json_decode('{"tr":[{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"类别"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"收回欠款"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"收回欠款"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"新增欠款"},{"dataType":0,"value":"0","rowspan":1,"colspan":1,"type":"收回欠款"}]}',true);
            $yszkmx = M()->query('select * from yszkmx where depart_id ='.$v1['id'].' and createtime ='.$date);
            if (count($yszkmx))
            {
                foreach ($yszkmx as $k2 => $v2)
                {
                    if ($k2 ==0)
                    {
                        $title5['tr'][0]['value'] = $v1['dname'];
                        $title5['tr'][0]['rowspan'] = count($yszkmx);
                        $title5['tr'][1]['value'] = ($v2['type'] =='其中:直发' || $v2['type'] =='其中:库房' || $v2['type'] =='防盗门')?'防盗门产品':$v2['type'];
                        $title5['tr'][2]['value'] = $v2['khmc'];
                        $title5['tr'][3]['value'] = $v2['type']=='其中:直发'?$v2['increase']:0;
                        $title5['tr'][4]['value'] = $v2['type']=='其中:直发'?$v2['takeback']:0;
                        $title5['tr'][5]['value'] = $v2['type']=='其中:库房'?$v2['increase']:0;
                        $title5['tr'][6]['value'] = $v2['type']=='其中:库房'?$v2['takeback']:0;
                        $title5['tr'][7]['value'] = $v2['increase'];
                        $title5['tr'][8]['value'] = $v2['takeback'];
                        $arr_click[] = $title5;
                    }elseif ($k2 >0)
                    {
                        $title4['tr'][0]['value'] = ($v2['type'] =='其中:直发' || $v2['type'] =='其中:库房' || $v2['type'] =='防盗门')?'防盗门产品':$v2['type'];
                        $title4['tr'][1]['value'] = $v2['khmc'];
                        $title4['tr'][2]['value'] = $v2['type']=='其中:直发'?$v2['increase']:0;
                        $title4['tr'][3]['value'] = $v2['type']=='其中:直发'?$v2['takeback']:0;
                        $title4['tr'][4]['value'] = $v2['type']=='其中:库房'?$v2['increase']:0;
                        $title4['tr'][5]['value'] = $v2['type']=='其中:库房'?$v2['takeback']:0;
                        $title4['tr'][6]['value'] = $v2['increase'];
                        $title4['tr'][7]['value'] = $v2['takeback'];
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
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','类别');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','对方部门');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','直发');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','库房');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','合计');
                $objPHPExcel->getActiveSheet(0)->mergeCells( 'A1:A2' );
                $objPHPExcel->getActiveSheet(0)->mergeCells( 'B1:B2' );
                $objPHPExcel->getActiveSheet(0)->mergeCells( 'C1:C2' );
                $objPHPExcel->getActiveSheet(0)->mergeCells( 'D1:E1' );
                $objPHPExcel->getActiveSheet(0)->mergeCells( 'F1:G1' );
                $objPHPExcel->getActiveSheet(0)->mergeCells( 'H1:I1' );

                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2','新增预收款');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2','减少预收款');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2','新增预收款');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2','减少预收款');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2','新增预收款');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2','减少预收款');
                
                $i =3;
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
                            if (count($v2['tr']) ==9)
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
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:I2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:I2')->getFill()->getStartColor()->setARGB("0099CCFF");  //浅蓝色
                $objPHPExcel->getActiveSheet()->freezePane('A3');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$v1['id'].'-yszkmx-'.$date.'.xls';
                
                if ($bumen_id !='')
                {
                    $fileName = $v1['id'].'-yszkmx-'.$date.'.xls';
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"$fileName\"");
                    header('Cache-Control: max-age=0');
                    $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);;
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
						$sql3 = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$v1['id'].",'yszkmx',$date,'$keys')";
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