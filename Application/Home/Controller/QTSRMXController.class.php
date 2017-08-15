<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class QTSRMXController extends RestController
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
            $pagesize = 35;
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
        $dept = M()->query($sql);
        $title = json_decode('{"page":1,"pagesize":20,"total":2,"data":[{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"业务类型","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"说明","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"防盗门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"数码产品","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"门配产品","rowspan":1,"colspan":1,"type":""}]}]}',true);
        $content = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"业务类型"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"说明"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"防盗门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"数码产品"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"门配产品"}]}',true);
        foreach ($dept as $k1 =>$v1)
        {
            $qtmx = M()->query('select * from qtmx where dept ='.$v1['id'].' and `date` ='.$date);
            if (count($qtmx))
            {
                foreach ($qtmx as $k2 => $v2)
                {
                    $fdm ='';$sdj ='';$dmb ='';
                    if ($v2['xmlx'] =='经营部资金调入' || $v2['xmlx'] =='代支采购货款')
                    {
                        $fdm = $v2['sum'];
                    }
                    if ($v2['xmlx'] =='代收款' || $v2['xmlx'] =='代支其他部门')
                    {
                        $dmb = $v2['sum'];
                    }
                    $content['tr'][0]['value'] = $v1['dname'];
                    $content['tr'][1]['value'] = $v2['xmlx'];
                    $content['tr'][2]['value'] = $v2['xm'];
                    $content['tr'][3]['value'] = $fdm;
                    $content['tr'][4]['value'] = $sdj;
                    $content['tr'][5]['value'] = $dmb;
                    $arr_click[] = $content;
                }
            }
            $qtsrmx = M()->query('select * from qtsrmx where depart_id ='.$v1['id'].' and `date` ='.$date);
            if (count($qtsrmx))
            {
                foreach ($qtsrmx as $k2 => $v2)
                {
                    $content['tr'][0]['value'] = $v1['dname'];
                    $content['tr'][1]['value'] = $v2['xmmc'];
                    $content['tr'][2]['value'] = $v2['xm'];
                    $content['tr'][3]['value'] = $v2['fdm'];
                    $content['tr'][4]['value'] = $v2['sdj'];
                    $content['tr'][5]['value'] = $v2['dmb'];
                    $arr_click[] = $content;
                }
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
	    
    public function uploadExcel($date ='',$bumen_id ='')
    {
		ini_set('max_execution_time',2000);
        ini_set('memory_limit', "-1");		
        header("Access-Control-Allow-Origin:*");     
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
        foreach ($dept_id as $k1 => $v1)
        {
            $cx = M()->query("select * from xsrb_excel where dept_id =".$v1['id']." and `date` =".$date." and `biao` ='qtsrmx' ");
            if (!count($cx))  //判断此循环下的部门是否已导入
            {
                //new一个phpexcel
                $objPHPExcel = new \PHPExcel();
                //设置excel标题
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','业务类型');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','说明');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','防盗门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','数码产品');$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','门配产品');
                $i =2;
                $json = $this->search('',$date,'','','excel',$v1['id']);
                foreach ($json['data'] as $k2 => $v2)
                {
                    foreach ($v2['tr'] as $k3 => $v3)
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k3).$i, $v3['value']);
                    }
                    $i++;
                }
                $objPHPExcel->getActiveSheet()->freezePane('A2');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$v1['id'].'-qtsrmx-'.$date.'.xls';
                
                $objWriter1 = new \PHPExcel_Writer_Excel5($objPHPExcel);
                if ($bumen_id !='')
                {
                    $fileName = $v1['id'].'-qtsrmx-'.$date.'.xls';
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
 //               $revurl = uploadfile_ali_160112($keys);
//				$xls = json_decode($revurl,true);
				$keys = "http://xsrb.wsy.me:801/files/".$v1['id'].'-qtsrmx-'.$date.'.xls';
				$cxo = M()->query("select * from xsrb_excel where dept_id =".$v1['id']." and `date` =".$date." and `biao` ='qtsrmx' ");
			    if(!count($cxo))
				{
					$cxo =1;
	                if ($keys !='')    //上传成功返回url时,存入数据库
					{                //当前部门的文件下载地址存入数据库
						$sql = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$v1['id'].",'qtsrmx',$date,'$keys')";
						M()->execute($sql);
					}				
				}					
				$ret = -1;
            }
        }
         if($ret ==1)
            return '{"resultcode":1,"resultmsg":"其他收入明细表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"其他收入明细表上传失败"}';		
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
                    $sql = "select * from xsrb_excel where biao ='qtsrmx' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
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
    						'excel_url'=>C('Controller_url')."/QTSRMX/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
}