<?php
namespace Home\Controller;
use Think\Controller\RestController;
include 'Application/Common/Classes/PHPExcel.php';
class ZJDBMXController extends RestController
{    
    public function search($sdate='',$edate ='',$pagesize ='',$page ='',$token ='')
    {
        header("Access-Control-Allow-Origin: *");
        //token检测
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        } 
        $dept_id = $userinfo['dept_id'];
        //日期选择
        if ($sdate == '') {
            $sdate = date("Ymd");
        } else
        {
            $sdate = date("Ymd", strtotime($sdate));
        }
        if ($edate == '')
        {
            $edate = date('Ymd');
        }else 
        {
            $edate = date("Ymd",strtotime($edate));
        }
        // 分页
        if ($page <= 0) {
            $page = 1;
        }
        if ($pagesize <= 0) {
            $pagesize = 50;
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
        $data = json_decode('{"page":1,"pagesize":20,"total":2,"data":[{"tr":[{"dataType":0,"value":"调出部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"对方部门","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"户名","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"开户行","rowspan":1,"colspan":1,"type":""},{"dataType":0,"value":"金额","rowspan":1,"colspan":1,"type":""}]}]}',true);
        $dept = M()->query($sql);
        foreach ($dept as $k1 => $v1)
        {
            $a =Array('tr' => Array( 0 => Array ('dataType' => 0,'value' => '0','rowspan' => 1,'colspan' => 1,'type' => '调出部门'),1 => Array('dataType' => 0,'value' => '0','rowspan' => 1,'colspan' => 1,'type' => '对方部门'),2 =>Array('dataType' =>0,'value' =>'0','rowspan' =>1,'colspan' =>1,'type' =>'户名'),3 =>Array('dataType' =>0,'value' =>'0','rowspan' =>1,'colspan' =>1,'type' =>'开户行'),4 => Array('dataType' => 0,'value' => '0','rowspan' => 1,'colspan' => 1,'type' => '金额')));
            $zjdbmx = M()->query("select * from zjdbmx where depart_id =".$v1['id']." and createtime between ".$sdate." and ".$edate." order by `id` desc");
            foreach ($zjdbmx as $k2 => $v2)
            {
                $a['tr'][0]['value'] = $v1['dname'];
                $a['tr'][1]['value'] = $v2['in_depart'];
                $a['tr'][2]['value'] = $v2['accountname'];
                $a['tr'][3]['value'] = $v2['accountbank'];
                $a['tr'][4]['value'] = $v2['amount'];
                $arr_click[] = $a;
            }            
        }
        $newarr = array_slice($arr_click, ($page-1)*$pagesize, $pagesize);
        for($i=0;$i<count($newarr);$i++)
        {
            $data['data'][] = $newarr[$i];
        }
        $data['page'] = $page;
        $data['pagesize'] = $pagesize;
        $data['total'] = ceil(count($arr_click)/$pagesize);
        echo json_encode($data);
    }
    
    //资金调拨明细 生成csv文件格式
    public function toexcel($sdate ='',$edate ='',$token ='')
    {
        header("Access-Control-Allow-Origin: *");
        //token检测
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        //日期选择
        if ($sdate == '')
        {
            $sdate = date("Ymd");
        } else {
            $sdate = date("Ymd", strtotime($sdate));
        }
        if ($edate == '')
        {
            $edate = date("Ymd");
        } else {
            $edate = date("Ymd", strtotime($edate));
        }
        
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname,pid from xsrb_department where pid != 1 and qt1 !=0 ";
        } else
        {
            // 片区部门
            $sql = "select id,dname,pid from xsrb_department where qt2 like '%." . $dept_id . "'";
        
            // 判断部门(非片区and总部)的查询
            $pagination = M()->query($sql);
            $count = count($pagination); //部门数
            //部门查询时,只显示本部门
            if ($count == 0)
            {
                $sql = "select * from xsrb_department where id =" . $dept_id;
            }
        }
		//第一行
        $str = '调出部门,对方部门,户名,开户行,金额'."\n";
        $dept = M()->query($sql);
        foreach($dept as $kd => $vd)
        {
            $zjdbmx = M()->query("select * from zjdbmx where depart_id =".$vd['id']." and createtime between ".$sdate." and ".$edate);
            if (count($zjdbmx))
            {
                foreach ($zjdbmx as $kz => $vz)
                {
					//循环数据行
                    $str .=$vd['dname'].','.$vz['in_depart'].','.$vz['accountname'].','.$vz['accountbank'].','.$vz['amount']."\n";
                }
            }
        }
        $str = iconv('utf-8','gbk',$str);
        $filename = $dept_id.'-'.'zjdbmx-'.$sdate.'-'.$edate.'.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
    }
}