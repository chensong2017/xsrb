<?php
namespace Home\Controller;

use Think\Controller\RestController;
require_once 'XSRBUtil.php';
/**
 * 期初查询报表
 */
class QCCXTJController extends RestController{
    
    /**
     * @param $token $page $date $flag
     * $pageSize默认每页显示四个部门的数据
     * 访问mysql获取每个部门的id,根据id查询redis 解析json
     * $flag=false 打印excel的标志
     */
    public function search($token='63b5a1d9af68c6b80e40adc5836e3ac8',$date=TODAY,$page=1,$pageSize=4,$flag=false,$dept=''){
        
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
      
        //响应数据
        $resposeData=array();
        //表头标题
        $resposeData['title']=array();
        $resposeData['data']['data']=array();
        //当前月
        $month= date("Y-m",strtotime($date));
		
        $resposeData['page']=$page;
        
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
        
        $total=M()->query($sql_total);
        $total=$total[0]['total'];
        if($total<$pageSize)
            $total=1;
        elseif($total%$pageSize==0)
            $total=$total/$pageSize;
        else
            $total=$total/$pageSize+1;
        $resposeData['total']=(int)$total;
		 //表头部分
        $json='[{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":""},{"dataType":0,"value":"业务名称","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":""},{"dataType":0,"value":"项目类别","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":""},{"dataType":0,"value":"项目名称","rowspan":1,"colspan":1,"product":"","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"防盗门合计","rowspan":1,"colspan":1,"product":"防盗门合计","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"其中:直发","rowspan":1,"colspan":1,"product":"其中:直发","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"其中:库房","rowspan":1,"colspan":1,"product":"其中:库房","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"数码产品","rowspan":1,"colspan":1,"product":"数码产品","type":"业务名称","type_detail":"项目名称"},{"dataType":0,"value":"门配产品","rowspan":1,"colspan":1,"product":"门配产品","type":"业务名称","type_detail":"项目名称"}]}]';
        $resposeData['title']=json_decode($json,true);
        //模板json(如果部门没有数据则获取模板)
       
        $tempJson='{"data":[{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"业务名称","product":"","type":"业务名称","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"项目类别","product":"","type":"业务名称","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"项目名称","product":"","type":"业务名称","type_detail":"项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"防盗门","product":"防盗门","type":"业务名称","type_detail":"项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"其中:直发","product":"其中:直发","type":"业务名称","type_detail":"项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"其中:库房","product":"其中:库房","type":"业务名称","type_detail":"项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"数码产品","product":"数码产品","type":"业务名称","type_detail":"项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"门配产品","product":"门配产品","type":"业务名称","type_detail":"项目名称"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"现金结存","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"现金结存","product":"","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"现金业务","type_detail":"现金结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款","product":"","type":"应收款","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款","product":"","type":"应收款","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款结存","product":"","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"应收款","type_detail":"应收款结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"预收账款结存","product":"","type":"预收账款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"预收账款结存","product":"","type":"预收账款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"预收账款结存","product":"","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"预收账款结存","type_detail":"预收账款结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"暂存款结存","product":"","type":"暂存款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"暂存款结存","product":"","type":"暂存款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"暂存款结存","product":"","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"暂存款结存","type_detail":"暂存款结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效结存","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效结存","product":"","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"商品业务","type_detail":"有效结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"送货结存","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"送货结存","product":"","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"商品业务","type_detail":"送货结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效结存","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效结存","product":"","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"商品业务","type_detail":"无效结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"暂存商品结存","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"暂存商品结存","product":"","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"商品业务","type_detail":"暂存商品结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"铺货结存","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"铺货结存","product":"","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"商品业务","type_detail":"铺货结存"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"待处理商品结存","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"待处理商品结存","product":"","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门合计","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:直发","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"其中:库房","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"数码产品","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"1","value":"0","product":"门配产品","type":"商品业务","type_detail":"待处理商品结存"}]}]}';
         $from=($page-1)*$pageSize;
        $sql_data.=" limit $from,$pageSize";
        $result=M()->query($sql_data);
        //循环每一个部门id查询josn解析
        foreach ($result as $row){
            $dept_id=$row['id'];
            $dname=$row['dname'];

            //获取对应的销售日报期初json
            $xsrbqc = new XSRBQCController();
            $json = $xsrbqc -> search('',$dept_id,$month);

            $data=json_decode($json,true);

			foreach ($data['data'] as $k1 => $v1)
			{
				foreach ($v1['tr'] as $k2 => $v2)
				{
					if (is_numeric($v2['value']))
					{
						$data['data'][$k1]['tr'][$k2]['value'] = $v2['value']/10000;
					}
				}
			}			
			
            $data=$data['data'];
            //截取数据部分（除去表头行）
            $data=array_slice($data,1);
            //为查询地json添加部门信息
            $tr=array("dataType"=>0,"value"=>$dname,"rowspan"=>1,"rowspan"=>10,
                "product"=>"","type"=>"","type_detail"=>"");
            //数据头插入部门信息
            array_unshift($data[0]['tr'],$tr);
            $temp['dept']=$data;
           //循环添加每一个部门的数据
           array_push($resposeData['data']['data'],$temp);
        }
      
        if($flag)
            return $resposeData;
        echo json_encode($resposeData);
    }
    
    //打印excel
    public function printExcel($token='',$date=TODAY){
       
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
             
            $a = \XSRBUtil::download($dept_id,$date,'QCCXTJ');
            if ($a ==-1)
            {
                $objWriter=$this->toExcel($token,$date);
                $filename = "QCCXTJ-$date-$dept_id.xls";
                
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
         			         $filename = "QCCXTJ-$date-$dept_id.xls";

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
    
    public function toExcel($token='',$date=TODAY,$dept='',$flag=false){
   
        set_time_limit(600);
        header("Access-Control-Allow-Origin: *");
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
        
        $objPHPExcel = new \PHPExcel();
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        //待打印的数据
        $printData=$this->search($token,$date,1,1000,true,$dept);
       
        //表头
        $title=$printData['title'][0]['tr'];
        $objPHPExcel->setActiveSheetIndex(0);
        //打印表头部分
        foreach ($title as $k_title=>$v_title){
            $offset=$index[$k_title];
            $objPHPExcel->getActiveSheet()->setCellValue($offset."1",$v_title['value']);
        }
        //打印数据部分
        $data=$printData['data']['data'];
       
        //遍历每一个部门
        foreach ($data as $k_data=>$v_data){
            $v_data=$v_data['dept'];
            //遍历一个部门的行
            foreach ($v_data as $k_tr=>$v_tr){
                $v_tr=$v_tr['tr'];
                //计算行标
                $row_offset=$k_data*10+$k_tr+2;
                //如果是第一行则需要部门跨行
                if($k_tr==0){
                    foreach ($v_tr as $k_temp=>$v_temp){
                        $offset=$index[$k_temp];
                        $objPHPExcel->getActiveSheet()->setCellValue("$offset$row_offset",$v_temp['value']);
                    }
                    //合并部门行
                    $des=$row_offset+9;
                    $objPHPExcel->getActiveSheet()->mergeCells("A$row_offset:A$des");
                }
                 
                //第二行开始从B列开始打印
                else{
                    foreach ($v_tr as $k=>$v){
                        $offset=$index[$k+1];
                        $objPHPExcel->getActiveSheet()->setCellValue("$offset$row_offset",$v['value']);
                    }
                }
            }
        }
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(19);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        /*  //设置水平垂直居中
         $objPHPExcel->getActiveSheet()->getStyle('A1:A2275')->applyFromArray(
         array(
         'alignment' => array(
         'horizontal' =>\PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
         'vertical'=>\PHPExcel_Style_Alignment::VERTICAL_CENTER
         )
         )
         ); */
        //设置水平垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2275')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1:A2275')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
       //定时调度上传
       if($flag){
           $dept_id=$dept['dept_id'];
           return \XSRBUtil::uploadExcel($dept_id, $objWriter,'QCCXTJ',$date);
       }else 
           return $objWriter;
      
    }
    
    /**
     * 定时调度上传各部门的excel
     */
    public function uploadExcel($date=TODAY){
        set_time_limit(600);
        $dept=array();
        $biao='QCCXTJ';
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
            return '{"resultcode":1,"resultmsg":"期初查询报表明细上传成功"}';
            else
			return  '{"resultcode":-1,"resultmsg":"期初查询报表明细上传失败"}';
    }
    

}
?>