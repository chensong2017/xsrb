<?php
namespace Home\Controller;

use Think\Controller\RestController;
require_once 'XSRBUtil.php';
/**
 * 数码产品期初报表统计表
 * @author Administrator
 *
 */
class NewSMCPQCBTJController extends RestController{
    /**
     * @param $token $date $page
     * 每页显示25个部门
     * 读取mysql数据生成
     * $flag=true 打印excel标志
     */
    public function search($token='',$date=TODAY,$page=1,$pageSize=25,$flag=false,$dept=''){
        header("Access-Control-Allow-Origin: *");
        //根据日期查询对应版本的数据
        if(strtotime($date)>=strtotime("2017-08-01"))
            $version="20170801";
        if($token!=''){
            //验证token
            $userinfo = checktoken($token);
            if(!$userinfo)
            {
                $this->response(retmsg(-2),'json');
                return;
            }
            //部门id
            $dept_id=$userinfo['dept_id'];
                 //qt1判断是否是片区的标志
           $qt1=$userinfo['qt1'];
        }elseif($dept!=""){
            //部门id
             $dept_id=$dept['dept_id'];
            //qt1判断是否是片区的标志
              $qt1=$dept['qt1']; 
        }else 
            return; 
       /*  $dept_id=25;
        $qt1=22; */
        if($date==''){
            $date=date("Y-m-d");
        } 
        //响应数据
        $resposeData=array();
       
	    $yue = date('Y-m');
		
        $resposeData['page']=$page;
        //分权限查询部门数据
        $sql_total="select count(*) as total from xsrb_department where ";
        $sql_data="select t1.dname as dname,t1.id as id,t2.dname as pname from xsrb_department t1,xsrb_department t2
        where t1.pid=t2.id ";
        //如果为总部查询所有部门
        if($dept_id==1){
            $sql_total.=" id!=1 and pid!=1";
            $sql_data.=" and t1.id!=1 and t1.pid!=1 ORDER BY t1.pid DESC,t1.qt1";
        }
        //如果为片区则查出该片区下的所有部门
        elseif($qt1==0){
            $sql_total.=" pid='$dept_id' ";
            $sql_data.=" and t1.pid='$dept_id' ORDER BY t1.pid DESC,t1.qt1";
        }
        //具体某个部门的数据
        else{
            $sql_total.=" id='$dept_id' ";
            $sql_data.=" and t1.id='$dept_id'  ";
        }
        
		//当前月
		$date=date("Y-m",strtotime($date));
        $total=M()->query( $sql_total);
        $total=$total[0]['total'];
        if($total<$pageSize)
            $total=1;
        elseif($total%$pageSize==0)
            $total=$total/$pageSize;
        else 
            $total=$total/$pageSize+1;
        $resposeData['total']=(int)$total;
        $resposeData['page']=$page;
       //左边模板
        //2017-2-16 添加产品品牌
        $leftJson='[{"tr":[{"dataType":0,"value":"片区","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"产品类型","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品品牌","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品型号","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品规格","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""}]}]';
        $leftTemp=json_decode($leftJson,true);
        
/*        $sql="select  DISTINCT lx,xh,cpgg
        from smcpkcbqc GROUP BY lx,xh,cpgg,date,dept ORDER BY  lx,xh,cpgg limit 0,28";
       $result=M()->query($sql); */
        if($version=='20170801'){
            $result=unserialize('a:40:{i:0;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";}i:1;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";}i:2;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";}i:3;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";}i:4;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";}i:5;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";}i:6;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";}i:7;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";}i:8;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:9;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"科凡达";s:4:"cpgg";s:6:"小机";}i:10;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:11;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:12;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:13;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:14;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:15;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:16;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:17;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:18;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:19;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:20;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:21;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:22;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"新和特";s:4:"cpxh";s:9:"新世界";s:4:"cpgg";s:6:"小机";}i:23;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";}i:24;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";}i:25;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";}i:26;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";}i:27;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";}i:28;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"天宫一号-D";s:4:"cpgg";s:6:"小机";}i:29;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星云-D";s:4:"cpgg";s:6:"小机";}i:30;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";}i:31;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"塑壳";s:4:"cpgg";s:6:"塑壳";}i:32;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"铁壳";s:4:"cpgg";s:6:"铁壳";}i:33;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:23:"其他地面波(机子)";s:4:"cpgg";s:23:"其他地面波(机子)";}i:34;a:4:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";}i:35;a:4:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";}i:36;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";}i:37;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";}i:38;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";}i:39;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";}}');
        }
        else{
            $result=unserialize('a:46:{i:0;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";}i:1;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";}i:2;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";}i:3;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";}i:4;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";}i:5;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";}i:6;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";}i:7;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";}i:8;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:9;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:10;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:11;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:12;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:13;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:14;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:15;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:16;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:17;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:18;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:19;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:20;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:21;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:22;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:23;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:24;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:25;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:26;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";}i:27;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";}i:28;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";}i:29;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";}i:30;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";}i:31;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";}i:32;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"200塑壳";}i:33;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"225铁壳";}i:34;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"188铁壳";}i:35;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:7:"DTMB+S2";s:4:"cpxh";s:7:"DTMB+S2";s:4:"cpgg";s:9:"200塑壳";}i:36;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"高清";s:4:"cpxh";s:6:"高清";s:4:"cpgg";s:9:"225铁壳";}i:37;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:10:"高清+OTT";s:4:"cpxh";s:10:"高清+OTT";s:4:"cpgg";s:9:"225铁壳";}i:38;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"其他";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:15:"其他地面波";}i:39;a:4:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";}i:40;a:4:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";}i:41;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";}i:42;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";}i:43;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";}i:44;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";}i:45;a:4:{s:2:"lx";s:12:"电控产品";s:5:"brand";s:12:"电控产品";s:4:"cpxh";s:12:"电控产品";s:4:"cpgg";s:12:"电控产品";}}');
        }
       
       //每一页的部门（默认4个）
       foreach ($result as $key=>$value){
           $leftTemp[$key+3]['data'][]['value']=$value['lx'];
           $leftTemp[$key+3]['data'][]['value']=empty($value['brand'])?$value['cpgg']:$value['brand'];
           $leftTemp[$key+3]['data'][]['value']=$value['cpxh'];
           $leftTemp[$key+3]['data'][]['value']=$value['cpgg'];
       }
      // echo json_encode($leftTemp);
       //左边部分
       $resposeData['leftTitle']=$leftTemp;
        //模板上标题部分
        $tempTitleJ='[{"tr":[{"dataType":0,"value":"齐河销售处","rowspan":1,"colspan":"2","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"齐河防盗门一科","rowspan":1,"colspan":"2","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"有效结存","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效结存","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":""}]}]';
        $tempTitleA=json_decode($tempTitleJ,true);
        $from=($page-1)*$pageSize;
        //正式数据部分
        $sql_data.=" limit $from,$pageSize";
        $result=M()->query($sql_data);
        foreach ($result as $key=>$value){
            $tempTitleA[1]['tr'][0]['value']=$value['dname'];
            $tempTitleA[0]['tr'][0]['value']=$value['pname'];
            //表头标题
            $resposeData['data'][$key]['topTitle']=array();
            $resposeData['data'][$key]['topTitle']= $tempTitleA;
            $dept_id=$value['id'];
                $sql="select yxjc,wxjc from new_smcpkcbqc where date='$date' and dept='$dept_id' order by px";
            $tempData=M()->query($sql);
            if(empty($tempData)){
                /* $sql=" select 0 as yxjc,0 as wxjc from new_smcpkcbqc limit 0,46";
                $tempData=M()->query($sql); */
                if($version=='20170801'){
                    $s='a:40:{i:0;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:1;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:2;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:3;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:4;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:5;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:6;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:7;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:8;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:9;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:10;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:11;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:12;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:13;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:14;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:15;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:16;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:17;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:18;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:19;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:20;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:21;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:22;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:23;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:24;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:25;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:26;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:27;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:28;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:29;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:30;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:31;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:32;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:33;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:34;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:35;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:36;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:37;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:38;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:39;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}}';
                }
                else{
                    $s='a:46:{i:0;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:1;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:2;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:3;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:4;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:5;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:6;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:7;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:8;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:9;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:10;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:11;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:12;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:13;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:14;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:15;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:16;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:17;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:18;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:19;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:20;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:21;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:22;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:23;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:24;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:25;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:26;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:27;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:28;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:29;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:30;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:31;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:32;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:33;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:34;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:35;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:36;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:37;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:38;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:39;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:40;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:41;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:42;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:43;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:44;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}i:45;a:2:{s:4:"yxjc";i:0;s:4:"wxjc";i:0;}}';
                }
                $tempData=unserialize($s);
            }
            //产品总类（每一页的行数）
            foreach ($tempData as $rowKey=>$row){
              $resposeData['data'][$key]['content'][$rowKey]['tr']=array();
              $temp=array();
               $temp[]['value']=$row['yxjc'];
               $temp[]['value']=$row['wxjc'];
               
               $resposeData['data'][$key]['content'][$rowKey]['tr']=$temp;
            }
            
        }
        
        if($flag)
            return $resposeData;
        echo json_encode($resposeData);
    } 
    
    /**
     * 生成或者上传excel接口
     * 数据来源search接口
     * @param string $token
     * @param string $date
     * @param string $dept
     * @param $flag 后台上传excel调用标志
     * @return void|boolean|\PHPExcel_Writer_Excel5
     * $flag=false 直接返回输出流对象，$flag=true生成并上传excle返回上传成功标志（true or false）
     */
    public function toExcel($token='',$date=TODAY,$dept='',$flag=false){
        set_time_limit(600);
        header("Access-Control-Allow-Origin: *");
        //如果打印总部或片区的数据直接查询数据库
      if($flag){
            //部门id
            $dept_id=$dept['dept_id'];
            //上级部门id
            $pid=$dept['pid'];
        }else{
            //验证token
            $userinfo = checktoken($token);
             if(!$userinfo)
            {
                $this->response(retmsg(-2),'json');
                return;
            } 
            //部门id
            $dept_id=$userinfo['dept_id'];
            //$dept_id=25;
            //上级部门id
            $pid=$userinfo['pid'];
            //$pid=22;
        } 
       /*  $dept['pid']=1;
        $dept['dept_id']=1028; */
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
         
        $objPHPExcel = new \PHPExcel();
        //待打印的数据
        $printData=$this->search($token,$date,1,300,true,$dept);
        //左边标题部分
        $leftTitle=$printData['leftTitle'];
        //每个部门的数据部分
        $data=$printData['data'];
        //循环打印每一个部门的数据
        foreach ($data as $key_dept=>$value_dept){

            if (is_int($key_dept/128))
            {
                //当超过126个部门的时候,设置下一个sheet页
                $sheet = floor($key_dept/128);
                $objPHPExcel->createSheet($sheet);		//创建一个sheet
                $objPHPExcel->setactivesheetindex($sheet);
            }
            //当前部门的起始列数字索引
            $col_offset_n=$key_dept*2+1-256*$sheet;
            //打印topTitle
            $topTitle=$value_dept['topTitle'];
            foreach ($topTitle as $k_top=>$v_top){
                if($k_top==0){
                    //合并列列号
                    $des=$col_offset_n+1;
                    $des=$this->ToNumberSystem26($des);
                    //列的字母索引
                    $col_offset=$this->ToNumberSystem26($col_offset_n);
                    $objPHPExcel->getActiveSheet($sheet)->setCellValue($col_offset."1",$v_top['tr'][0]['value']);
                    //合并单元格
                    $from=$col_offset."1";
                    $des.="1";
                    $objPHPExcel->getActiveSheet($sheet)->mergeCells("$from:$des");
                     
                }elseif($k_top==1){
                    $des=$col_offset_n+1;
                    $des=$this->ToNumberSystem26($des);
                    $from=$col_offset."2";
                    $des.="2";
                    $objPHPExcel->getActiveSheet($sheet)->setCellValue($from,$v_top['tr'][0]['value']);
                    $objPHPExcel->getActiveSheet($sheet)->mergeCells("$from:$des");
                }
                elseif($k_top==2){
                    foreach ($v_top['tr'] as $k=>$v){
                        $col_offset=$col_offset_n+$k;
                        $col_offset=$this->ToNumberSystem26($col_offset);
                        $objPHPExcel->getActiveSheet($sheet)->setCellValue($col_offset."3",$v['value']);
                    }
                }
            }
            //打印数据部分
            $data=$value_dept['content'];
            foreach ($data as $k_con=>$v_con){
                $row_offset=$k_con+4;
                foreach ($v_con['tr'] as $key=>$value){
                    $col_offset=$col_offset_n+$key;
                    $col_offset=$this->ToNumberSystem26($col_offset);
                    $objPHPExcel->getActiveSheet()->setCellValue("$col_offset$row_offset",$value['value']);
                }
            }
             
        }     
        // 直接输出到浏览器
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        //定时调度上传
       if($flag){
           $dept_id=$dept['dept_id'];
           return \XSRBUtil::uploadExcel($dept_id, $objWriter,'NEW_SMCPQCBTJ',$date);
       }else 
           return $objWriter;
        
    }
    
    //打印excel
    public function printExcel($token='',$date=''){
         set_time_limit(600);
         header("Access-Control-Allow-Origin: *"); 
        
             //验证token
             $userinfo = checktoken($token);
             if(!$userinfo)
             {
                 $this->response(retmsg(-2),'json');
                 return;
             } 
             //部门id
             $dept_id= $userinfo['dept_id'];
             //上级部门id
             $pid=$userinfo['pid'];
             $qt1=$userinfo['qt1'];
             /* $dept_id=25;
             $pid=22; */
            if ($date == '') {
                $date = date("Ymd",strtotime("-1 day"));
            } else {
                if ($date >= date('Ymd'))
                    $date = date("Ymd",strtotime("-1 day"));
                    else
                        $date = date("Ymd", strtotime($date));
            }
        
           //如果是片区或者是总部则直接访问上传的excle
         if($qt1==0||$dept_id==1){
             
            $a = \XSRBUtil::download($dept_id,$date,'NEW_SMCPQCBTJ');
            if ($a ==-1)
            {
                $objWriter=$this->toExcel($token,$date);
                $filename = "NEW_SMCPQCBTJ-$date-$dept_id.xls";
                
                //输出到浏览器
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");;
                header("Content-Disposition:attachment;filename=$filename");
                header("Content-Transfer-Encoding:binary");
                $objWriter->save('php://output');
            }
         }
         //如果是普通部门则生成excle
         else {
             $objWriter=$this->toExcel($token,$date);
         //输出到浏览器
         			         $filename = "NEW_SMCPQCBTJ-$date-$dept_id.xls";
             header("Pragma: public");
             header("Expires: 0");
             header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
             header("Content-Type:application/force-download");
             header("Content-Type:application/vnd.ms-execl");
             header("Content-Type:application/octet-stream");
             header("Content-Type:application/download");;
             header("Content-Disposition:attachment;filename=$filename");
             header("Content-Transfer-Encoding:binary");
             $objWriter->save('php://output');
         }
     
    }
    
    /**
     * 十进制转26进制
     * @param number $n 十进制参数
     * @return string 26进制返回字符串
     */
    public function  ToNumberSystem26($n=1){
        while ($n > 0){
            $m = $n % 26;
            if ($m == 0) $m = 26;
            $s = chr($m + 64).$s;
            $n = ($n - $m) / 26;
        }
        return $s;
        //echo $s;
    }
    
    /**
     * 定时调度上传各部门的excel
     */
    public function uploadExcel($date=TODAY){
         set_time_limit(600);
         $dept=array();
          $biao='NEW_SMCPQCBTJ';
        $sql="select id,pid from xsrb_department where qt1 in (0,1)  and id not in(
             select dept_id from xsrb_excel where date='$date' and biao='$biao') ";
         $depts=M()->query($sql);
		 $ret=1;
         foreach ($depts as $row){
             $dept['dept_id']=$row['id'];
             $dept['pid']=$row['pid'];
             $ret=$this->toExcel("",$date,$dept,true); 
         }
         if($ret)
            return '{"resultcode":1,"resultmsg":"新数码产品期初报表统计表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"新数码产品期初报表统计表上传失败"}';
     }
}

?>