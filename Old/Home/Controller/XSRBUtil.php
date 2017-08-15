<?php
/**
 * 销售日报工具类
 * @author Administrator
 *
 */
include_once 'upload/uploadfile.php';
define('WWW_PATH',str_replace('\\','/',realpath(__DIR__.'/../../../')));
class XSRBUtil{
    
    /**
     * 
     * @param unknown $dept_id
     * @param PHPExcel_Writer_Excel $objWriter 输出流对象
     * @param unknown $biao 要上传的表名称
     */
    public static function uploadExcel($dept_id,&$objWriter,$biao,$date=TODAY){
		 set_time_limit(600);
        ini_set('memory_limit', "-1");//设置内存无限制
       
        $filename="$biao-$date-$dept_id";
        $savepath=str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$filename.".xls";
		 try {
			// chmod(WWW_PATH."/tempExcel/",0777); 
            $objWriter->save($savepath);
        } catch (Exception $e) {
            echo $e;
            return;
        }
		//$basePath=$_SERVER['HTTP_HOST'];
		/* $basePath="http://114.55.123.85:801";
        $url=$basePath."/upload/uploadExcel.php?xlspath=$savepath"; */
		//echo "         basepath:".$url;
//         $ch = curl_init();
//         $timeout =300;
//         curl_setopt ($ch, CURLOPT_URL, $url);
//         curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
//         curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		
//         $file_contents = curl_exec($ch);
//         curl_close($ch);
/* 		$file_contents=uploadfile_ali_160112($savepath);
        $data=json_decode($file_contents,true);
        $url=$data['data']['url']; */
/* 		echo "         picurl:".$url;
        //写入数据库
        if($url=="")
            return false;
		
        $sql="insert into xsrb_excel(dept_id,biao,date,url) values('$dept_id','$biao','$date','$url') ";
        $result=M()->execute($sql);
		echo "xsrbutil ".$url;return;
        if($result)
            return true;
            else
                return false; */
		$savepath="http://xsrb.wsy.me:801/files/".$filename.".xls";
		$url = $savepath;	
		if($url !='')
		{
			$che = M()->query("select * from xsrb_excel where dept_id ='$dept_id' and `date` ='$date' and `biao` ='$biao'");
			if(!count($che))
			{
				$che =1;
				$sql="insert into xsrb_excel(dept_id,biao,date,url) values('$dept_id','$biao','$date','$url') ";
				$result=M()->execute($sql);			
			}
		}
		if($result)
            return true;
		else
			return false;
    }
    
    /**
     * 
     * @param string $dept_id
     * @param unknown $pid
     * @param string $date
     * @param string $biao 要下载的表名称
     */
    public static function download($dept_id='',$date='',$biao=''){
		 set_time_limit(600);
       
	    //默认下载昨天的数据
		if ($date == '') {
			$date = date("Ymd",strtotime("-1 day"));
		} else {
			if ($date >= date('Ymd'))
				$date = date("Ymd",strtotime("-1 day"));
			else
				$date = date("Ymd", strtotime($date));
		}

            $s="select * from xsrb_excel where date='$date'  and dept_id='$dept_id'  and biao='$biao' limit 1";

            $r=M()->query($s);
           
		  
            if(empty($r)){
/* 				echo "error";
                echo "{'resultcode':-1,'resultmsg':'定时导出出现异常！'}"; */
                return -1;
            }
			if($_SERVER['SERVER_NAME'] =='172.16.10.252')
			{
				$excel_url ="http://172.16.10.252/files/".$biao."-".$date."-".$dept_id.".xls" ;
			}else
			{
				$excel_url =$r[0]['url'];
			}
          
            $url=$excel_url;
			
           header( 'Location: '.$url );
           return;
        
    }
}

?>