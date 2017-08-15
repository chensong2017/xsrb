<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class SPMXController extends RestController
{
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
		if($type =='zfm')
		{
			echo "111333333333";return;exit;
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
        $depart = M()->query($sql);
        $title = json_decode('{"page":1,"pagesize":20,"total":2,"data":[{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"类别","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"有效/无效","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"对方部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"防盗门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"三代机","rowspan":1,"colspan":1,"type":""}]}]}','json');
        foreach ($depart as $k1 => $v1)
        {
            $title5 = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"类别"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"有效/无效"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"防盗门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"三代机"}]}',true);
            $title6 = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"类别"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"有效/无效"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"防盗门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"三代机"}]}',true);
            $title4 = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"有效/无效"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"防盗门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"三代机"}]}',true);
            $title3 = json_decode('{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"对方部门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"防盗门"},{"dataType":0,"value":"","rowspan":1,"colspan":1,"type":"三代机"}]}',true);
            
                $spmx1 = M()->query("select * from hwdbmx where xmmc = '外购入库' and dept =".$v1['id']." and `date` =".$date );
                $spmx2 = M()->query("select * from hwdbmx where xmmc = '调拨支出' and dept =".$v1['id']." and `date` =".$date );
                $spmx3 = M()->query("select * from hwdbmx where xmmc = '调拨收入' and dept =".$v1['id']." and `date` =".$date );
                $count1 = count($spmx1);
                $count2 = count($spmx2);
                $count3 = count($spmx3);

                if ($count1 >0 || $count2 >0 || $count3 >0 )
                {
					$spmx21 = M()->query("select * from hwdbmx where xmmc = '调拨支出' and xmlb = '无效支出' and dept =".$v1['id']." and `date` =".$date );
					$spmx22 = M()->query("select * from hwdbmx where xmmc = '调拨支出' and xmlb = '有效支出' and dept =".$v1['id']." and `date` =".$date );
					$spmx31 = M()->query("select * from hwdbmx where xmmc = '调拨收入' and xmlb = '无效收入' and dept =".$v1['id']." and `date` =".$date );
					$spmx32 = M()->query("select * from hwdbmx where xmmc = '调拨收入' and xmlb = '有效收入' and dept =".$v1['id']." and `date` =".$date );
 					$count21 = count($spmx21);
					$count22 = count($spmx22);
					$count31 = count($spmx31);
					$count32 = count($spmx32);					
                    if ($count1)
                    {
                        foreach ($spmx1 as $k2 => $v2)
                        {
                            if ($k2 == 0)
                            {
                                $title6['tr'][0]['value'] = $v1['dname'];
                                $title6['tr'][0]['rowspan'] = $count1 + $count2 + $count3;
                                $title6['tr'][1]['value'] = $v2['xmmc'];
                                $title6['tr'][1]['rowspan'] = $count1;
                                $title6['tr'][2]['value'] = $v2['xmlb'];
                                $title6['tr'][2]['rowspan'] = $count1;
                                $title6['tr'][3]['value'] = $v2['qtbm'];
                                $title6['tr'][4]['value'] = $v2['fdm'];
                                $title6['tr'][5]['value'] = $v2['sdj'];
                                $arr_click[] = $title6;
                            }elseif ($k2 >0)
                            {
                                $title3['tr'][0]['value'] = $v2['qtbm'];
                                $title3['tr'][1]['value'] = $v2['fdm'];
                                $title3['tr'][2]['value'] = $v2['sdj'];
                                $arr_click[] = $title3;
                            }
                        }
                    }
                    if ($count2)								//显示调拨支出数据
                    {
                        if ($count1 ==0)
                        {
                            if ($count21)
                            {
                                foreach ($spmx21 as $k21 => $v21)
                                {
                                    if ($k21 == 0)
                                    {
                                        $title6['tr'][0]['value'] = $v1['dname'];
                                        $title6['tr'][0]['rowspan'] = $count1 + $count2 + $count3;
                                        $title6['tr'][1]['value'] = $v21['xmmc'];
                                        $title6['tr'][1]['rowspan'] = $count21+$count22;
                                        $title6['tr'][2]['value'] = $v21['xmlb'];
                                        $title6['tr'][2]['rowspan'] = $count21;
                                        $title6['tr'][3]['value'] = $v21['qtbm'];
                                        $title6['tr'][4]['value'] = $v21['fdm'];
                                        $title6['tr'][5]['value'] = $v21['sdj'];
                                        $arr_click[] = $title6;
                                    }elseif ($k21 >0)
                                    {
                                        $title3['tr'][0]['value'] = $v21['qtbm'];
                                        $title3['tr'][1]['value'] = $v21['fdm'];
                                        $title3['tr'][2]['value'] = $v21['sdj'];
                                        $arr_click[] = $title3;
                                    }
                                }
                                foreach ($spmx22 as $k22 => $v22)
                                {
                                    if ($k22 == 0)
                                    {
                                        $title4['tr'][0]['value'] = $v22['xmlb'];
                                        $title4['tr'][0]['rowspan'] = $count22;
                                        $title4['tr'][1]['value'] = $v22['qtbm'];
                                        $title4['tr'][2]['value'] = $v22['fdm'];
                                        $title4['tr'][3]['value'] = $v22['sdj'];
                                        $arr_click[] = $title4;
                                    }elseif ($k22 >0)
                                    {
                                        $title3['tr'][0]['value'] = $v22['qtbm'];
                                        $title3['tr'][1]['value'] = $v22['fdm'];
                                        $title3['tr'][2]['value'] = $v22['sdj'];
                                        $arr_click[] = $title3;
                                    }
                                }
                            }else
                            {
                                foreach ($spmx22 as $k22 => $v22)
                                {
                                    if ($k22 == 0)
                                    {
                                        $title6['tr'][0]['value'] = $v1['dname'];
                                        $title6['tr'][0]['rowspan'] = $count1 + $count2 + $count3;										
                                        $title6['tr'][1]['value'] = $v22['xmmc'];
                                        $title6['tr'][1]['rowspan'] = $count21+$count22;
                                        $title6['tr'][2]['value'] = $v22['xmlb'];
                                        $title6['tr'][2]['rowspan'] = $count22;
                                        $title6['tr'][3]['value'] = $v22['qtbm'];
                                        $title6['tr'][4]['value'] = $v22['fdm'];
                                        $title6['tr'][5]['value'] = $v22['sdj'];
                                        $arr_click[] = $title6;
                                    }elseif ($k22 >0)
                                    {
                                        $title3['tr'][0]['value'] = $v22['qtbm'];
                                        $title3['tr'][1]['value'] = $v22['fdm'];
                                        $title3['tr'][2]['value'] = $v22['sdj'];
                                        $arr_click[] = $title3;
                                    }
                                }
                            }
                        }elseif ($count1 !=0)
                        {
							if($count21)
							{
								foreach ($spmx21 as $k21 => $v21)
								{
									if ($k21 == 0)
									{
										$title5['tr'][0]['value'] = $v21['xmmc'];
										$title5['tr'][0]['rowspan'] = $count21+$count22;
										$title5['tr'][1]['value'] = $v21['xmlb'];
										$title5['tr'][1]['rowspan'] = $count21;
										$title5['tr'][2]['value'] = $v21['qtbm'];
										$title5['tr'][3]['value'] = $v21['fdm'];
										$title5['tr'][4]['value'] = $v21['sdj'];
										$arr_click[] = $title5;
									}elseif ($k21 >0)
									{
										$title3['tr'][0]['value'] = $v21['qtbm'];
										$title3['tr'][1]['value'] = $v21['fdm'];
										$title3['tr'][2]['value'] = $v21['sdj'];
										$arr_click[] = $title3;
									}
								}
								foreach ($spmx22 as $k22 => $v22)
								{
									if ($k22 == 0)
									{
										$title4['tr'][0]['value'] = $v22['xmlb'];
										$title4['tr'][0]['rowspan'] = $count22;
										$title4['tr'][1]['value'] = $v22['qtbm'];
										$title4['tr'][2]['value'] = $v22['fdm'];
										$title4['tr'][3]['value'] = $v22['sdj'];
										$arr_click[] = $title4;
									}elseif ($k22 >0)
									{
										$title3['tr'][0]['value'] = $v22['qtbm'];
										$title3['tr'][1]['value'] = $v22['fdm'];
										$title3['tr'][2]['value'] = $v22['sdj'];
										$arr_click[] = $title3;
									}
								}
							}else
							{
								foreach ($spmx22 as $k22 => $v22)
								{
									if ($k22 == 0)
									{
										$title5['tr'][0]['value'] = $v22['xmmc'];
										$title5['tr'][0]['rowspan'] = $count21+$count22;
										$title5['tr'][1]['value'] = $v22['xmlb'];
										$title5['tr'][1]['rowspan'] = $count22;
										$title5['tr'][2]['value'] = $v22['qtbm'];
										$title5['tr'][3]['value'] = $v22['fdm'];
										$title5['tr'][4]['value'] = $v22['sdj'];
										$arr_click[] = $title5;
									}elseif ($k22 >0)
									{
										$title3['tr'][0]['value'] = $v22['qtbm'];
										$title3['tr'][1]['value'] = $v22['fdm'];
										$title3['tr'][2]['value'] = $v22['sdj'];
										$arr_click[] = $title3;
									}
								}
							}
                        }
                    }
                    if ($count3)						//显示调拨收入数据
                    {
                        if ($count1 ==0 && $count2 ==0)
                        {
                            if ($count31)
                            {
                                foreach ($spmx31 as $k31 => $v31)
                                {
                                    if ($k31 == 0)
                                    {
                                        $title6['tr'][0]['value'] = $v1['dname'];
                                        $title6['tr'][0]['rowspan'] = $count1 + $count2 + $count3;
                                        $title6['tr'][1]['value'] = $v31['xmmc'];
                                        $title6['tr'][1]['rowspan'] = $count31+$count32;
                                        $title6['tr'][2]['value'] = $v31['xmlb'];
                                        $title6['tr'][2]['rowspan'] = $count31;
                                        $title6['tr'][3]['value'] = $v31['qtbm'];
                                        $title6['tr'][4]['value'] = $v31['fdm'];
                                        $title6['tr'][5]['value'] = $v31['sdj'];
                                        $arr_click[] = $title6;
                                    }elseif ($k31 >0)
                                    {
                                        $title3['tr'][0]['value'] = $v31['qtbm'];
                                        $title3['tr'][1]['value'] = $v31['fdm'];
                                        $title3['tr'][2]['value'] = $v31['sdj'];
                                        $arr_click[] = $title3;
                                    }
                                }
                                foreach ($spmx32 as $k32 => $v32)
                                {
                                    if ($k32 == 0)
                                    {
                                        $title4['tr'][0]['value'] = $v32['xmlb'];
                                        $title4['tr'][0]['rowspan'] = $count32;
                                        $title4['tr'][1]['value'] = $v32['qtbm'];
                                        $title4['tr'][2]['value'] = $v32['fdm'];
                                        $title4['tr'][3]['value'] = $v32['sdj'];
                                        $arr_click[] = $title4;
                                    }elseif ($k32 >0)
                                    {
                                        $title3['tr'][0]['value'] = $v32['qtbm'];
                                        $title3['tr'][1]['value'] = $v32['fdm'];
                                        $title3['tr'][2]['value'] = $v32['sdj'];
                                        $arr_click[] = $title3;
                                    }
                                }
                            }else
                            {
                                foreach ($spmx32 as $k32 =>$v32)
                                {
                                    if ($k32 == 0)
                                    {
                                        $title6['tr'][0]['value'] = $v1['dname'];
                                        $title6['tr'][0]['rowspan'] = $count1 + $count2 + $count3;
                                        $title6['tr'][1]['value'] = $v32['xmmc'];
                                        $title6['tr'][1]['rowspan'] = $count31+$count32;
                                        $title6['tr'][2]['value'] = $v32['xmlb'];
                                        $title6['tr'][2]['rowspan'] = $count32;
                                        $title6['tr'][3]['value'] = $v32['qtbm'];
                                        $title6['tr'][4]['value'] = $v32['fdm'];
                                        $title6['tr'][5]['value'] = $v32['sdj'];
                                        $arr_click[] = $title6;
                                    }elseif ($k32 >0)
                                    {
                                        $title3['tr'][0]['value'] = $v32['qtbm'];
                                        $title3['tr'][1]['value'] = $v32['fdm'];
                                        $title3['tr'][2]['value'] = $v32['sdj'];
                                        $arr_click[] = $title3;
                                    }
                                }
                            }
                        }elseif ($count1 !=0 || $count2 !=0)
                        {
							if($count31)
							{
								foreach ($spmx31 as $k31 => $v31)
								{
									if ($k31 == 0)
									{
										$title5['tr'][0]['value'] = $v31['xmmc'];
										$title5['tr'][0]['rowspan'] = $count31+$count32;
										$title5['tr'][1]['value'] = $v31['xmlb'];
										$title5['tr'][1]['rowspan'] = $count31;
										$title5['tr'][2]['value'] = $v31['qtbm'];
										$title5['tr'][3]['value'] = $v31['fdm'];
										$title5['tr'][4]['value'] = $v31['sdj'];
										$arr_click[] = $title5;
									}elseif ($k31 >0)
									{
										$title3['tr'][0]['value'] = $v31['qtbm'];
										$title3['tr'][1]['value'] = $v31['fdm'];
										$title3['tr'][2]['value'] = $v31['sdj'];
										$arr_click[] = $title3;
									}
								}
								foreach ($spmx32 as $k32 => $v32)
								{
									if ($k32 == 0)
									{
										$title4['tr'][0]['value'] = $v32['xmlb'];
										$title4['tr'][0]['rowspan'] = $count32;
										$title4['tr'][1]['value'] = $v32['qtbm'];
										$title4['tr'][2]['value'] = $v32['fdm'];
										$title4['tr'][3]['value'] = $v32['sdj'];
										$arr_click[] = $title4;
									}elseif ($k32 >0)
									{
										$title3['tr'][0]['value'] = $v32['qtbm'];
										$title3['tr'][1]['value'] = $v32['fdm'];
										$title3['tr'][2]['value'] = $v32['sdj'];
										$arr_click[] = $title3;
									}
								}
							}else
							{
								foreach ($spmx32 as $k32 => $v32)
								{
									if ($k32 == 0)
									{
										$title5['tr'][0]['value'] = $v32['xmmc'];
										$title5['tr'][0]['rowspan'] = $count31+$count32;
										$title5['tr'][1]['value'] = $v32['xmlb'];
										$title5['tr'][1]['rowspan'] = $count32;
										$title5['tr'][2]['value'] = $v32['qtbm'];
										$title5['tr'][3]['value'] = $v32['fdm'];
										$title5['tr'][4]['value'] = $v32['sdj'];
										$arr_click[] = $title5;
									}elseif ($k32 >0)
									{
										$title3['tr'][0]['value'] = $v32['qtbm'];
										$title3['tr'][1]['value'] = $v32['fdm'];
										$title3['tr'][2]['value'] = $v32['sdj'];
										$arr_click[] = $title3;
									}
								}
							}
                        }
                    }
                }
                else
                {
                    $title6['tr'][0]['value'] = $v1['dname'];
                    $arr_click[] = $title6;
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
    
    //生成excel文件
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
        if ($bumen_id !='' && isset($bumen_id))
        {
            $dept_id = array(
                array('id' =>$bumen_id)
            );
        }
        foreach ($dept_id as $k1 => $v1)
        {
            $cx = M()->query("select * from xsrb_excel where `biao` ='spmx' and dept_id =".$v1['id']." and `date` =".$date);
            if (!count($cx))
            {
                //new一个phpexcel
                $objPHPExcel = new \PHPExcel();
                //设置excel标题
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','类别');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','有效/无效');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','对方部门');            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','防盗门');$objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','三代机');
                $i =2;
                $json = $this->search('',$date,'','','excel',$v1['id']);
                foreach ($json['data'] as $k2 => $v2)
                {
                    foreach ($v2['tr'] as $k3 => $v3)
                    {
                        if (count($v2['tr']) ==6)
                        {
                            if ($v3['rowspan'] != 1)
                            {
                                $hebing = chr(ord('A')+$k3).$i.':'.chr(ord('A')+$k3).($i+$v3['rowspan']-1);      //合并那些单元格
                                $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                $a = chr(ord('A')+$k3);
                            }
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k3).$i, $v3['value']);
                        }elseif (count($v2['tr']) ==3)
                        {
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('D')+$k3).$i, $v3['value']);
                        }elseif (count($v2['tr']) ==5)
                        {
                            if ($v3['rowspan'] != 1)
                            {
                                $hebing = chr(ord('B')+$k3).$i.':'.chr(ord('B')+$k3).($i+$v3['rowspan']-1);      //合并那些单元格
                                $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                            }
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('B')+$k3).$i, $v3['value']);
                        }elseif (count($v2['tr']) ==4)
                        {
                            if ($v3['rowspan'] != 1)
                            {
                                $hebing = chr(ord('C')+$k3).$i.':'.chr(ord('C')+$k3).($i+$v3['rowspan']-1);      //合并那些单元格
                                $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                            }
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('C')+$k3).$i, $v3['value']);
                        }
                    }
                    $i++;
                }
                $objPHPExcel->getActiveSheet()->freezePane('A2');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$v1['id'].'-spmx-'.$date.'.xls';
                
                if ($bumen_id !='')
                {
                    $fileName = $v1['id'].'-spmx-'.$date.'.xls';
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
 //               $revurl = uploadfile_ali_160112($keys);
//				$xls = json_decode($revurl,true);
	            $keys = "http://xsrb.wsy.me:801/files/".$v1['id'].'-spmx-'.$date.'.xls';
				$cxo = M()->query("select * from xsrb_excel where `biao` ='spmx' and dept_id =".$v1['id']." and `date` =".$date);
				if(!count($cxo))
				{
					$cxo =1;
	                if ($keys !='')
					{
						//当前部门的文件下载地址存入数据库
						$sql = "insert into xsrb_excel(`dept_id`,`biao`,`date`,`url`) values(".$v1['id'].",'spmx',$date,'$keys')";
						M()->execute($sql);
					}				
				}

				$ret = -1;
            }
        }
         if($ret ==1)
            return '{"resultcode":1,"resultmsg":"商品明细表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"商品明细报表上传失败"}';		
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
                    $sql = "select * from xsrb_excel where biao ='spmx' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
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
    						'excel_url'=>C('Controller_url')."/SPMX/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
    
}