<?php
require_once __DIR__ . '/Common.php';

use OSS\OssClient;
use OSS\Core\OssException;

function uploadfile_ali_160112()
{
	$bucket = Common::getBucketName();
	$ossClient = Common::getOssClient();
	if (is_null($ossClient)) exit(1);
	
	//$retobj = $ossClient->putObject($bucket, "1.txt", "99845");
	//echo "aaa";return;
	$post_param_arr = json_decode(file_get_contents("php://input"),true);
	
	$pic = $post_param_arr['data']['pic'];

	$ret = array();

	if(sscanf($pic, "data:%[^/]/%[^;];base64,%[^.]",$type, $format, $content) == 3)
	{
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
	echo json_encode($ret);
}

uploadfile_ali_160112();				