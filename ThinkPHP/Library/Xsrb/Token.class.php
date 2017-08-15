<?php 
//namespace ChaoGe;
//token操作类 
//暂不支持加密中文

class Token
{
	//token
	private $token = "";
	private $mem = null;
	function __construct($token=null)
	{
		if($token && !empty($token))
		{
			$this->token = $token;
		}
		
		$this->mem = new Memcache;
		$this->mem->connect(C("MEM_URL"), 11211);
	}
	

/*	public function GetTokenInfo($catchname,$catchtime)
	{
		$cache = \Alibaba::Cache($catchname);
		if($catchtime <= 0 || !($userinfo = $cache->get($this->token)))//token过期或不存在
		{
			//echo "token不存在phone=".$cachekey;
			return false;
		}
		else//token存在
		{
			return json_decode($userinfo,true);
		}
	}*/
	
	//获取token(每次都是新的)
	public function GetToken($catchname,$catchtime,$userinfo)
	{
		$tokenPars = "";
		ksort($userinfo);
		foreach($userinfo as $k => $v) 
		{
			if("" != $v)
			{
				$tokenPars .= $k . "=" . $v . "&";
			}
		}
		$this->token = strtolower(md5($tokenPars));
		
		$cachekey = $this->token;
		
		//清除旧token
		$old_token = $this->mem->get("user".$userinfo["uid"]);
		$this->mem->delete($old_token);
		
		$this->mem->set($cachekey, json_encode($userinfo), 0, $catchtime);
		$this->mem->set("user".$userinfo["uid"], $this->token);

		return $this->token;
	}
	
	//检查token是否存在
	public function CheckToken($catchname,$catchtime)
	{
		$cachekey = $this->token;
		$userinfo = $this->mem->get($cachekey);
		if($catchtime <= 0 || !$userinfo)//token过期或不存在
		{
			return false;
		}
		else//token存在
		{
			$s_arr = json_decode($userinfo,true);
			$last_user_token = $this->mem->get("user".$s_arr["uid"]);
			if($last_user_token == $this->token)
			{
				return $s_arr;
				//return true;
			}
			else
			{
				$this->mem->delete($this->token);
				return false;
			}
		}
	}
	
	//删除以key为键值的数据
	public function DeleteKey($catchname,$catchtime,$key)
	{
		return $this->mem->delete($key);
	}
	
	//检查key是否存在
	public function GetKey($catchname,$catchtime,$key)
	{
		return $this->mem->get($key);
	}
}
?>