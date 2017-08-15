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
        //$SMCPKCBMX = new SMCPKCBMXController();
        //$SMCPQCBTJ = new SMCPQCBTJController();
        $QCCXTJ = new QCCXTJController();
        $TJFYMX = new TJFYMXController();
        $FYMX = new FYMXController();
        $CGXSRBHZ = new CGXSRBHZController();
        //$FDMKCBQCCXBB = new FDMKCBQCCXBBController();
        //$FDMKCBMX = new FDMKCBMXController();
        $XSRBMX = new XSRBMXController();
        $SPMX = new SPMXController();
        $QTSRMX = new QTSRMXController();
        $YSZKMX = new YSZKMXController();
        //$FDMKCBTJB =new FDMKCBTJBController();

		//三月新增
        $NEW_SMCPKCBMX = new NewSMCPKCBMXController();
        $NEW_SMCPQCBTJ = new NewSMCPQCBTJController();
        $FDMKCHZ=new FDMKCHZController();
		$SPZMX = new SPZMXController();
		$SPZQCTJ = new SPZQCTJController();

		//7月测试
        $NEW_FDMKCHZ=new \NewSPZ\Controller\FDMKCHZController();
        $NEW_SPZMX=new \NewSPZ\Controller\SPZMXController();
        $NEW_SPZQCTJ=new \NewSPZ\Controller\SPZQCTJController();
        
/* 		$table=array($FDMKCBTJB,$CGXSRBHZ,$XSRBMX,$QTSRMX,$YSZKMX,$FYMX,$SPMX,$QCCXTJ,$TJFYMX,
		    $FDMKCBQCCXBB,$SMCPQCBTJ,$SMCPKCBMX,$FDMKCBMX,$NEW_SMCPKCBMX,$NEW_SMCPQCBTJ,$FDMKCHZ,$SPZMX);  */  
		$table=array($CGXSRBHZ,$XSRBMX,$QTSRMX,$YSZKMX,$FYMX,$SPMX,$QCCXTJ,$TJFYMX,
		    $NEW_SMCPKCBMX,$NEW_SMCPQCBTJ,$FDMKCHZ,$SPZMX,$SPZQCTJ,$NEW_FDMKCHZ,$NEW_SPZMX,$NEW_SPZQCTJ);
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
		
		if($type =='info')
		{
			$info = $redis->info();
			echo "redis内存使用情况:".$info['used_memory']."字节/".$info['used_memory_human'].'<br/>';
			echo "最大内存的峰值:".$info['used_memory_peak_human'].'<br/>';
			echo "内存碎片率:".$info['mem_fragmentation_ratio'].'<hr/>';
			echo '<pre/>';
			print_r($info);
			return;
		}
		$info = $redis->info();
        echo "redis内存使用:".$info['used_memory']."字节/".$info['used_memory_human'].'<br/>';
        echo "最大内存的峰值:".$info['used_memory_peak_human'].'<br/>';
        echo "内存碎片率:".$info['mem_fragmentation_ratio'].'<hr/>';
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
		$tiao = 1;
		foreach ($biao as $vbiao)
		{

			if ($vbiao == 'XSRBQC' || $vbiao == 'FDMKCQC' || $vbiao == 'SMCPKCQC')
			{
				$shijian = floor((strtotime($edate)-strtotime($sdate))/(60*60*24*30));;
			
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
							echo $tiao."--|--report-$dept-$date-$vbiao<br/>";
					}elseif($type =='del')		//删除指定的redis
					{
						$aa = $redis->del("report-$dept-$date-$vbiao");		//redis删除操作
						if ($aa == 1)
							echo $tiao."--|--report-$dept-$date-$vbiao<br/>";
					}
					$tiao ++;
				}
			}
		}
	}
	
	//清除版本更换前,有生成的redis,录入数据
	public function clear($date)
	{
		$redis = new \Redis();
		$redis->connect(C('REDIS_URL'),"6379");
		$redis->auth(C('REDIS_PWD'));
		$dept = M()->query("select * from xsrb_department ");
		$yue1 = date("Ym",strtotime($date));
		$yue2 = date("Y-m",strtotime($date));
		$yuechu = date("Ym01",strtotime($date));
		
		//清除redis期初
		foreach($dept as $v)
		{
			$dept_id = $v['id'];
			
			//期初数据
			//$redis->del("report-$dept_id-$yue-XSRBQC");
			$redis->del("report-$dept_id-$yue1-FDMKCQC");
			//$redis->del("report-$dept_id-$yue-SMCPKCQC");
			
			//录入数据
			//$redis->del("report-$dept_id-$yuechu-XSRBLR");
			$redis->del("report-$dept_id-$yuechu-FDMKC");
			//$redis->del("report-$dept_id-$yuechu-SMCPKC");
			
			//数据库数据
			M()->execute("delete from fdmkcb where dept=$dept_id and `date` ='$yuechu'");
			
			M()->execute("delete from fdmkcbqc where dept=$dept_id and `date` ='$yue2'");
		}
		
	}
    //外部获取系统处理保存数据的时间
    public function SERVER_TIME(){
        header("Access-Control-Allow-Origin: *");
        $this->response(array('server_time'=>date('Y-m-d',strtotime(TODAY))),'json');
    }	
}



