<?php
namespace Home\Controller;
use Think\Controller\RestController;
/**
 * 定时调度控制器，每天调度 schedule方法
 *
 * @author Administrator
 */
class ScheduleController extends RestController
{

    public function schedule($date = TODAY1, $type = '')
    {
        $ret =1;
        set_time_limit(0);
        $SMCPKCBMX = new SMCPKCBMXController();
        $SMCPQCBTJ = new SMCPQCBTJController();
        $QCCXTJ = new QCCXTJController();
        $TJFYMX = new TJFYMXController();
        $FYMX = new FYMXController();
        $CGXSRBHZ = new CGXSRBHZController();
        $FDMKCBQCCXBB = new FDMKCBQCCXBBController();
        $FDMKCBMX = new FDMKCBMXController();
        $XSRBMX = new XSRBMXController();
        $SPMX = new SPMXController();
        $QTSRMX = new QTSRMXController();
        $YSZKMX = new YSZKMXController(); 
        
        
        $table=array($SMCPKCBMX,$FDMKCBMX,$XSRBMX,$SMCPQCBTJ,$FDMKCBQCCXBB,$CGXSRBHZ,$TJFYMX,$QCCXTJ,$SPMX,$FYMX,$YSZKMX,$QTSRMX);
        foreach ($table as $value) 
        {
            
            $file_contents = $value->uploadExcel($date);
            $data = json_decode($file_contents, true);
            
            if ($data['resultcode'] != 1) {
                echo $data['resultmsg'];
                $ret =-1;
            }
        }
        if($ret ==1)
            $this->response('{"resultcode":1,"resultmsg":"定时上传成功"}');
            else
                $this->repsonse('{"resultcode":-1,"resultmsg":"定时上传成功上传失败"}');
    }
	
	/**
	*清除redis数据
	*
	* @param number $sdate	开始日期
	* @param string $edate	截止日期
	* @param string $dept	要删除的部门id
	* @param string $biao	要删除的表名,以&符号来连接
	* @param string $type	查询seach,删除del
	* @param string $zhiding 指定删除某个redis(截止日期参数可以不使用)
	*
	*/
	public function delRedis($sdate ='20160601',$edate='',$dept='',$biao='',$type ='search',$zhiding ='')
	{
		ini_set('max_execution_time',3000);
		$redis = new \Redis();
		$redis->connect(C('REDIS_URL'),"6379");
		$redis->auth(C('REDIS_PWD'));
		if ($biao !='')			//指定要删除的redis表名
		{
			$biao = explode('&', $biao);
		}else
		{
			$biao = array(		//默认的删除表名
					'XSRBLR','FDMKC','SMCPKC'
			);
		}
		$arr = json_encode(array(		//操作记录
				'sdate'=>$sdate,
				'edate'=>$edate,
				'dept'=>$dept,
				'biao'=>$biao,
				'type'=>$type,
				'zhiding'=>$zhiding
		));
		$shijian = $type.date('Y-m-d H:i:s');
		$sql_log = "insert into test(c1,c2,c3) value('redis','$shijian','$arr')";
		M()->execute($sql_log);
		if ($zhiding !='')		//指定删除某个redis
		{
			$redis->del("$zhiding");
			$this->response(array('retmsg'=>'删除ok'),'json');
		}
		if($dept !='')		//指定要删除的部门redis
		{
			$sql = "select id from xsrb_department where id = $dept";
		}else
		{
			$sql = "select id from xsrb_department";		//默认全部部门
		}
	
		$re = M()->query($sql);
	
	
		if($edate =='')		//必须设置截止时间
		{
			$this->response(array('retmsg'=>'截止日期未设置!'),'json');
		}
		foreach ($biao as $vbiao)
		{
			if ($vbiao == 'XSRBQC' || $vbiao == 'FDMKCQC' || $vbiao == 'SMCPKCQC')
			{
				$shijian = ceil((strtotime($edate)-strtotime($sdate))/(60*60*24*30));;
	
			}else
			{
				$shijian = ceil((strtotime($edate)-strtotime($sdate))/(60*60*24));;
			}
			foreach ($re as $vdept)
			{
				$dept = $vdept['id'];
				for ($i=0;$i<=$shijian;$i++)
				{
					if ($vbiao == 'XSRBQC' || $vbiao == 'FDMKCQC' || $vbiao == 'SMCPKCQC')
					{
						$date = date("Ym",strtotime("-$i month",strtotime($edate)));
					}else
					{
						$date = date("Ymd",strtotime("-$i day",strtotime($edate)));
					}
					if($type =='search')		//查询redis存在
					{
						$aa = $redis->exists("report-$dept-$date-$vbiao");
						if($aa == 1)
							echo "report-$dept-$date-$vbiao<br/>";
					}elseif($type =='del')		//删除指定的redis
					{
						$aa = $redis->del("report-$dept-$date-$vbiao");		//redis删除操作
						if ($aa == 1)
							echo "report-$dept-$date-$vbiao<br/>";
					}
				}
			}
		}
	}
}



