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

            //获取当前条件下的json
            $query = M()->query("select json from xsrblr_json where dept = ".$dept[0]['id']." and date='$date'");
			$xsrbmx = $query[0]['json'];
			if ($xsrbmx =='')
			{
			    $cgxsrb = new CGXSRBHZController();
                $xsrbmx = $cgxsrb->tomysql($date,$dept[0]['id']);  //生成日报模版的数据录入部分
			}
            $xsrbmx = $xsrblr->submit($xsrbmx,$token,'gx','gx',$date,$dept[0]['id'],'xx','a20170208');   //实时更新日报计算数据部分
            if (strtotime($date)>=strtotime('20170401'))    //因为201704修改了销售日报录入的行结构,对结构进行重组进行调整
            {
                $nu1 = 4;$nu2 = 6;$nu3 = 0;
            }else
            {
                $nu1 = 4;$nu2 = 6;$nu3 = 1;
            }

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
                if ($sb <= (30+$nu3)) {
                    $data['data'][] = $vsb;
                }
                if ($sb >= (47+$nu3) && $sb <= (76+$nu3)) {
                    $data['data'][] = $vsb;
                }
            }
            foreach ($temp['data'] as $sb1 => $vsb1) {      //数据调换位置
                if ($sb1 >= (31+$nu3) && $sb1 <= (46+$nu3)) {
                    $data['data'][] = $vsb1;
                }
                if ($sb1 >= (77+$nu3)) {
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
                $query = M()->query("select json from xsrblr_json where dept = ".$vv['id']." and date='$date'");
                $xsrbmx = $query[0]['json'];
                if ($xsrbmx =='')
                {
                    $cgxsrb = new CGXSRBHZController();
                    $xsrbmx = $cgxsrb->tomysql($date,$vv['id']);  //生成日报模版的数据录入部分
                }
                $xsrbmx = $xsrblr->submit($xsrbmx,$token,'gx','gx',$date,$vv['id'],'xx','a20170208');   //实时更新日报计算数据部分
//				if ($xsrbmx =='')
//				{
//					$filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");       //部门导出没有xsrblr的redis时,会出现录入数据为0的情况
//					$handle=fopen($filename,'r');
//					$xsrbmx=fread($handle, filesize($filename));
//					fclose($handle);
//				}
//				$xsrblr->submit($xsrbmx,$token,'gx','gx',$date,$vv['id']);   //获取没有录入天时,计算数据加载正确
//				$xsrbmx = $redis->get("report-".$vv['id']."-$date-XSRBLR");

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
                unset($arr);unset($xsrbmx);
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
        //echo 111111;return;
		if($date =='')
		{
			$date = date("Ymd",strtotime("-1 day"));		//根据昨天的数据生成Excel
		}
        $sql = "select id from xsrb_department where id =1 or qt1 =0 ";
        $dept_id = M()->query($sql);
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
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门名称');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','业务名称');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','项目类别');        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1','项目名称');
                if (strtotime($date)>=strtotime('20170401')){
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','防盗门合计');
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','其中:直发');
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','其中:库房');
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H1','数码产品');
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I1','门配产品');
                }else{
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1','防盗门');
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F1','数码产品');
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','门配产品');
                }
                $i =2;
                $json = array();
                $json = $this->search('',$date,'','','excel',$vde['id']);
                //new一个phpexcel
                //遍历整合过的jsondom,然后给每个单元格赋值,合并
                foreach ($json as $kj => $vj)
                {
                    if (1)      //生成data模版数据
                    {
                        if (strtotime($date)>=strtotime('20170401')) {    //因为201701修改了销售日报录入的行结构, 要重新使用新的json
                            $nu1 = 4;$nu2 = 6;$nu0=2;$nu4 = 0;
                            $filename = str_replace('\\', '/', realpath(__DIR__) . "/tempJson/XSRBLR.txt");
                        }
                        else{
                            $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR_old1.txt");
                            $nu1 = 4;$nu2 = 6;$nu0=2;$nu4 =1;
                        }

                        $handle=fopen($filename,'r');
                        $xsrbmx=fread($handle, filesize($filename));
                        fclose($handle);
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
                            if ($sb <= (29+$nu4)) {
                                $data['data'][] = $vsb;
                            }
                            if ($sb >= (42+$nu1+$nu4) && $sb <= (69+$nu2+$nu4)) {
                                $data['data'][] = $vsb;
                            }
                        }
                        foreach ($temp['data'] as $sb1 => $vsb1) {       //数据调换位置
                            if ($sb1 >= (30+$nu4) && $sb1 <= (41+$nu1+$nu4)) {
                                $data['data'][] = $vsb1;
                            }
                            if ($sb1 >= (70+$nu2+$nu4)) {
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
                            if ($k1 <=($nu0+57+$nu4))
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
                            elseif ($k1>($nu0+57+$nu4) && $k1 <=($nu0+61+$nu4))
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
                            elseif ($k1 >($nu0+61+$nu4) && $k1 <=($nu2+69+$nu4))
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
                                        $objPHPExcel->getActiveSheet()->mergeCells( 'C'.$i.':'.'C'.($i+$v2['rowspan']-1) );
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
                            elseif ($k1 >($nu2 +69+$nu4) && $k1 <=($nu2+75+$nu4))
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
                    $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);;
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
						$sql = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$vde['id'].",'xsrbmx',$date,'$keys')";
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
	
	public function settemplate($key)
	{
		$redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $val = file_get_contents("php://input");
		$redis->set($key,
            $val);

	}
}