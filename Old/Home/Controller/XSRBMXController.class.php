<?php
namespace Home\Controller;

use Think\Controller\RestController;

include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class XSRBMXController extends RestController
{
    //销售日报明细
    public function search($token ='',$date ='',$page ='',$pagesize ='',$type ='',$id ='')
    {
        header("Access-Control-Allow-Origin: *");
        if ($type =='')     //
        {
            //token检测
            $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept_id = $userinfo['dept_id'];
        }
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        
        //当upexcel方法调用时处理
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
        
        $xsrblr = new XSRBLRController();

        // 分页
        if ($page <= 0) {
            $page = 1;
        }
        if ($pagesize <= 0)
        {
            $pagesize =1;
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
        }
        $pagination = M()->query($sql);
        $count1 = count($pagination); //部门数
        //部门查询时,只显示本部门
        if ($count1 == 0)
        {
            $sql = "select * from xsrb_department where id =" . $dept_id;
        }
        $count2 = count(M()->query($sql));

        if ($type =='')		//type=''时,表示查询销售日报明细
        {
            $dept = M()->query($sql.' limit '.($page-1).',1');            		

            //获取当前条件下的redis
			$xsrbmx = $redis->get('report-'.$dept[0]['id'].'-'.$date.'-XSRBLR');
			if ($xsrbmx =='')
			{
				$filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");
				$handle=fopen($filename,'r');
				$xsrbmx=fread($handle, filesize($filename));
				fclose($handle);
			}				
			$xsrblr->submit($xsrbmx,$token,'gx','gx',$date,$dept[0]['id']);   //实时更新期初数据
			$xsrbmx = $redis->get("report-".$dept[0]['id']."-$date-XSRBLR");
			
            $temp = json_decode($xsrbmx,true);
            $count3 = count($temp['data'][0]['tr']);		//部门头所占列数
            $dep = array(
                array(
                    'tr' => array(
                        array(
                            'dataType' => 0,
                            'value' => $dept[0]['dname'],
                            'rowspan' => 1,
                            'colspan' => $count3
                        )
                    )
                )
            );
            array_splice($temp['data'], 1, 0, $dep);        //模版-标题头换成部门头
            foreach ($temp['data'] as $sb => $vsb) {        //数据调换位置
                if ($sb <= 31) {
                    $data['data'][] = $vsb;
                }
                if ($sb >= 44 && $sb <= 71) {
                    $data['data'][] = $vsb;
                }
            }
            foreach ($temp['data'] as $sb1 => $vsb1) {      //数据调换位置
                if ($sb1 >= 32 && $sb1 <= 43) {
                    $data['data'][] = $vsb1;
                }
                if ($sb1 >= 72) {
                    $data['data'][] = $vsb1;
                }
            }
            $data['page'] = $page;
            $data['total'] = $count2;
            $this->response(json_encode($data));
        }
        elseif ($type =='excel') 
        {
            $dept = M()->query($sql);
            foreach ($dept as $kk =>$vv)
            {
                $xsrbmx = $redis->get('report-'.$vv['id'].'-'.$date.'-XSRBLR');
				if ($xsrbmx =='')
				{
					$filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");
					$handle=fopen($filename,'r');
					$xsrbmx=fread($handle, filesize($filename));
					fclose($handle);
				}
				$xsrblr->submit($xsrbmx,$token,'gx','gx',$date,$vv['id']);   //获取没有录入天时,计算数据加载正确
				$xsrbmx = $redis->get("report-".$vv['id']."-$date-XSRBLR");

                $xsrb = json_decode($xsrbmx,true);
                foreach ($xsrb['data'] as $k1 => $v1)
                {
                    foreach ($v1['tr'] as $ks=>$vs)
                    {
                        if ($vs['product'] != '')
                        {
                            $arr[$vs['type'].$vs['type_detail'] . $vs['product']] = $vs['value'];   //所有rblr的数据存入shuju数组里面
                        }
                    }
                }
                $aa[$vv['dname']] = $arr;
            }
            return $aa;
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
        $sql = "select id from xsrb_department where id =1 or qt1 =0 ";
        $dept_id = M()->query($sql);
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
		$ret = 1;
		if ($bumen_id !='' && isset($bumen_id))
		{
		    $dept_id = array(
		        array('id' =>$bumen_id)
		    );
		}
        foreach ($dept_id as $kde=>$vde)
        {
            $cx = M()->query("select * from xsrb_excel where `biao` ='xsrbmx' and dept_id =".$vde['id']." and `date` =".$date);
            if (!count($cx))
            {
                //设置excel标题,如果有新产品添加,要在标题里面加一列
                $objPHPExcel = new \PHPExcel();
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门名称');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','业务名称');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','项目类别');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','项目名称');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','防盗门');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','三代机');
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','地面波');
                $i =2;
                $json = array();
                $json = $this->search('',$date,'','','excel',$vde['id']);
                //new一个phpexcel
                //遍历整合过的jsondom,然后给每个单元格赋值,合并
                foreach ($json as $kj => $vj)
                {
                    if (1)      //生成data模版数据
                    {
                        $xsrbmx = $redis->get('report-xsrblr-template');
                        $temp = json_decode($xsrbmx,true);
                        $count = count($temp['data'][0]['tr'])+1;
                        $dep = array(
                            'tr' => array(
                                array(
                                    'dataType' => 0,
                                    'value' => 'depart',
                                    'rowspan' => 1,
                                    'colspan' => $count
                                )
                            )
                        );
                        $temp['data'][0] = $dep;        //模版-标题头换成部门头
                        foreach ($temp['data'] as $kt => $vt) {     //模版-加部门头
                            if ($kt > 0) {
                                foreach ($vt as $ktt => $vtt) {
                                    $dep1 = array(
                                        array(
                                            'dataType' => 0,
                                            'value' => 'depart',
                                            'rowspan' => 1,
                                            'colspan' => 1
                                        )
                                    );
                                    array_splice($temp['data'][$kt][$ktt], 0, 0, $dep1);
                                }
                            }
                        }
                        foreach ($temp['data'] as $sb => $vsb) {        //数据调换位置
                            if ($sb <= 30) {
                                $data['data'][] = $vsb;
                            }
                            if ($sb >= 43 && $sb <= 70) {
                                $data['data'][] = $vsb;
                            }
                        }
                        foreach ($temp['data'] as $sb1 => $vsb1) {       //数据调换位置
                            if ($sb1 >= 31 && $sb1 <= 42) {
                                $data['data'][] = $vsb1;
                            }
                            if ($sb1 >= 71) {
                                $data['data'][] = $vsb1;
                            }
                        }
                    }
                    
                    foreach ($data['data'] as $kt => $vt)           //模版赋值,
                    {
                        foreach ($vt['tr'] as $ke => $ve)
                        {
                            if ($ve['value'] ==='depart')
                            {
                                $data['data'][$kt]['tr'][$ke]['value'] = $kj;
                            }
                            if ($vj[$ve['type'].$ve['type_detail'].$ve['product']] != '')
                            {
                                $data['data'][$kt]['tr'][$ke]['value'] = $vj[$ve['type'].$ve['type_detail'].$ve['product']];
                            }
                        }
                    }
                    foreach ($data['data'] as $k1 => $v1)           //单元格设值,生成excel
                    {
                        $a = '';
                        foreach ($v1['tr'] as $k2 => $v2)
                        {
                            if ($k1 <=58)
                            {
                                if ($v2['colspan'] ==$count)
                                {
                                    $hebing = 'A'.$i.':'.chr(ord('A')+$count-1).$i;      //合并那些单元格
                                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $kj);
                                    $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                }
                                else
                                {
                                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2).$i, $v2['value'] );
                                }
                            }
                            elseif ($k1>58 && $k1 <=62)
                            {
                                if ($v2['colspan'] ==1)
                                {
                                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+$a).$i, $v2['value'] );
                                }else
                                {
                                    $hebing = chr(ord('A')+$k2).$i.':'.chr(ord('A')+$k2+$v2['colspan']-1).$i;
                                    $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2).$i, $v2['value'] );
                                    $a = $v2['colspan']-1;
                                }
                            }
                            elseif ($k1 >62 && $k1 <=70)
                            {
                                if (count($v1['tr']) ==$count)
                                {
                                    if ($v2['rowspan'] ==1)
                                    {
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+$a).$i, $v2['value'] );
                                    }else
                                    {
                                        $hebing = chr(ord('A')+$k2).$i.':'.chr(ord('A')+$k2).($i+$v2['rowspan']-1);
                                        $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2).$i, $v2['value'] );
                                    }
                                }
                                elseif (count($v1['tr']) ==($count-2))
                                {
                                    if ($k2 ==0)
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $v2['value'] );
                                        else
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+2).$i, $v2['value'] );
                                }
                                elseif (count($v1['tr'] ==($count-1)))
                                {
                                    if ($k2 ==1)
                                    {
                                        $objPHPExcel->getActiveSheet()->mergeCells( 'C'.$i.':'.'C'.($i+3) );
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+1).$i, $v2['value'] );
                                    }elseif ($k2 ==4 || $k2 ==3)
                                    {
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+1).$i, $v2['value'] );
                                    }elseif ($k2 ==0)
                                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $v2['value'] );
                                    else
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+1).$i, $v2['value'] );
                                }
                            }
                            elseif ($k1 >70 && $k1 <=76)
                            {
                                if (count($v1['tr']) ==($count-1))
                                {
                                    if ($k2 ==0)
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $v2['value'] );
                                        elseif ($k2 ==1)
                                        {
                                            $objPHPExcel->getActiveSheet()->mergeCells( 'B'.$i.':'.'B'.($i+5) );
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'B'.$i, $v2['value'] );
                                        }elseif ($k2 ==2)
                                        {
                                            $hebing = chr(ord('A')+$k2).$i.':'.chr(ord('A')+$k2+1).$i;
                                            $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2).$i, $v2['value'] );
                                        }elseif ($k2 >= 3)
                                        {
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+1).$i, $v2['value'] );
                                        }
                                }else
                                {
                                    if ($k2 ==0)
                                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$i, $v2['value'] );
                                        elseif ($k2 ==1)
                                        {
                                            $hebing = chr(ord('A')+$k2+1).$i.':'.chr(ord('A')+$k2+2).$i;
                                            $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+1).$i, $v2['value'] );
                                        }elseif ($k2 >= 2)
                                        {
                                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue( chr(ord('A')+$k2+2).$i, $v2['value'] );
                                        }
                                }
                            }
                        }
                        $i++;
                    }
                    unset($data);
                }
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'A')->setWidth(15);         //J列->宽
                $objPHPExcel->getActiveSheet()->freezePane('A2');       //冻结单元格
                //生成xls文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-xsrbmx-'.$date.'.xls';
                
                if ($bumen_id !='')
                {
                    $fileName = $vde['id'].'-xsrbmx-'.$date.'.xls';
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
		//		$revurl = uploadfile_ali_160112($keys);
         //       $xls = json_decode($revurl,true);
				$keys = "http://xsrb.wsy.me:801/files/".$vde['id'].'-xsrbmx-'.$date.'.xls';
				$cxo = M()->query("select * from xsrb_excel where `biao` ='xsrbmx' and dept_id =".$vde['id']." and `date` =".$date);
				if(!count($cxo))
				{
					$cxo =1;
	                if ($keys !='')
					{
						//当前部门的文件下载地址存入数据库
						$sql = "insert into xsrb_excel(`dept_id`,`biao`,`date`,`url`) values(".$vde['id'].",'xsrbmx',$date,'$keys')";
						M()->execute($sql);
					}						
				}
				$ret = -1;
            }
        }          
        unset($objPHPExcel);
		 if($ret ==1)
            return '{"resultcode":1,"resultmsg":"销售日报明细报表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"销售日报明细报表上传失败"}';
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
                    $sql = "select * from xsrb_excel where biao ='xsrbmx' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
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
    						'excel_url'=>C('Controller_url')."/XSRBMX/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
	
	public function settemplate()
	{
		$redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
		$redis->set('report-xsrblr-template', 
            '{"data":[{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"业务名称","product":"","type":"业务名称","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"项目类别","product":"","type":"业务名称","type_detail":"项目类别"},{"colspan":1,"rowspan":1,"dataType":"0","value":"项目名称","product":"","type":"业务名称","type_detail":"项目类别项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"防盗门","product":"防盗门","type":"业务名称","type_detail":"项目类别项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"三代机","product":"三代机","type":"业务名称","type_detail":"项目类别项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"地面波","product":"地面波","type":"业务名称","type_detail":"项目类别项目名称"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"当日销现","product":"","type":"现金业务","type_detail":"损益类现金收入当日销现"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入当日销现"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入当日销现"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入当日销现"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"报废收入","product":"","type":"现金业务","type_detail":"损益类现金收入报废收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入报废收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入报废收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入报废收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"运费收入","product":"","type":"现金业务","type_detail":"损益类现金收入运费收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入运费收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入运费收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入运费收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"其他收入","product":"","type":"现金业务","type_detail":"损益类现金收入其他收入","child":{"child_data":[{"project":"项目","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入其他收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入其他收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入其他收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"收回欠款","product":"","type":"现金业务","type_detail":"资产类现金收入收回欠款","child":{"child_data":[{"customname":"客户名称","class":"类别","newarrear":"新增欠款","recoverarrear":"收回欠款"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入收回欠款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"职工还借","product":"","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入职工还借"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"收押金","product":"","type":"现金业务","type_detail":"资产类现金收入收押金"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入收押金"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入收押金"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入收押金"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加预收款","product":"","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入增加预收款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加暂存款","product":"","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入增加暂存款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"经营部资金调入","product":"","type":"现金业务","type_detail":"资产类现金收入经营部资金调入","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"代收款","product":"","type":"现金业务","type_detail":"资产类现金收入代收款","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入代收款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入代收款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入代收款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"维修费","product":"","type":"现金业务","type_detail":"资产类现金收入维修费"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入维修费"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入维修费"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入维修费"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"费用类现金支出","product":"","type":"现金业务","type_detail":"费用类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"经营费用","product":"","type":"现金业务","type_detail":"费用类现金支出经营费用","child":{"child_data":[{"projectclass":"项目类别","projectname":"项目名称","door":"防盗门","genera":"三代机","ground_wave":"地面波"},{"projectclass":"经营费","projectname":"办公费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"财务费用","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"差旅费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"差旅费补助","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"调拨费用","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"返利","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"其他","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"其他运费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"市内交通费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"水电气费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"税金","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"维修费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"销售运费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"行政管理费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"业务招待费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"邮电费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"租赁费","door":0,"genera":0,"ground_wave":0},{"projectclass":"车辆费","projectname":"车杂费","door":0,"genera":0,"ground_wave":0},{"projectclass":"车辆费","projectname":"车修费","door":0,"genera":0,"ground_wave":0},{"projectclass":"车辆费","projectname":"车油费","door":0,"genera":0,"ground_wave":0}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"费用类现金支出经营费用"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"费用类现金支出","product":"","type":"现金业务","type_detail":"费用类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"车辆费用","product":"","type":"现金业务","type_detail":"费用类现金支出车辆费用","child":{"child_data":[{"projectclass":"项目类别","projectname":"项目名称","door":"防盗门","genera":"三代机"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"费用类现金支出车辆费用"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"资金调成总","product":"","type":"现金业务","type_detail":"资产类现金支出资金调成总","child":{"child_data":[{"otherdepartment":"对方部门","accountname":"户名","accountbank":"开户行","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出资金调成总"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"资金调经营部","product":"","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出资金调经营部"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"职工借款","product":"","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出职工借款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支押金","product":"","type":"现金业务","type_detail":"资产类现金支出支押金"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支押金"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支押金"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支押金"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支付职工浮动薪酬","product":"","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少预收款","product":"","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出减少预收款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少暂存款","product":"","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出减少暂存款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"代支采购货款","product":"","type":"现金业务","type_detail":"资产类现金支出代支采购货款","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出代支采购货款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"代支其他部门","product":"","type":"现金业务","type_detail":"资产类现金支出代支其他部门","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出代支其他部门"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加固定资产","product":"","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出增加固定资产"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加低易品与待摊费用","product":"","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支付工资","product":"","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支付工资"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支付预提","product":"","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支付预提"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支外购款","product":"","type":"现金业务","type_detail":"资产类现金支出支外购款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支外购款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支外购款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支外购款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"待处理","product":"","type":"现金业务","type_detail":"资产类现金支出待处理"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出待处理"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出待处理"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出待处理"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款","product":"","type":"应收款","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款","product":"","type":"应收款","type_detail":"应收款"},{"colspan":1,"rowspan":1,"dataType":"5","value":"新增","product":"","type":"应收款","type_detail":"应收款新增","child":{"child_data":[{"project":"项目","door":"防盗门","genera":"三代机"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"应收款","type_detail":"应收款新增"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"应收款","type_detail":"应收款新增"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"应收款","type_detail":"应收款新增"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"现金结存","product":"","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"现金结存","product":"","type":"现金业务","type_detail":"现金结存现金结存"},{"colspan":3,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"现金结存现金结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"应收款","product":"","type":"应收款","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"应收款结存","product":"","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"应收款","type_detail":"应收款结存"}]},{"tr":[{"colspan":3,"rowspan":1,"dataType":"6","value":"预收账款结存","product":"","type":"预收账款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"预收账款结存","type_detail":"预收账款结存"}]},{"tr":[{"colspan":3,"rowspan":1,"dataType":"6","value":"暂存款结存","product":"","type":"暂存款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"暂存款结存","type_detail":"暂存款结存"}]},{"tr":[{"colspan":1,"rowspan":8,"dataType":"6","value":"销售情况","product":"","type":"销售情况","type_detail":""},{"colspan":1,"rowspan":4,"dataType":"6","value":"当月","product":"","type":"销售情况","type_detail":"当月"},{"colspan":1,"rowspan":1,"dataType":"6","value":"销售收入累计","product":"","type":"销售情况","type_detail":"当月销售收入累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况","type_detail":"当月销售收入累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况","type_detail":"当月销售收入累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况","type_detail":"当月销售收入累计"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"销售成本累计","product":"","type":"销售成本累计","type_detail":"当月销售成本累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售成本累计","type_detail":"当月销售成本累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售成本累计","type_detail":"当月销售成本累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售成本累计","type_detail":"当月销售成本累计"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"毛利累计","product":"","type":"毛利累计","type_detail":"当月毛利累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"毛利累计","type_detail":"当月毛利累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"毛利累计","type_detail":"当月毛利累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"毛利累计","type_detail":"当月毛利累计"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"毛利率","product":"","type":"毛利率","type_detail":"当月毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"毛利率","type_detail":"当月毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"毛利率","type_detail":"当月毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"毛利率","type_detail":"当月毛利率"}]},{"tr":[{"colspan":1,"rowspan":4,"dataType":"6","value":"当日","product":"","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"当日销售收入","product":"","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"当日","type_detail":"当日销售收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"当日销售成本","product":"","type":"销售情况 ","type_detail":"当日销售成本"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况 ","type_detail":"当日销售成本"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况 ","type_detail":"当日销售成本"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况 ","type_detail":"当日销售成本"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"当日毛利","product":"","type":"销售情况 ","type_detail":"当日毛利"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况 ","type_detail":"当日毛利"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况 ","type_detail":"当日毛利"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况 ","type_detail":"当日毛利"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"当日毛利率","product":"","type":"销售情况 ","type_detail":"当日毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况 ","type_detail":"当日毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况 ","type_detail":"当日毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况 ","type_detail":"当日毛利率"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"外购入库","product":"","type":"商品业务","type_detail":"有效收入外购入库","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入外购入库"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入外购入库"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入外购入库"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"调拨收入","product":"","type":"商品业务","type_detail":"有效收入调拨收入","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入调拨收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"送货收回","product":"","type":"商品业务","type_detail":"有效收入送货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入送货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入送货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入送货收回"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少铺货商品","product":"","type":"商品业务","type_detail":"有效收入减少铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入减少铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入减少铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入减少铺货商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少待处理商品","product":"","type":"商品业务","type_detail":"有效收入减少待处理商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入减少待处理商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入减少待处理商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入减少待处理商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加暂存商品","product":"","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入增加暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价升值","product":"","type":"商品业务","type_detail":"有效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入调价升值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘盈","product":"","type":"商品业务","type_detail":"有效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入盘盈"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"销售成本","product":"","type":"商品业务","type_detail":"有效支出销售成本"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出销售成本"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出销售成本"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出销售成本"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"调拨支出","product":"","type":"商品业务","type_detail":"有效支出调拨支出","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出调拨支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"换货支出","product":"","type":"商品业务","type_detail":"有效支出换货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出换货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出换货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出换货支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"送货支出","product":"","type":"商品业务","type_detail":"有效支出送货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出送货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出送货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出送货支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加铺货商品","product":"","type":"商品业务","type_detail":"有效支出增加铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出增加铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出增加铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出增加铺货商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加待处理商品","product":"","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出增加待处理商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少暂存商品","product":"","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出减少暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"报废支出","product":"","type":"商品业务","type_detail":"有效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出报废支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价降值","product":"","type":"商品业务","type_detail":"有效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出调价降值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘亏","product":"","type":"商品业务","type_detail":"有效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出盘亏"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"调拨收入","product":"","type":"商品业务","type_detail":"无效收入调拨收入","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入调拨收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"换货收回","product":"","type":"商品业务","type_detail":"无效收入换货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入换货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入换货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入换货收回"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加暂存商品","product":"","type":"商品业务","type_detail":"无效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入增加暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价升值","product":"","type":"商品业务","type_detail":"无效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入调价升值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘盈","product":"","type":"商品业务","type_detail":"无效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入盘盈"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"调拨支出","product":"","type":"商品业务","type_detail":"无效支出调拨支出","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出调拨支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"报废支出","product":"","type":"商品业务","type_detail":"无效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出报废支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少暂存商品","product":"","type":"商品业务","type_detail":"无效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出减少暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价降值","product":"","type":"商品业务","type_detail":"无效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出调价降值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘亏","product":"","type":"商品业务","type_detail":"无效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出盘亏"}]},{"tr":[{"colspan":1,"rowspan":6,"dataType":"6","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":2,"rowspan":1,"dataType":"6","value":"有效结存","product":"","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"送货结存","product":"","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"送货结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"无效结存","product":"","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"无效结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"暂存商品结存","product":"","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"暂存商品结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"铺货结存","product":"","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"铺货结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"待处理商品结存","product":"","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"待处理商品结存"}]}]}');

	}
}