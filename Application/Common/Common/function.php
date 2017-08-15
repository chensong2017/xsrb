<?php
//输出函数
function p($value)
{
	if (is_bool($value))
	{
		var_dump($value);
	}elseif (is_null($value))
	{
		var_dump(NULL);
	}else
	{
		echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;font-size:14px;line-height:18px;opacity:0.9;'>".print_r($value,true)."</pre>";
	}
}
//检查版本,以选取时间前的最新为准
function checkbanben($filepath,$banben)
{
    $chk = file_exists($filepath."/".$banben);
    if (!$chk)
    {
        $mon = $banben.'01';
        $banben = checkbanben($filepath,date("Ym",strtotime("$mon -1 month")));
    }else
    {
        return $banben;
    }
    return $banben;
}

//防注入式
function safe($s)
{ 
	//安全过滤函数
	if(get_magic_quotes_gpc())
	{
		$s=stripslashes($s);
	}
	//$s=mysql_real_escape_string($s);
	$s = addslashes($s);
	return $s;
}	

// 验证token公共函数
function checktoken($token)
{
	if(!isset($token) || empty($token))
		return false;
	import("Xsrb.Token");
	$token_obj = new \Token($token);
	return $token_obj->CheckToken(C('CACHE_NAME'),C('Cache_TimeOut_Token'));
}

// 验证短信验证码公共函数
function checkyzm($skey,$phone,$yzm)
{
	import("Xsrb.Token");
	$token_obj = new \Token();
					
	$json_str = $token_obj->GetKey(C('CACHE_NAME'),C('Cache_TimeOut_Token'),$skey."_seesionid");
	$s_arr = "";
	if(!$json_str)//session不存在
	{
		return -3;
	}
	else		//session存在
	{
		$s_arr = json_decode($json_str,true);
		if($s_arr["mobile"] != $phone)
		{
			return -10;
		}
		if($s_arr["mobile_code"] != $yzm)
		{
			return -11;
		}
		$token_obj->DeleteKey(C('CACHE_NAME'),C('Cache_TimeOut_Token'),$skey."_seesionid");
	}
	return 0;
}

//根据省、市、区县 匹配办事处
function getoffice(&$model,$province,$city,$county)
{
	//根据地址匹配办事处

	$office = "";
	//匹配区县
	$sql = "select office from area_county where county='".safe($county)."'";
	$sqlret=$model->query($sql);
	if(!is_bool($sqlret))
	{
		if(count($sqlret) > 0)
		{
			$office = $sqlret[0]["office"];
		}
		else
		{
			//匹配市
			$sql = "select office from area_city where city='".safe($city)."'";
			$sqlret=$model->query($sql);
			if(!is_bool($sqlret))
			{
				if(count($sqlret) > 0)
				{
					$office = $sqlret[0]["office"];
				}
				else
				{
					//匹配省
					$sql_office = "select office from area_province where province='".safe($province)."'";
					$sqlret=$model->query($sql);
					if(!is_bool($sqlret))
					{
						if(count($sqlret) > 0)
						{
							$office = $sqlret[0]["office"];
						}
						else
						{
							$office = "";
						}
					}
					else
					{
						return -17;
					}
				}
			}
			else
			{
				return -16;
			}
		}
	}
	else
	{
		return -15;
	}
	return $office;
}

//验证码算法
function random($length = 6 , $numeric = 0) 
{
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) 
	{
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} 
	else
	{
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) 
		{
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

//手机号格式验证
function isPhoneNum($phone)
{
	return preg_match("/^1[3|4|5|6|7|8][0-9]\d{8}$/",$phone);
}

//获取最近办事处
function getlastoffice(&$model,$userphone)
{
	$sql = "select lastoffice,office from Xsrb_user where phone='".$userphone."'";
	$list = $model->query($sql);
	if(isset($list[0]["lastoffice"]) && $list[0]["lastoffice"] != '')
	{
		return $list[0]["lastoffice"];
	}
	else if(isset($list[0]["office"]) && $list[0]["office"] != '')
	{
		return $list[0]["office"];
	}
	return "";
}

//curl send
function http($url, $params, $method = 'GET', $header = array(), $multi = false)
{
    $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method))
	{
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return  $data;
}

//返回函数
function retmsg($retcode,$retdata=null,$retmessage=null)
{
	$retmsg = "";
	switch($retcode)
	{
		case 0	: { $retmsg = "操作成功"; break; }
		case -1	: { $retmsg = "操作失败"; break; }
		case -2	: { $retmsg = "token验证失败"; break; }
		case -3	: { $retmsg = "短信验证码过期"; break; }
		case -4	: { $retmsg = "号码已被注册"; break; }
		case -5	: { $retmsg = "手机号格式不正确"; break; }
		case -6	: { $retmsg = "今日短信条数已使用完"; break; }
		case -7	: { $retmsg = "一分钟内只能获取一次短信"; break; }
		case -8	: { $retmsg = "验证码发送失败"; break; }
		case -9	: { $retmsg = "密码错误或用户不存在"; break; }
		case -10: { $retmsg = "手机号码不匹配"; break; }
		case -11: { $retmsg = "验证码错误"; break; }
		case -12: { $retmsg = "原密码错误"; break; }
		case -13: { $retmsg = "用户信息未做任何修改"; break; }
		case -14: { $retmsg = "密码不能为空"; break; }
		case -15: { $retmsg = "查询势力范围内的办事处失败(区县)"; break; }
		case -16: { $retmsg = "查询势力范围内的办事处失败(市)"; break; }
		case -17: { $retmsg = "查询势力范围内的办事处失败(省)"; break; }
		case -18: { $retmsg = "skey不能为空"; break; }
		case -19: { $retmsg = "该号码已被注册"; break; }
		case -20: { $retmsg = "图片内容不能为空"; break; }
		case -21: { $retmsg = "图片base64内容格式错误"; break; }
		case -22: { $retmsg = "评论内容不能为空"; break; }
		case -23: { $retmsg = "评论时,资讯id不能为空"; break; }
		case -24: { $retmsg = "收货地址未作修改"; break; }
		case -25: { $retmsg = "删除收货地址时id不能为空"; break; }
		case -25: { $retmsg = "未找到对应的收获地址"; break; }
		case -26: { $retmsg = "用户不存在"; break; }
		case -27: { $retmsg = "无数据"; break; }
		case -28: { $retmsg = "删除订单时id不能为空"; break; }
		case -29: { $retmsg = "生成订单号失败"; break; }
		case -30: { $retmsg = "订单号重复"; break; }
		case -31: { $retmsg = "没有选择商品"; break; }
		case -32: { $retmsg = "参数错误"; break; }
		case -33: { $retmsg = "收货地址不能为空"; break; }
		case -34: { $retmsg = "收货地址id不能为空"; break; }
		case -35: { $retmsg = "服务端和客户端计算的总价不符"; break; }
		case -36: { $retmsg = "未做任何修改"; break; }
		case -37: { $retmsg = "本期剩余参与次数不够"; break; }
		case -38: { $retmsg = "购物车最多不能超过10件物品"; break; }
		case -39: { $retmsg = "余额不足，请充值！"; break; }
        case -40: {$retmsg = "版本不正确,请重新登录！"; break; }
        case -41: {$retmsg = "系统维护中,请耐心等待!预计上午10点恢复"; break; }		
		//管理后台错误码
		case -51: { $retmsg = "您没有管理员权限"; break; }
		default	: { $retmsg = "未知错误";}
	}
	return array("resultcode"=>$retcode,"resultmsg"=>empty($retmessage)?$retmsg:$retmessage,"data"=>$retdata);
}

//导入excel去空格
function excel_trim($content){
    $test=1;
    if(is_object($content)){
        $str=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$content->__toString());
    }
    $str=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$content);
    $chars=array(" ","　","\t","\n","\r","?");
    return str_replace($chars,"",$str);
}

//导入excel去空格,如果是0 则会返回'' 主要用来判断期初 0视为没有录入
function excel_trim2($content){
    $test=1;
    if(is_object($content)){
        $str=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$content->__toString());
    }
    $str=preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/","",$content);
    $chars=array(" ","　","\t","\n","\r","?");
    $str=str_replace($chars,'', $str);
    return empty($str)?"":$str;
}

//检查当前是否可以录入数据
function check_submit_time(){
    $time=M('xsrb_test_time')->select();
    if(empty($time)){
        return;
    }
    if(strtotime($time[0]['start_time'])<time()&&strtotime($time[0]['end_time'])>time()){
        echo json_encode(array('resultcode'=>0,'resultmsg'=>'系统正在维护，请于：'.$time[0]['end_time'].'后再录入数据',null));exit();
    }
}

?>