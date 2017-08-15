<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/7
 * Time: 14:10
 */
namespace Api\Controller;
use Think\Controller\RestController;
//define(SEARCH_URL,'http://192.111.110.21/get_gh.php');//查询地址
define(SEARCH_URL,'http://222.209.223.240:10001/get_gh.php');//查询地址
class ProgressQueryController extends RestController {
    public function query($key='',$token=''){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2,array('resultcode'=>-2,'resultmsg'=>'登陆失效')),'json');
            return;
        }
        $ch = curl_init();
        $url=SEARCH_URL."?key=$key&group=chuchutongda";
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        $data=json_decode($output,true);
        $data['resultcode']=0;
        $data['resultmsg']='操作成功';
        $this->response(retmsg(0,$data),'json');
    }
}
