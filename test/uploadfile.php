<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;
set_time_limit(300);
header("Access-Control-Allow-Origin: *");
for($i=0;$i<20;$i++)
{
	uploadfile_ali_160112();
}
function uploadfile_ali_160112()
{
	//$xlspath=$_GET['xlspath'];
	$xlspath=__DIR__ . '/18-xsrbmx-20160714.xls';
	//echo $xlspath;return;
	//echo $xlspath;
	$bucket = Common::getBucketName();
	$ossClient = Common::getOssClient();
	if (is_null($ossClient)) exit(1);
	$ret = array();
	//if ($_FILES["file"]["error"] > 0)
	//{
		//$ret['resultcode'] = "-1";
		//$ret['resultmsg'] = "upload file error!";
		//$ret['data']['url'] = "";
	//}
	//else
	//{
	//  echo "Upload: " . $_FILES["file"]["name"] . "<br />";
	//  echo "Type: " . $_FILES["file"]["type"] . "<br />";
	//  echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
	//  echo "Stored in: " . $_FILES["file"]["tmp_name"];
	//  $object = "111.png";
		$from = $_GET['from'];
		$path = "";
		if($from != "")
		{
			$path = $from."/";
		}
		else
		{
			$path = "ghsq/";
		}
		
		$object = $path.date("YmdHis").'_'.rand(10000,99999);
		sscanf($xlspath, "%*[^.].%s", $format);
		
		//echo sscanf('D:\\2.xls', "%*[^.].%s", $format);
		//echo $format;
		if($format  == "apk")
		{
			$ret['resultcode'] = "-1";
			$ret['resultmsg'] = "can not upload apk file!";
			$ret['data']['url'] = "";
			echo json_encode($ret);
			return;
		}
		
		if($format == "")
		{
			$format = "jpg";	
		}
		
		if($format != "")
		{
			$object .= '.'.$format;
		}
	    $options = array();
		try 
		{
		    $ossClient->uploadFile($bucket, $object, $xlspath, $options);
		    $ret['resultcode'] = "0";
			$ret['resultmsg'] = "upload success!";
			$ret['data']['url'] = Config::OSS_DOWNLOAD_DIR."/".$object;
	    } 
		catch (OssException $e) 
		{
			print_r ($e);
			$ret['resultcode'] = "-1";
			$ret['resultmsg'] = "upload file except!";
			$ret['data']['url'] = "";
		}
    
	//}
	echo json_encode($ret);
/*
	if(sscanf($pic, "data:%[^/]/%[^;];base64,%[^.]",$type, $format, $content) == 3)
	{
	//	$ossClient->putObject($bucket, "d.jpg", $ret["errormsg"]);
		$object = date("YmdHis").'_'.rand(10000,99999).'.'.$format;
		$data = base64_decode(str_replace(" ","+",$content));
		$retobj = $ossClient->putObject($bucket, $object, $data);
		$ret['resultcode'] = "0";
		$ret['resultmsg'] = "upload success!";
		$ret['data']['url'] = Config::OSS_DOWNLOAD_DIR."/".$object;
	}
	else 
	{
		$ret['resultcode'] = "-1";
		$ret['resultmsg'] = "base64 data error!";
		$ret['data']['url'] = "";
	}
	echo json_encode($ret);*/
}
?>