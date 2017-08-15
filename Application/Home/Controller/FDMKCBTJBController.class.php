<?php
namespace Home\Controller;
use Think\Controller\RestController;

class FDMKCBTJBController extends RestController
{
    //防盗门库存表统计表查询
    public function search($sdate ='',$edate ='',$token ='',$page ='',$pagesize ='',$type='')
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

        $syue = date('Y-m',strtotime($sdate));
        $eyue = date('Y-m',strtotime($edate));
        if ($syue !== $eyue)
        {
            $retmsg = array(
                'resultcode' =>-1,
                'resultmsg' =>'统计查询按月查询'
            );
            $this->response($retmsg,'json');
            return ;
        }
        $yuechu = date('Ym01',strtotime($edate));
        $banben = checkbanben(str_replace('\\','/',realpath(__DIR__)."/tempJson"),date('Ym',strtotime($edate)));;
        // 分页
        if ($page <= 0)
        {
            $page = 1;
        }
        if ($pagesize <=0)
        {
            $pagesize = 1;
        }
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname,pid from xsrb_department where pid != 1  and qt1 !=0 order by pid desc,qt1,id ";
        } else
        {
            // 片区部门
            $sql = "select id,dname,pid from xsrb_department where qt2 like '%." . $dept_id . "' order by pid desc,qt1,id";

            // 判断部门(非片区and总部)的查询
            $pagination = M()->query($sql);
            $count = count($pagination); //部门数
            //部门查询时,只显示本部门
            if ($count == 0)
            {
                $sql = "select * from xsrb_department where id =" . $dept_id;
            }
        }

        $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json1.txt"),'r');
        $tj_json1=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json1.txt")));
        fclose($handle);
        $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json2.txt"),'r');
        $tj_json2=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json2.txt")));
        fclose($handle);
        $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json3.txt"),'r');
        $tj_json3=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json3.txt")));
        fclose($handle);

        //左标题
        $left_title = json_decode($tj_json1,true);
        //topTitle与content
        $count = ceil(count(M()->query($sql))/$pagesize);  //分页数
        $dept = M()->query($sql.' limit '.($page-1)*$pagesize.','.$pagesize);
        foreach ($dept as $k1 => $v1)
        {
            //查询部门属于的大区
            if ($v1['pid'] ==0)
            {
                $v1['pid'] = 1;
            }
            /*
                $ar由防盗门库存期初表生成全为0的数据,通过数据库查询
                $re = 	M()->query("select * from fdmkcbqc where date='2016-09' and dept=1");
                foreach ($re as $key=> $val)
                {
                    $arr[$val['product'].'有效收支有效结存']=0;
                    $arr[$val['product'].'无效收支无效结存']=0;
                }
            */
            $ar = unserialize($tj_json3);
            $daqu = M()->query('select dname from xsrb_department where `id` ='.$v1['pid']);
            $data = json_decode($tj_json2,true);

            $qc = M()->query("select * from fdmkcbqc where `dept` = '".$v1['id']."' and `date` = '".$syue."'");
            foreach ($qc as $kq => $vq)
            {
                $ar[$vq['product'].'有效收支有效结存'] = $vq['yxjc'];
                $ar[$vq['product'].'无效收支无效结存'] = $vq['wxjc'];
            }
            //查询时间段内的数据,并且计算和之后存入临时数组
            $sql="select product,yxjc,wxjc from fdmkcb where `dept` ='".$v1['id']."' and `date` between '$yuechu' and '$edate'";
            //echo $sql;
            $jc = M()->query("select product,yxjc,wxjc from fdmkcb where `dept` ='".$v1['id']."' and `date` between '$yuechu' and '$edate'");
            foreach($jc as $kk =>$vv)
            {
                $ar[$vv['product'].'有效收支有效结存'] += $vv['yxjc'];
                $ar[$vv['product'].'无效收支无效结存'] += $vv['wxjc'];
            }
            $fdmkcbtjb = M()->query("select * from fdmkcb where `dept` ='".$v1['id']."' and `date` between '$sdate' and '$edate'");
            if (count($fdmkcbtjb))
            {
                foreach ($fdmkcbtjb as $k2=>$v2)
                {
                    $arr[$v2['product'].'调拨收入'] += $v2['dbsr'];
                    $arr[$v2['product'].'其他收入'] += $v2['qtsr'];
                    $arr[$v2['product'].'销售量成都生产'] += $v2['cdsc'];
                    $arr[$v2['product'].'销售量齐河生产'] += $v2['qhsc'];
                    $arr[$v2['product'].'销售量外购门'] += $v2['wgm'];
                    $arr[$v2['product'].'有效收支报废支出'] += $v2['bfzc'];
                    $arr[$v2['product'].'有效收支调拨支出'] += $v2['dbzc'];
                    $arr[$v2['product'].'有效收支其他支出'] += $v2['qtzc'];

                    //$ar[$v2['product'].'有效收支有效结存'] += $v2['yxjc'];

                    $arr[$v2['product'].'无效收支有效转入'] += $v2['yxzr'];
                    $arr[$v2['product'].'无效收支报废支出'] += $v2['wxbfzc'];
                    $arr[$v2['product'].'无效收支其他支出'] += $v2['wxqtzc'];

                    //$ar[$v2['product'].'无效收支无效结存'] += $v2['wxjc'];

                    $arr[$v2['product'].'有效收支有效结存'] = $ar[$v2['product'].'有效收支有效结存'];
                    $arr[$v2['product'].'无效收支无效结存'] = $ar[$v2['product'].'无效收支无效结存'];
                }
            }
            if (empty($arr))
            {
                $arr = $ar;
            }
            //数组里面的值赋值给data模版里面
            foreach ($data['content'] as $k3 => $v3)
            {
                foreach ($v3['tr'] as $k4 => $v4)
                {
                    if ($arr[$v4['product'].$v4['type'].$v4['type_detail']] != '')
                    {
                        $data['content'][$k3]['tr'][$k4]['value'] = $arr[$v4['product'].$v4['type'].$v4['type_detail']];
                    }
                }

            }
            $data['topTitle'][0]['tr'][0]['value'] = $daqu[0]['dname'];
            $data['topTitle'][1]['tr'][0]['value'] = $v1['dname'];
            $left_title['data'][] = $data;
            unset($data); unset($ar); unset($arr);
        }
        $left_title['page'] = $page;
        $left_title['total'] = $count;
        $left_title['pagesize'] = $pagesize;
        echo json_encode($left_title);
    }

    public function uploadExcel($date)
    {
        $this->tozip('',$date,$date,'dsrw');
    }

    public function toexcel($token ='',$sdate ='',$edate ='',$type=null)
    {
        header("Access-Control-Allow-Origin: *");
        ini_set('max_execution_time', -1);
        $dept_id = 1;
        if ($type == null)
        {
            $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept_id = $userinfo['dept_id'];
        }
        $yuechu = date('Ym01',strtotime($edate));
        $banben = date("Ym",strtotime($edate));

        $sss = M()->query("select * from xsrb_excel where dept_id =$dept_id and date='$sdate' and edate ='$edate' and biao ='fdmkcbtjb'");
        if (count($sss))
        {
            $excel_url =$sss[0]['url'];         //浏览器导出zip文件
			if ($_SERVER['SERVER_NAME'] =='172.16.10.252')
			{
				$excel_url = 'http://172.16.10.252'.substr($excel_url,22);
            }
            $arr = array(
                'excel_url'=>$excel_url
            );
        } else
        {
            $arr = array(
                'excel_url'=>C('Controller_url')."/FDMKCBTJB/tozip/sdate/".$sdate."/edate/".$edate."/token/".$token
            );
        }
        echo json_encode($arr);
    }
    //防盗门库存表统计表  生成csv文件格式
    public function tozip($token ='',$sdate ='',$edate ='',$type=null)
    {
        header("Access-Control-Allow-Origin: *");
        ini_set('max_execution_time', -1);
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $dept_id = 1;
        if ($type == null)
        {
            $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept_id = $userinfo['dept_id'];
        }
        $yuechu = date('Ym01',strtotime($edate));
        $banben = checkbanben(str_replace('\\','/',realpath(__DIR__)."/tempJson"),date('Ym',strtotime($edate)));;

        $sss = M()->query("select * from xsrb_excel where dept_id =$dept_id and date='$sdate' and edate ='$edate' and biao='fdmkcbtjb'");
        if (count($sss))
        {
			if($type !='dsrw')
			{
			    $excel_url =$sss[0]['url'];         //浏览器导出zip文件
				echo "<SCRIPT LANGUAGE=\"JavaScript\">location.href='$excel_url'</SCRIPT>";
				return;	
			}
			return;
        }

        $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json3.txt"),'r');
        $tj_json3=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json3.txt")));
        fclose($handle);
        $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json4.txt"),'r');
        $tj_json4=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json4.txt")));
        fclose($handle);

        //日期选择
        if ($sdate == '')
        {
            $sdate = date("Ymd",strtotime("-1 day"));
        } else {
            $sdate = date("Ymd", strtotime($sdate));
        }
        if ($edate == '')
        {
            $edate = date("Ymd");
        } else {
            $edate = date("Ymd", strtotime($edate));
        }
        $syue = date('Y-m',strtotime($sdate));
        $eyue = date('Y-m',strtotime($edate));
        if ($syue !== $eyue)
        {
            $retmsg = array(
                'resultcode' =>-1,
                'resultmsg' =>'统计查询按月查询'
            );
            $this->response($retmsg,'json');
        }
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname,pid from xsrb_department where pid != 1 and pid !=0 order by pid desc,qt1,id ";
        } else
        {
            // 片区部门
            $sql = "select id,dname,pid from xsrb_department where qt2 like '%." . $dept_id . "' order by pid desc,qt1,id ";

            // 判断部门(非片区and总部)的查询
            $pagination = M()->query($sql);
            $count = count($pagination); //部门数
            //部门查询时,只显示本部门
            if ($count == 0)
            {
                $sql = "select * from xsrb_department where id =" . $dept_id;
            }
        }
        $result = M()->query($sql);
        $cntpage = ceil(count($result)/19);
        for ($i=1;$i<=$cntpage;$i++)
        {
            $page = 19*($i-1);
            $str = '';
            if ($dept_id == 1)
            {
                // dept_id=1表示为总部
                $sql = "select id,dname,pid from xsrb_department where pid != 1 and pid !=0 order by pid desc,qt1,id  limit $page ,19";
            } else
            {
                // 片区部门
                $sql = "select id,dname,pid from xsrb_department where qt2 like '%." . $dept_id . "' order by pid desc,qt1,id limit $page ,19";

                // 判断部门(非片区and总部)的查询
                $pagination = M()->query($sql);
                $count = count($pagination); //部门数
                //部门查询时,只显示本部门
                if ($count == 0)
                {
                    $sql = "select * from xsrb_department where id =" . $dept_id;
                }
            }
            //产品数组列表
            //$ar1数组的修改是在数组内修改对应的产品所在的位置

            $handle=fopen(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json5.txt"),'r');
            $tj_json5=fread($handle, filesize(str_replace('\\','/',realpath(__DIR__)."/tempJson/$banben/tj_json5.txt")));
            fclose($handle);
            $ar1 = unserialize($tj_json5);
//		if($type !='old')
//		{
//			$ar1 =["40门标门转印门","40门标门金属粉","40门标门喷塑粉","40门副窗门转印门","40门副窗门金属粉","40门副窗门喷塑粉","60门标门转印门低配","60门标门转印门高配","60门标门仿铜门","60门标门金属漆/粉","60门标门喷塑粉","60门单门副窗门转印门低配","60门单门副窗门转印门高配","60门单门副窗门仿铜门","60门单门副窗门金属漆/粉","60门单门副窗门喷塑粉","60门对开子母门转印门","60门对开子母门仿铜门","60门对开子母门金属漆/粉","60门对开子母门喷塑粉","70常规门标门转印门低配","70常规门标门转印门高配","70常规门标门仿铜门低配(花边框100祥云)","70常规门标门仿铜门低配","70常规门标门仿铜门高配(花边框100祥云)","70常规门标门仿铜门高配","70常规门标门金属漆/粉","70常规门标门喷塑粉","70常规门单门副窗门转印门低配","70常规门单门副窗门转印门高配","70常规门单门副窗门仿铜门低配(花边框100祥云)","70常规门单门副窗门仿铜门低配","70常规门单门副窗门仿铜门高配(花边框100祥云)","70常规门单门副窗门仿铜门高配","70常规门单门副窗门金属漆/粉","70常规门单门副窗门喷塑粉","70常规门对开子母门转印门","70常规门对开子母门仿铜门","70常规门对开子母门金属漆/粉","70常规门对开子母门喷塑粉","70常规门钢套门转印门(单扇)","70常规门钢套门转印门(双扇)","70常规门钢套门仿铜门(120花边框)(单扇)","70常规门钢套门仿铜门(120花边框)(双扇)","70常规门钢套门仿铜门(单扇)","70常规门钢套门仿铜门(双扇)","70等级门非标等级标门转印门","70等级门非标等级标门仿铜门","70等级门等级标门转印门","70等级门等级标门仿铜门","70等级门非标门转印门","70等级门非标门仿铜门","80门标门转印门","80门标门仿铜门","80门标门仿古红木","80门标门仿真铜门","80门标门金属漆","80门单门副窗门转印门","80门单门副窗门仿铜门","80门单门副窗门仿古红木","80门单门副窗门仿真铜门","80门单门副窗门金属漆","90常规门标门转印门","90常规门标门仿铜门","90常规门标门金属漆/粉","90常规门单门副窗门转印门","90常规门单门副窗门仿铜门","90常规门单门副窗门金属漆/粉","90常规门对开子母门仿铜门","90常规门对开子母门金属漆/粉","90等级门非标准丁级所有","90等级门丁级门所有","90等级门非标准甲级所有","90等级门甲级门所有","100等级门甲级门所有","100等级门非标准甲级所有","90钢套门对开子母门仿铜门","50防火门进户门转印门","50防火门进户子母门转印门","50防火门进户门喷塑粉","50防火门进户子母门喷塑粉","50防火门通道门所有","50防火门管井门所有","70防火门进户门转印门","70防火门进户子母门转印门","70防火门进户门喷塑粉","70防火门进户子母门喷塑粉","70防火门通道门所有","70防火门管井门所有","50门标门及副窗转印门(框厚1.0及以下配置)","50门标门及副窗仿铜门(框厚1.0及以下配置)","50门标门及副窗金属粉(框厚1.0及以下配置)","50门标门及副窗喷塑粉(框厚1.0及以下配置)","50门标门及副窗转印门","50门标门及副窗仿铜门","50门标门及副窗金属粉","50门标门及副窗喷塑粉","50门对开子母门转印门","50门对开子母门仿铜门","50门对开子母门金属粉","50门对开子母门喷塑粉","室内门强化门所有","室内门强化门-单门套所有","室内门强化门-单门扇所有","室内门钢木门烤漆","室内门楼宇门所有","室内门单层板门所有","木质门门扇所有","木质门门套所有","外购门系列卫浴门所有","外购门系列活动板房门所有","外购门系列室内门所有","外购门系列室内门门套所有","外购门系列等级门所有","其他门不能确定的分类产品所有","合扬直发经营部锁体智能锁","合扬直发经营部锁体防火锁","合扬直发经营部锁体其他","合扬直发经营部锁芯锁芯","合扬直发经营部拉手拉手","物流发经营部锁体智能锁","物流发经营部锁体防火锁","物流发经营部锁体其他","物流发经营部锁芯锁芯","物流发经营部拉手拉手","外购配件系列锁体锁体","外购配件系列锁芯锁芯","外购配件系列拉手拉手","配件类配件类配件类"];
//		}else
//		{
//			$ar1 =["40门标门转印门","40门标门金属粉","40门标门喷塑粉","40门副窗门转印门","40门副窗门金属粉","40门副窗门喷塑粉","60门标门转印门低配","60门标门转印门高配","60门标门仿铜门","60门标门金属漆/粉","60门标门喷塑粉","60门单门副窗门转印门低配","60门单门副窗门转印门高配","60门单门副窗门仿铜门","60门单门副窗门金属漆/粉","60门单门副窗门喷塑粉","60门对开子母门转印门","60门对开子母门仿铜门","60门对开子母门金属漆/粉","60门对开子母门喷塑粉","70常规门标门转印门低配","70常规门标门转印门高配","70常规门标门仿铜门低配(花边框100祥云)","70常规门标门仿铜门低配","70常规门标门仿铜门高配(花边框100祥云)","70常规门标门仿铜门高配","70常规门标门金属漆/粉","70常规门标门喷塑粉","70常规门单门副窗门转印门低配","70常规门单门副窗门转印门高配","70常规门单门副窗门仿铜门低配(花边框100祥云)","70常规门单门副窗门仿铜门低配","70常规门单门副窗门仿铜门高配(花边框100祥云)","70常规门单门副窗门仿铜门高配","70常规门单门副窗门金属漆/粉","70常规门单门副窗门喷塑粉","70常规门对开子母门转印门","70常规门对开子母门仿铜门","70常规门对开子母门金属漆/粉","70常规门对开子母门喷塑粉","70常规门钢套门转印门(单扇)","70常规门钢套门转印门(双扇)","70常规门钢套门仿铜门(120花边框)(单扇)","70常规门钢套门仿铜门(120花边框)(双扇)","70常规门钢套门仿铜门(单扇)","70常规门钢套门仿铜门(双扇)","70等级门非标等级标门转印门","70等级门非标等级标门仿铜门","70等级门等级标门转印门","70等级门等级标门仿铜门","70等级门非标门转印门","70等级门非标门仿铜门","80门标门转印门","80门标门仿铜门","80门标门仿古红木","80门标门仿真铜门","80门标门金属漆","80门单门副窗门转印门","80门单门副窗门仿铜门","80门单门副窗门仿古红木","80门单门副窗门仿真铜门","80门单门副窗门金属漆","90常规门标门转印门","90常规门标门仿铜门","90常规门标门金属漆/粉","90常规门单门副窗门转印门","90常规门单门副窗门仿铜门","90常规门单门副窗门金属漆/粉","90常规门对开子母门仿铜门","90常规门对开子母门金属漆/粉","90等级门非标准丁级所有","90等级门丁级门所有","90等级门非标准甲级所有","90等级门甲级门所有","100等级门甲级门所有","100等级门非标准甲级所有","90钢套门对开子母门仿铜门","50防火门进户门转印门","50防火门进户子母门转印门","50防火门进户门喷塑粉","50防火门进户子母门喷塑粉","50防火门通道门所有","50防火门管井门所有","70防火门进户门转印门","70防火门进户子母门转印门","70防火门进户门喷塑粉","70防火门进户子母门喷塑粉","70防火门通道门所有","70防火门管井门所有","50门标门及副窗转印门(框厚1.0及以下配置)","50门标门及副窗仿铜门(框厚1.0及以下配置)","50门标门及副窗金属粉(框厚1.0及以下配置)","50门标门及副窗喷塑粉(框厚1.0及以下配置)","50门标门及副窗转印门","50门标门及副窗仿铜门","50门标门及副窗金属粉","50门标门及副窗喷塑粉","50门对开子母门转印门","50门对开子母门仿铜门","50门对开子母门金属粉","50门对开子母门喷塑粉","室内门强化门所有","室内门强化门-单门套所有","室内门强化门-单门扇所有","室内门钢木门烤漆","室内门楼宇门所有","室内门单层板门所有","木质门门扇所有","木质门门套所有","外购门系列卫浴门所有","外购门系列活动板房门所有","外购门系列室内门所有","外购门系列室内门门套所有","外购门系列等级门所有","其他门不能确定的分类产品所有","锁把锁把锁把","配件类配件类配件类"];
//		}
            $str ='大区,';
            $bumen = '部门,';
            $shuxing='属性,';
            $leibie ='类别,';
            $dept = M()->query($sql);
            foreach ($dept as $kd => $vd)
            {
                //获取大区信息
                if ($vd['pid'] ==0)
                {
                    $vd['pid'] = 1;
                }
                /*
                    $ar数组在查询上面可以获取到
                */
                $ar = unserialize($tj_json3);
                $daqu = M()->query('select dname from xsrb_department where `id` ='.$vd['pid']);

                //通过redis的数据来生成报表
                $fdm_json = $redis->get("report-".$vd['id']."-$sdate-FDMKC");
                if ($sdate == $edate  && $fdm_json !='')
                {
                    $fdm_arr = json_decode($fdm_json,true);
                    foreach($fdm_arr['data'] as $k1 => $v1)
                    {
                        if ($k1>=4)
                        {
                            foreach($v1['tr'] as $v2)
                            {
                                if ($v2['type_detail'] !='' && $v2['product'] !='')
                                    $arr[$v2['product'].$v2['type'].$v2['type_detail']] = $v2['value'];
                            }
                        }
                    }

                }else
                {
                    $qc = M()->query("select * from fdmkcbqc where `dept` = '".$vd['id']."' and `date` = '".$syue."'");
                    foreach ($qc as $kq => $vq)     //期初数据获取
                    {
                        $ar[$vq['product'].'有效收支有效结存'] = $vq['yxjc'];
                        $ar[$vq['product'].'无效收支无效结存'] = $vq['wxjc'];
                    }
                    $jc = M()->query("select product,yxjc,wxjc from fdmkcb where `dept` ='".$vd['id']."' and `date` between '".$yuechu."' and '".$edate."'");
                    foreach($jc as $kk =>$vv)
                    {
                        $ar[$vv['product'].'有效收支有效结存'] += $vv['yxjc'];
                        $ar[$vv['product'].'无效收支无效结存'] += $vv['wxjc'];
                    }
                    $fdmkcbtjb = M()->query("select * from fdmkcb where `dept` ='".$vd['id']."' and `date` between '".$sdate."' and '".$edate."'");
                    if (count($fdmkcbtjb))
                    {
                        foreach ($fdmkcbtjb as $k2=>$v2)
                        {
                            $arr[$v2['product'].'调拨收入'] += $v2['dbsr'];
                            $arr[$v2['product'].'其他收入'] += $v2['qtsr'];
                            $arr[$v2['product'].'销售量成都生产'] += $v2['cdsc'];
                            $arr[$v2['product'].'销售量齐河生产'] += $v2['qhsc'];
                            $arr[$v2['product'].'销售量外购门'] += $v2['wgm'];
                            $arr[$v2['product'].'有效收支报废支出'] += $v2['bfzc'];
                            $arr[$v2['product'].'有效收支调拨支出'] += $v2['dbzc'];
                            $arr[$v2['product'].'有效收支其他支出'] += $v2['qtzc'];
                            //$ar[$v2['product'].'有效收支有效结存'] += $v2['yxjc'];
                            $arr[$v2['product'].'无效收支有效转入'] += $v2['yxzr'];
                            $arr[$v2['product'].'无效收支报废支出'] += $v2['wxbfzc'];
                            $arr[$v2['product'].'无效收支其他支出'] += $v2['wxqtzc'];
                            //$ar[$v2['product'].'无效收支无效结存'] += $v2['wxjc'];

                            $arr[$v2['product'].'有效收支有效结存'] = $ar[$v2['product'].'有效收支有效结存'];
                            $arr[$v2['product'].'无效收支无效结存'] = $ar[$v2['product'].'无效收支无效结存'];
                        }
                    }else
                    {
                        //$fdmkcbtjb获取的是通过select * from fdmkcb where date='xxxxx' and dept=1,然后生成序列化数据
                        $fdmkcbtjb = unserialize($tj_json4);
                        foreach($fdmkcbtjb as $kkk => $vvv)
                        {
                            $arr[$vvv['product'].'调拨收入'] = 0;
                            $arr[$vvv['product'].'其他收入'] = 0;
                            $arr[$vvv['product'].'销售量成都生产'] = 0;
                            $arr[$vvv['product'].'销售量齐河生产'] = 0;
                            $arr[$vvv['product'].'销售量外购门'] = 0;
                            $arr[$vvv['product'].'有效收支报废支出'] = 0;
                            $arr[$vvv['product'].'有效收支调拨支出'] = 0;
                            $arr[$vvv['product'].'有效收支其他支出'] = 0;
                            //$ar[$v2['product'].'有效收支有效结存'] += $v2['yxjc'];
                            $arr[$vvv['product'].'无效收支有效转入'] = 0;
                            $arr[$vvv['product'].'无效收支报废支出'] = 0;
                            $arr[$vvv['product'].'无效收支其他支出'] = 0;
                            //$ar[$v2['product'].'无效收支无效结存'] += $v2['wxjc'];

                            $arr[$vvv['product'].'有效收支有效结存'] = $ar[$vvv['product'].'有效收支有效结存'];
                            $arr[$vvv['product'].'无效收支无效结存'] = $ar[$vvv['product'].'无效收支无效结存'];
                        }
                    }
                }
//                p($arr['40门标门转印门调拨收入']);
                $new [$vd['dname']] = $arr;
//                p($new);
                unset($arr);   unset($ar);   unset($ar0); unset($fdm_json);   //清除变量
                //csv第一行,大区名
                $str .= $daqu[0]['dname'].",,,,,,,,,,,,,";
                //第二行,部门名称
                $bumen .=$vd['dname'].",,,,,,,,,,,,,";
                //分类
                $shuxing .= "调拨收入,其他收入,销售量,销售量,销售量,有效收支,有效收支,有效收支,有效收支,无效收支,无效收支,无效收支,无效收支,";
                //属性
                $leibie .="调拨收入,其他收入,成都生产,齐河生产,外购门,报废支出,调拨支出,其他支出,有效结存,有效转入,报废支出,其他支出,无效结存,";

            }
            //循环数据行
            $ii = 0;
            foreach ($ar1 as $ka => $va)
            {
                $shuju .= $ar1[$ii].",";
                foreach ($new as $kn => $vn)
                {
                    $shuju .=$vn[$va.'调拨收入'] .','.$vn[$va.'其他收入'] .','.$vn[$va.'销售量成都生产'] .','.$vn[$va.'销售量齐河生产'] .','.$vn[$va.'销售量外购门'] .','.$vn[$va.'有效收支报废支出'] .','.$vn[$va.'有效收支调拨支出'] .','.$vn[$va.'有效收支其他支出'] .','.$vn[$va.'有效收支有效结存'] .','.$vn[$va.'无效收支有效转入'] .','.$vn[$va.'无效收支报废支出'] .','.$vn[$va.'无效收支其他支出'] .','.$vn[$va.'无效收支无效结存'].',';
                }
                $ii++;
                $shuju .="\n";
            }
            $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/excel/';
            $str = $str."\n".$bumen."\n".$shuxing."\n".$leibie."\n".$shuju;
            $str = iconv('utf-8','gbk',$str);

            $filepath = $keys.$i.'-'.$dept_id.'-'.'fdmkcbtjb-'.$sdate.'-'.$edate.'.csv';
            $fp = fopen($filepath,"a");     //生成csv文件
            fwrite($fp,$str);
            fclose($fp);
            $fileNameArr[] = $filepath;
            $bumen =''; $shuxing=''; $leibie=''; $str=''; $shuju=''; unset($new);   //变量赋值null

        }
        $filename = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$dept_id.'-'.'fdmkcbtjb-'.$sdate.'-'.$edate. ".zip"; // 最终生成的文件名（含路径）
        // 生成文件
        $zip = new \ZipArchive (); // 使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open ( $filename, \ZIPARCHIVE::CREATE ) !== TRUE) {
            exit ( '无法打开文件，或者文件创建失败' );
        }
        //$fileNameArr 就是一个存储文件路径的数组 比如 array('/a/1.jpg,/a/2.jpg....');
        foreach ( $fileNameArr as $val ) {
            $zip->addFile ( $val, basename ( $val ) ); // 第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
        }
        $zip->close (); // 关闭

        foreach ($fileNameArr as $v)
        {
            if (file_exists($v) && is_readable($v))
            {
                unlink($v);       //删除下载在本地的excel文件
            }
        }

        if ($type =='dsrw')
        {
            $cxo = M()->query("select * from xsrb_excel where dept_id =".$dept_id." and `date` =".$sdate." and edate ='$edate' and `biao` ='fdmkcbtjb' ");
            if(!count($cxo))
            {
                $cxo =1;
                if ($keys !='')    //上传成功返回url时,存入数据库
                {
                    $keys = "http://xsrb.wsy.me:801/files/".$dept_id.'-'.'fdmkcbtjb-'.$sdate.'-'.$edate. ".zip";
                    M()->execute("insert into xsrb_excel(`createtime`,dept_id,biao,date,edate,url)value(now(),$dept_id,'fdmkcbtjb','$sdate','$edate','$keys')");
                }
            }
            return;
        }
        //$fileNameArr[] = $filename;
        //下面是输出下载;
        header ( "Cache-Control: max-age=0" );
        header ( "Content-Description: File Transfer" );
        header ( 'Content-disposition: attachment; filename=' . basename ( $filename ) ); // 文件名
        header ( "Content-Type: application/zip" ); // zip格式的
        header ( "Content-Transfer-Encoding: binary" ); // 告诉浏览器，这是二进制文件
        header ( 'Content-Length: ' . filesize ( $filename ) ); // 告诉浏览器，文件大小
        @readfile ( $filename );//输出文件;
    }
}