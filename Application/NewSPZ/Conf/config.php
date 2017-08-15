<?php
//定义当前录入时间
$now_hour=date("H");
if((int)$now_hour<4)
    define("TODAY",date("Ymd",strtotime("-1 day")));
else
    define("TODAY",date("Ymd"));
define("XSRB_IP","http://".$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"]);

define("TODAY1",date("Ymd",strtotime("-1 day")));
return array(
    //'配置项'=>'配置值'
        'DB_TYPE'   => 'mysqli', // 数据库类型
		'DB_HOST'               => 'rm-bp1nv3z13yh2u390d.mysql.rds.aliyuncs.com', // 服务器地址
		'DB_NAME'               => 'xsrb',          // 数据库名
		'DB_USER'               => 'xsrb',      // 用户名
		'DB_PWD'                => 'sdfa57_kkd85_jkdkfa5',          // 密码
		'DB_PORT'               => '3306',        // 端口
		'DB_PREFIX'             => '',    // 数据库表前缀
        'SHOW_PAGE_TRACE' =>false,  //
        'SHOW_ERROR_MSG' =>    false,
		'CACHE_NAME'			=>	'xsrb',		//ACE缓存名称
		'Cache_TimeOut_Token'	=>	60*60*24*15,		//token缓存时间，单位：秒
        'ERROR_MESSAGE'  =>    '发生错误！',
        'REDIS_URL'=>'1829898179b64795.redis.rds.aliyuncs.com',//redis的url
        'REDIS_PWD'=>'kiujk48VBJk82euiJycxvb',
		'MEM_URL'=>'afb33e1381504b4d.m.cnhzalicm10pub001.ocs.aliyuncs.com',
		'UPEXCEL_URL'			=> XSRB_IP.'/upload/uploadfile.php?xlspath=',
		'Controller_url'		=> XSRB_IP.'/index.php/NewSPZ'		//手动下载excel属于的url
         );