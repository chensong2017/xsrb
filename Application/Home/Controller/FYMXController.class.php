<?php
namespace Home\Controller;

use Think\Controller\RestController;
require_once 'XSRBUtil.php';
/**
 * 费用明细报表
 * @author Administrator
 *
 */
class FYMXController extends RestController{
    
    /**
     * 
     * @param string $token 验证token
     * @param string $date 查询数据日期
     * @param number $page 显示第几页数据
     * @return json
     */
    public function search($token='',$date=TODAY,$page=1,$pageSize=1,$flag=false,$dept=''){
       
        header("Access-Control-Allow-Origin: *");
 
       if($token!=''){
            //验证token
            $userinfo = checktoken($token);
            if(!$userinfo)
            {
                $this->response(retmsg(-2),'json');
                return;
            }
            //部门id
            $dept_id=$userinfo['dept_id'];
             //qt1判断是否是片区的标志
           $qt1=$userinfo['qt1'];
        }elseif($dept!=""){
            //部门id
             $dept_id=$dept['dept_id'];
             //qt1判断是否是片区的标志
              $qt1=$dept['qt1'];
            
        }else 
            return; 
        if($date==''){
            $date=date("Y-m-d");
        }
        
        //分权限查询部门数据
        $sql_total="select count(*) as total from xsrb_department where ";
        $sql_data="select id,dname from xsrb_department  where  ";
        //如果为总部查询所有部门
        if($dept_id==1){
            $sql_total.=" qt1!=0 ";
            $sql_data.=" qt1!=0  ";
        }
        //如果为片区则查出该片区下的所有部门
        elseif($qt1==0){
            $sql_total.=" pid='$dept_id' ";
            $sql_data.=" pid='$dept_id' ";
        }
        //具体某个部门的数据
        else{
            $sql_total.=" id='$dept_id' ";
            $sql_data.=" id='$dept_id'  ";
        }
        
       
        $resposeData['page']=$page;
        $total=M()->query($sql_total);
        $total=$total[0]['total'];
        if($total<$pageSize)
            $total=1;
        elseif($total%$pageSize==0)
            $total=$total/$pageSize;
        else
            $total=$total/$pageSize+1;
        $resposeData['total']=(int)$total;
        $resposeData['page']=$page;
//        //定义数据表头部分
//        $resposeData['title']=array(0=>array(
//            "tr"=>array(
//                0=>array("value"=>"费用类别"),
//                1=>array("value"=>"费用项目"),
//                2=>array("value"=>"防盗门合计"),
//                3=>array("value"=>"数码产品")
//            )
//        ));
        //数据部分
        $data=array();
        //模板json，查询数据不存在时，使用模板惊悚全部渲染0
        if (strtotime($date)>=strtotime('20170401')){
            $resposeData['title']=array(0=>array(
                "tr"=>array(
                    0=>array("value"=>"费用类别"),
                    1=>array("value"=>"费用项目"),
                    2=>array("value"=>"防盗门合计"),
                    3=>array("value"=>"其中:直发"),
                    4=>array("value"=>"其中:库房"),
                    5=>array("value"=>"数码产品")
                )
            ));
            $deptInfo['colspan']=6;
        }
        else{
            $resposeData['title']=array(0=>array(
                "tr"=>array(
                    0=>array("value"=>"费用类别"),
                    1=>array("value"=>"费用项目"),
                    2=>array("value"=>"防盗门"),
                    3=>array("value"=>"数码产品")
                )
            ));
            $deptInfo['colspan']=4;
        }
        $from=($page-1)*$pageSize;
         //查询出所有的部门
        $sql_data.=" limit $from,$pageSize";
        $result=M()->query($sql_data);
        //遍历每个部门循环查询每个部门对应的销售日报录入json
        foreach ($result as $row){
           
            $dept_id=$row['id'];
            $dname=$row['dname'];
            //第一行存放部门信息
            $deptInfo['value']=$dname;
            //部门数据行跨四列
//            $deptInfo['colspan']=4;
            array_push($data, $deptInfo);

            //获取销售日报录入json数据
            $query = M()->query("select json from xsrblr_json where dept = $dept_id and date='$date'");
            $redisData=$query[0]['json'];
            if(!$redisData) 
                $redisData=$this->tojson($dept_id,$date);
            $redisData=json_decode($redisData,true);
            $redisData=$redisData['data'];
            //遍历json,将需要的数据存入$data
             foreach($redisData as $keytr=>$tr){
                $tr=$tr['tr'];
                foreach($tr as $keytd=>$td){
//                 	p($td);
                    $value=trim($td["value"]);
                   if($value=="经营费用"){
//                    	echo $value;return;
                       //如果有明细
                       if(array_key_exists("child", $td)){
                            
                           $childData=$td['child']['child_data'];

                           //遍历每一行明细数据存入$data
                           foreach ($childData as $key=>$val){
                               //跳过第一行标题
                               if($key==0)
                                   continue;
                                   $temp=array();
                                   //部门名称
                                   $temp['tr'][]['value']=$val['projectclass'];
                                   //对方部门
                                   $temp['tr'][]['value']=$val['projectname'];
                                   //防盗门
                               if (strtotime($date)>=strtotime('20170401')){
                                   $temp['tr'][]['value']=$val['kf'] + $val['zf'];
                                   $temp['tr'][]['value']=$val['zf'];
                                   $temp['tr'][]['value']=$val['kf'];
                               }
                               else
                                   $temp['tr'][]['value']=$val['door'];

                                   //数码产品
                                   $temp['tr'][]['value']=$val['genera'];
                                   array_push($data,$temp);
                           }
                       }
                   }
             }   
        }
    }
    //数据部分
    $resposeData['data']=$data;
    
    if($flag)
        return $resposeData;
    echo json_encode($resposeData);
}

    //打印excel
    public function toExcel($token='',$date=TODAY,$dept='',$flag=false){
        set_time_limit(600);
        header("Access-Control-Allow-Origin: *");
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
    
        $objPHPExcel = new \PHPExcel();
        //待打印的数据
        $printData=$this->search($token,$date,1,300,true,$dept);
        //var_dump($printData);
        $objPHPExcel->setActiveSheetIndex(0);
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        //打印title部分
        $title=$printData['title'][0]['tr'];
        foreach ($title as $k_title=>$v_title){
            $offset=$index[$k_title];
            $objPHPExcel->getActiveSheet()->setCellValue($offset."1",$v_title['value']);
        }
    
        //打印数据分
        $data=$printData['data'];
        foreach ($data as $key=>$value){
            $row_offset=$key+2;
           if(array_key_exists("colspan",$value)){
               $objPHPExcel->getActiveSheet()->setCellValue("A$row_offset",$value['value']);
           }
           else{
               $val=$value['tr'];
               foreach ($val as $k=>$v){
                   $offset=$index[$k];
                   $objPHPExcel->getActiveSheet()->setCellValue("$offset$row_offset",$v['value']);
               }
               
           }
        }
        
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        //设置背景色
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:U1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        //不能加“#” RGB颜色码
        $objPHPExcel->getActiveSheet()->getStyle( 'A1:U1')->getFill()->getStartColor()->setRGB('99CCFF');
        $objPHPExcel->getActiveSheet()->getStyle( 'A2:B4685')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle( 'A2:B4685')->getFill()->getStartColor()->setRGB('CCFFFF');
        // 直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
       //定时调度上传
       if($flag){
           $dept_id=$dept['dept_id'];
           return \XSRBUtil::uploadExcel($dept_id, $objWriter,'FYMX',$date);
       }else 
           return $objWriter;
    }
    
    //打印excel
    public function printExcel($token='',$date=''){
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
        //qt1判断是否是片区的标志
            $qt1=$userinfo['qt1'];
        
        //如果是片区或者是总部则直接访问上传的excle
            if ($date == '') {
                $date = date("Ymd",strtotime("-1 day"));
            } else {
                if ($date >= date('Ymd'))
                    $date = date("Ymd",strtotime("-1 day"));
                    else
                        $date = date("Ymd", strtotime($date));
            }
        
           //如果是片区或者是总部则直接访问上传的excle
		if($qt1==0||$dept_id==1){
             
            $a = \XSRBUtil::download($dept_id,$date,'FYMX');
            if ($a ==-1)
            {
                $objWriter=$this->toExcel($token,$date);
                $filename = "FYMX-$date-$dept_id.xls";
                
                //输出到浏览器
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");;
                header("Content-Disposition:attachment;filename=$filename");
                header("Content-Transfer-Encoding:binary");
                $objWriter->save('php://output');
            }
        }
        //如果是普通部门则生成excle
        else {
            $objWriter=$this->toExcel($token,$date);
$filename = "FYMX-$date-$dept_id.xls";			
            //输出到浏览器
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header("Content-Disposition:attachment;filename=$filename");
            header("Content-Transfer-Encoding:binary");
            $objWriter->save('php://output');
        }
    }

 /**
     * 定时调度上传各部门的excel
     */
    public function uploadExcel($date=TODAY){
        set_time_limit(600);
        $dept=array();
        $biao='FYMX';
        $sql="select id,qt1 from xsrb_department where qt1 in (0,1) and id not in(
             select dept_id from xsrb_excel where date='$date' and biao='$biao') ";
        $depts=M()->query($sql);
		$ret=1;
        foreach ($depts as $row){
            $dept['dept_id']=$row['id'];
            $dept['qt1']=$row['qt1'];
            $ret=$this->toExcel("",$date,$dept,true);
        }
         if($ret)
            return '{"resultcode":1,"resultmsg":"费用明细报表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"费用明细报表上传失败"}';
    }
    
    
    //生成费用明细的josn数据
    public function tojson($dept ='',$date ='')
    {
        if (strtotime($date)>=strtotime('20170401'))     //因为201704修改了销售日报录入的行结构, 要重新使用新的json
            $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");
        else
            $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR_old1.txt");
        $handle=fopen($filename,'r');
        $rblr=fread($handle, filesize($filename));
        fclose($handle);
		
    	$rblr = json_decode($rblr,true);
    	$result = M()->query("select * from xsrblr where `dept` =$dept and `date` = '$date'");
    	$sql_fymx = "select * from fymx where `dept` =$dept and `date` = '$date'" ;		//获取明细数据
    	$re_fymx = M()->query($sql_fymx);
    	foreach ($re_fymx as $fy=>$mx)
    	{
    		$fymx[$mx[xmmc].'door'] = $mx['fdm'];
    		$fymx[$mx[xmmc].'zf'] = $mx['zf'];
    		$fymx[$mx[xmmc].'kf'] = $mx['kf'];

    		$fymx[$mx[xmmc].'genera'] = $mx['sdj'];
    		$fymx[$mx[xmmc].'ground_wavel'] = $mx['dmb'];
    	}
    	if (count($result))
    	{
//     		$arr = array();
//     		foreach($result as $key=>$val)
//     		{
//     			$arr[$val['type'].$val['type_detail'].'防盗门'] = $val['fdm'];
//     			$arr[$val['type'].$val['type_detail'].'数码产品'] = $val['sdj'];
//     			$arr[$val['type'].$val['type_detail'].'门配产品'] = $val['dmb'];
//     		}
    		foreach ($rblr as $k1=>$v1)
    		{
    			foreach ($v1 as $k2=>$v2)
    			{
    				foreach ($v2['tr'] as $k3=>$v3)
    				{
    					if ($v3['value'] =='经营费用')
    					{
    						foreach ($v3['child']['child_data'] as $k4=>$v4)
    						{
    							if ($fymx[$v4['projectname'].'door'] !='')
    							{
    								$rblr[$k1][$k2]['tr'][$k3]['child']['child_data'][$k4]['door'] = $fymx[$v4['projectname'].'door'];
    								$rblr[$k1][$k2]['tr'][$k3]['child']['child_data'][$k4]['zf'] = $fymx[$v4['projectname'].'zf'];
    								$rblr[$k1][$k2]['tr'][$k3]['child']['child_data'][$k4]['kf'] = $fymx[$v4['projectname'].'kf'];
    								$rblr[$k1][$k2]['tr'][$k3]['child']['child_data'][$k4]['genera'] = $fymx[$v4['projectname'].'genera'];
    								$rblr[$k1][$k2]['tr'][$k3]['child']['child_data'][$k4]['ground_wavel'] = $fymx[$v4['projectname'].'ground_wavel'];
    							}
    						}
    					}
//     					$rblr[$k1][$k2]['tr'][$k3]['value'] = $arr[$v3['type'].$v3['type_detail'].$v3['product']];
    				}
    			}
    		}
    	}
    	$rblr = json_encode($rblr);
    	return $rblr;
    }
    
}

?>