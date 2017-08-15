<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
set_time_limit(300);
header("Access-Control-Allow-Origin: *");
//uploadfile_ali_160112();
function uploadfile_ali_160112($xlspath)
{
	
	//$xlspath=$_GET['xlspath'];
// 	echo $xlspath.'sasasasa';return;
	$bucket = Common::getBucketName();
	$ossClient = Common::getOssClient();
	if (is_null($ossClient)) exit(1);
	$ret = array();
	
	$filename=substr($xlspath,strripos($xlspath,'/')+1);
	$filename=explode(".",$filename);
	
	   $object="xsrb/".$filename[0];
	   
		try 
		{
		    $ossClient->uploadFile($bucket, $object, $xlspath);
		    $ret['resultcode'] = "0";
			$ret['resultmsg'] = "upload success!";
			$ret['data']['url'] = Config::OSS_DOWNLOAD_DIR."/".$object; 
		   /*  $content="hello";
		    $ossClient->putObject($bucket, $object, $content); */
	    } 
		catch (OssException $e) 
		{
			$ret['resultcode'] = "-1";
			$ret['resultmsg'] = "$e";
			$ret['data']['url'] = ""; 
		}
    
	//}
	return json_encode($ret);

}
?>