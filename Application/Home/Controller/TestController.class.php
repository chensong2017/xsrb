<?php
namespace Home\Controller;
use Think\Controller;
use OSS\OssClient;
use name\test2;

/**
 * 销售日报表期初
 */
class TestController extends Controller
{
	public function test()
	{
	    
		/* $redis = new \Redis();
		$redis->connect("10.132.17.32","6379");
		echo $redis->get("report-depart-template"); */
		 //$redis;
	    $url="";
		echo 123456789;
		exit();
	    $ch = curl_init();
	    $timeout = 5;
	    curl_setopt ($ch, CURLOPT_URL, $url);
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	    $file_contents = curl_exec($ch);
	    curl_close($ch);
	     echo $file_contents;
	}
	
	
}