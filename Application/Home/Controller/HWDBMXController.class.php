<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 * 货物调拨明细报表
 * @author Administrator
 *
 */
class HWDBMXController extends RestController{
    
    /**
     * 
     * @param string $token 验证token
     * @param string $date 查询数据日期
     * @param number $page 显示第几页数据
     * @param number $pageSize 每页显示条数（打印excel用）
     * @param number $type  显示收入或支出调拨数据，1代表有效收入，0代表无效支出
     * 全部数据从mysql里读取
     */
    public function search($token='',$date='',$page=1,$type=1, $pageSize=56){
        header("Access-Control-Allow-Origin: *");
       //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        //部门id
        $dept_id = $userinfo['dept_id'];
         //qt1判断是否是片区的标志
           $qt1=$userinfo['qt1'];
        if($date==''){
            $date=date("Y-m-d");
        } 
       
        //定义数据表头部分

        //数据部分
        $data=array();
       //查询记录条数
       if($type)
	   {
		   $resposeData['title']=array(0=>array(
            "tr"=>array(
                0=>array("value"=>"对方部门"),
                1=>array("value"=>"部门名称"),
                2=>array("value"=>"防盗门"),
                3=>array("value"=>"数码产品"),
                4=>array("value"=>"门配产品")
            )
        ));
			$type_str="收入";
	   }
       else
	   {		   
           $type_str="支出";
		   $resposeData['title']=array(0=>array(
            "tr"=>array(
                0=>array("value"=>"部门名称"),
                1=>array("value"=>"对方部门"),
                2=>array("value"=>"防盗门"),
                3=>array("value"=>"数码产品"),
                4=>array("value"=>"门配产品")
            )
        ));		   
       }
       //分权限查询部门数据
      $sql_total="select count(*) as total from hwdbmx where  xmlb in ('有效$type_str','无效$type_str')
       and date='$date'  ";
		if($type_str =='支出')
		{
		  $sql_data="select dname as d1, qtbm as d2,SUM(sdj) as sdj,SUM(fdm) as fdm,SUM(dmb) as dmb from hwdbmx t1,xsrb_department t2 where
          t1.dept=t2.id and xmlb in ('有效$type_str','无效$type_str')  and date='$date'";
		}else
		{
			$sql_data="select qtbm as d1, dname as d2,SUM(sdj) as sdj,SUM(fdm) as fdm,SUM(dmb) as dmb from hwdbmx t1,xsrb_department t2 where
			t1.dept=t2.id and xmlb in ('有效$type_str','无效$type_str')  and date='$date'";
		}
       //如果为片区则查出该片区下的所有部门
       if($qt1==0){
           $sql_total.=" and dept in(select id from xsrb_department where pid='$dept_id') ";
           $sql_data.=" and dept in(select id from xsrb_department where pid='$dept_id') ";
       }
       //具体某个部门的数据
       elseif( $qt1!=0&&$qt1!=1){
           $sql_total.="and dept='$dept_id' ";
           $sql_data.=" and dept='$dept_id'  ";
       }
       $sql_total.=" group by qtbm,dept";
       $sql_data.=" group by qtbm,dept";
        $result=M()->query($sql_total);
        $total=$result[0]['total'];
        $resposeData['page']=$page;
        $resposeData['total']=$total;
        if($total<$pageSize)
            $total=1;
        elseif($total%$pageSize==0)
            $total=$total/$pageSize;
        else
            $total=$total/$pageSize+1;
        $from=($page-1)*$pageSize;
        //查询数据部分
         $sql_data.=" limit $from,$pageSize";
        $result=M()->query($sql_data);
        foreach($result as $key=>$row){
            $data[$key]['tr'][]['value']=$row['d1'];
            $data[$key]['tr'][]['value']=$row['d2'];
            $data[$key]['tr'][]['value']=$row['fdm']/10000;
            $data[$key]['tr'][]['value']=$row['sdj']/10000;
            $data[$key]['tr'][]['value']=$row['dmb']/10000;
        }
        //数据部分
        $resposeData['data']=$data;
        $resposeData['total']=(int)$total;
        $resposeData['page']=$page;
        echo json_encode($resposeData);          
}

    //打印excel
    public function printExcel($token='',$date=''){
        set_time_limit(60);
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        //部门id
        $dept_id = $userinfo['dept_id'];
        //上级部门id
        $pid=$userinfo['pid'];
        if($date==''){
            $date=date("Y-m");
        }
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
        $objPHPExcel = new \PHPExcel();
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        //查询当日调出
        $sql_drdc="select dname as d1, qtbm as d2,SUM(sdj) as sdj,SUM(fdm) as fdm,SUM(dmb) as dmb from hwdbmx t1,xsrb_department t2 where
        t1.dept=t2.id and xmlb in ('有效支出','无效支出')  and date='$date' ";
   
        //查询当日验 收
        $sql_drys="select qtbm as d1, dname as d2,SUM(sdj) as sdj,SUM(fdm) as fdm ,SUM(dmb) as dmb from hwdbmx t1,xsrb_department t2 where
        t1.dept=t2.id and xmlb in ('有效收入','无效收入') and date='$date'  ";
        //如果为片区则查出该片区下的所有部门
        if($pid==1){
             $sql_drdc.=" and dept in(select id from xsrb_department where pid='$dept_id') ";
              $sql_drys.=" and dept in(select id from xsrb_department where pid='$dept_id') ";
        }
        //具体某个部门的数据
        elseif($pid!=0){
            $sql_drdc.="and dept='$dept_id' ";
            $sql_drys.=" and dept='$dept_id'  ";
        }
        $sql_drdc.=" group by qtbm,dept";
        $sql_drys.=" group by qtbm,dept";
        $drys=M()->query($sql_drys);
        $drdc=M()->query($sql_drdc);
        //创建一个sheet
        $objPHPExcel->createSheet();
       
        for($i=0;$i<2;$i++){
            $objPHPExcel->setActiveSheetIndex($i);
            if($i){
                $objPHPExcel->getActiveSheet()->setTitle('当日验收');
                $data=$drys;
				$kk = 1;
            }
            else {
                $objPHPExcel->getActiveSheet()->setTitle('当日调出');
                $data=$drdc;
				$kk =0;
            }
            //打印表头
			if($kk ==1)
			{
				$objPHPExcel->getActiveSheet()->setCellValue("A1","对方部门");
				$objPHPExcel->getActiveSheet()->setCellValue("B1","部门名称");
			}elseif($kk ==0)
			{
				$objPHPExcel->getActiveSheet()->setCellValue("A1","部门名称");
				$objPHPExcel->getActiveSheet()->setCellValue("B1","对方部门");
			}
            $objPHPExcel->getActiveSheet()->setCellValue("C1","防盗门");
            $objPHPExcel->getActiveSheet()->setCellValue("D1","数码产品");
			 $objPHPExcel->getActiveSheet()->setCellValue("E1","门配产品");
            //打印数据部分
           foreach ($data as $key=>$value){
               $offset=$key+2;
               $objPHPExcel->getActiveSheet()->setCellValue("A$offset",$value['d1']);
               $objPHPExcel->getActiveSheet()->setCellValue("B$offset",$value['d2']);
               $objPHPExcel->getActiveSheet()->setCellValue("C$offset",$value['fdm']/10000);
               $objPHPExcel->getActiveSheet()->setCellValue("D$offset",$value['sdj']/10000);
			   $objPHPExcel->getActiveSheet()->setCellValue("E$offset",$value['dmb']/10000);
           }
            
        }
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $filename = "HWDBMX-$date-$dept_id.xls";	
        // 直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
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

?>