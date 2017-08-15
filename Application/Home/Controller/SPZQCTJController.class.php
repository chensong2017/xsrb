<?php
/**
 * 商品帐期初统计表
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/11
 * Time: 9:57
 */
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
class SPZQCTJController extends RestController
{
    /**
     * 商品帐期初统计导出
     * @param string $date
     * @param string $bumen_id
     * @param int $type
     */
    public function uploadExcel($date ='',$bumen_id =''){
        ini_set('max_execution_time',2000);
        ini_set('memory_limit', "-1");
        header("Access-Control-Allow-Origin:*");
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
        //excel列标从A开始循环到IV
        $currentColumn = 'C';
        for ($i = 1; $i <= 254; $i++)
        {
            $a[] = $currentColumn++;
        }
        //起始sheet表
        $sheet = 0;
        foreach ($dept_id as $kde => $vde)
        {
            $cx = M()->query("select * from xsrb_excel where `biao` ='spzqctj' and dept_id ='".$vde['id']."' and `date` =".$date);
            if (!count($cx))
            {
                //new一个phpexcel
                $objPHPExcel = new \PHPExcel();
                $arr = $this->search($date,$vde['id']);
                foreach($arr as $k=>$v){
                    if (is_int($k/127))
                    {
                        //当超过127个部门的时候,设置下一个sheet页
                        $sheet = floor($k/127);
                        $objPHPExcel->createSheet($sheet);		//创建一个sheet
                        $objPHPExcel->setactivesheetindex($sheet);
                        $sql_left = "select col2 from spzmx_neikong where type=1 ";
                        $left_title = M()->query($sql_left);
                        foreach($left_title as $ktitle=>$vtitle){
                            $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( 'B'.($ktitle+5), $vtitle['col2'] );
                        }
                        $objPHPExcel->getActiveSheet($sheet)->mergeCells('A4:B4');
                        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( 'A4', '合计' );
                        $objPHPExcel->getActiveSheet($sheet)->mergeCells('A5:A14');
                        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( 'A5', '按部门统计' );
                        $objPHPExcel->getActiveSheet($sheet)->mergeCells('A15:A37');
                        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( 'A15', '按大类统计' );
                        $objPHPExcel->getActiveSheet($sheet)->mergeCells('A38:A57');
                        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( 'A38', '按表面要求统计' );
                        $objPHPExcel->getActiveSheet($sheet)->mergeCells('A58:A116');
                        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( 'A58', '按门框统计' );
                    }
                    foreach($v as $k1=>$v1){
                        if ($k1 ==0){
                            //部门信息设置
                            $hebing = $a[$k*2-254*$sheet].'1:'.$a[$k*2+1-254*$sheet].'1';
                            $objPHPExcel->getActiveSheet($sheet)->mergeCells($hebing);
                            $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( $a[$k*2-254*$sheet].'1', $v1[0] );
                        }else{
                            //结存数据列设置
                            $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( $a[$k*2-254*$sheet].($k1+1), $v1['0'] );
                            $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue( $a[$k*2+1-254*$sheet].($k1+1), $v1['1'] );
                        }
                    }
                }
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-spzqctjb-'.$date.'.xls';

                if ($bumen_id !='')
                {
                    $fileName = $vde['id'].'-spzqctjb-'.$date.'.xls';
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"$fileName\"");
                    header('Cache-Control: max-age=0');
                    $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
                    $objWriter->save('php://output'); //文件通过浏览器下载
                    return;
                }
                $objWriter1 = new \PHPExcel_Writer_Excel5($objPHPExcel);
                $objWriter1->save($keys);
                ob_clean();
                //执行上传的excel文件,返回文件的下载地址
                $keys = "http://xsrb.wsy.me:801/files/".$vde['id'].'-spzqctjb-'.$date.'.xls';
                $cxo = M()->query("select * from xsrb_excel where `biao` ='spzqctjb' and dept_id =".$vde['id']." and `date` =".$date);
                if(!count($cxo))
                {
                    $cxo =1;
                    if ($keys !='')
                    {
                        //当前部门的文件下载地址存入数据库
                        $sql = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$vde['id'].",'spzqctjb','".$date."','$keys')";
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

    //防盗门库存表期初查询表
    public function search($date ='',$id ='')
    {
        header("Access-Control-Allow-Origin: *");
        $dept_id = $id;
        //日期选择
        if ($date == '') {
            $date = date("Y-m");
        } else {
            $date = date("Y-m", strtotime($date));
        }
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname,pid from xsrb_department where pid != 1  and pid !=0 order by pid desc,qt1,id ";
        } else
        {
            // 判断部门(非片区and总部)的查询
            $sql = "select id,dname,pid from xsrb_department where qt2 like '%.".$dept_id."'order by pid desc,qt1,id";
            $pagination = M()->query($sql);
            $count = count($pagination); //部门数
            //部门查询时,只显示本部门
            if ($count == 0)
            {
                $sql = "select * from xsrb_department where id =" . $dept_id;
            }
        }
        $dept = M()->query($sql);
        foreach ($dept as $k1 => $v1)
        {
            $vid = $v1['id'];
            $sql_jc="SELECT col1,col2,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and zhizaobm=spzmx_neikong.col2 and `date`='$date' and `type`=1 and `dalei` not in ('锁体','锁芯','锁把','配件','门套')),0))as youxiao,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and zhizaobm=spzmx_neikong.col2 and `date`='$date' and `type`=0 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as wuxiao FROM spzmx_neikong where type=1 and col1='bumentj'
              UNION SELECT col1,col2,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and dalei=spzmx_neikong.col2 and `date`='$date' and `type`=1 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as youxiao,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and dalei=spzmx_neikong.col2 and `date`='$date' and `type`=0 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as wuxiao FROM spzmx_neikong where type=1 and col1='daleitj'
              UNION SELECT col1,col2,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and biaomianyq=spzmx_neikong.col2 and `date`='$date' and `type`=1 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as youxiao,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and biaomianyq=spzmx_neikong.col2 and `date`='$date' and `type`=0 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as wuxiao FROM spzmx_neikong where type=1 and col1='biaomianyqtj'
              UNION SELECT col1,col2,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and menkuang=spzmx_neikong.col2 and `date`='$date' and `type`=1 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as youxiao,(select ifnull((select sum(qichusl) from spzmxqc where dept=$vid and menkuang=spzmx_neikong.col2 and `date`='$date' and `type`=0 and `dalei` not in ('锁体','锁芯','锁把','配件','门套') ),0))as wuxiao FROM spzmx_neikong where type=1 and col1='menkuangtj' ";
            $spzqctj = M()->query($sql_jc);
            foreach($spzqctj as $tongji){
                if ($tongji['col1'] =='bumentj'){
                    $arr[$k1][0][0] = $v1['dname'];
                    $arr[$k1][1][0] = '有效商品';
                    $arr[$k1][1][1] = '无效商品';
                    $arr[$k1][3][0] +=$tongji['youxiao'];
                    $arr[$k1][3][1] +=$tongji['wuxiao'];
                    $arr[$k1][2][0] = '结存';
                    $arr[$k1][2][1] = '结存';
                }
                $arr[$k1][] =array($tongji['youxiao'],$tongji['wuxiao']);
            }
        }
        return $arr;
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

                //把有效无效
                $sql = "select * from xsrb_excel where biao ='spzqctjb' and `dept_id` =$dept_id and `date` ='.$date.' limit 1";
                $result = M()->query($sql);
                if (count($result))
                {
                    if($_SERVER['SERVER_NAME'] =='172.16.10.252')
                    {
                        $excel_url ="http://172.16.10.252/files/".$dept_id."-spzqctjb-".$date.".xls" ;
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
                        'excel_url'=>C('Controller_url')."/SPZQCTJ/uploadExcel/date/".$date."/bumen_id/".$dept_id
                    );
                }
                //将一维关联数组转换为json字符串
                $json = json_encode($arr);
                echo $json;
            }
        }
    }
}