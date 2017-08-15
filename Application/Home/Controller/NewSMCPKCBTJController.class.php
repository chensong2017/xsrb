<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 * 数码产品库存报表统计表
 * @author Administrator
 *
 */
class NewSMCPKCBTJController extends RestController{
  
    /**
     * @param $token $date $page
     * 每页显示四个部门
     * 读取mysql数据生成
     * $pageSize默认每页显示四个部门的数据
     * $flag 打印excel 标志
     */
    public function search($token='',$s_date='',$e_date='',$page=1,$pageSize=4,$flag=false){
        header("Access-Control-Allow-Origin: *");
        if(strtotime($s_date)<strtotime("2017-08-01")&&strtotime($e_date)>=strtotime("2017-08-01")){
            $this->response(retmsg(-1,null,'赞不支持跨版本数据查询！'),'json');
        }
        //根据日期查询对应版本的数据
        if(strtotime($s_date)>=strtotime("2017-08-01"))
            $version="20170801";
       //验证token
         $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }  
		$yuechu = date('Ym01',strtotime($e_date));
        //部门idsfdds
        $dept_id = $userinfo['dept_id'];
        //$dept_id=25;
        //上级部门id
        $pid=$userinfo['pid'];   
        
        if($s_date==''){
            $s_date=date("Y-m-d");
        }
        if($e_date==''){
            $e_date=date("Y-m-d");
        }
        //响应数据
        $resposeData=array();
        $resposeData['page']=$page;
       
        //分权限查询部门数据
        $sql_total="select count(*) as total from xsrb_department where ";
        $sql_data="select t1.dname as dname,t1.id as id,t2.dname as pname from xsrb_department t1,xsrb_department t2 
        where t1.pid=t2.id ";
        //如果为总部查询所有部门
        if($pid==0){
            $sql_total.=" id!=1 and pid!=1";
            $sql_data.=" and t1.id!=1 and t1.pid!=1 ORDER BY t1.pid DESC,t1.qt1";
        }
        //如果为片区则查出该片区下的所有部门
        elseif($pid==1){
            $sql_total.=" pid='$dept_id' ";
            $sql_data.=" and t1.pid='$dept_id' ORDER BY t1.pid DESC,t1.qt1";
        }
        //具体某个部门的数据
        else{
            $sql_total.=" id='$dept_id' ";
            $sql_data.=" and t1.id='$dept_id'  ";
        }
        
        $total=M()->query($sql_total);
        $total=$total[0]['total'];
        if($total<$pageSize)
            $total=1;
        elseif($total%$pageSize==0)
            $total=$total/$pageSize;
        else 
            $total=$total/$pageSize+1;
        $resposeData['total']=(int)$total;
        $resposeData['page']=$page;
        //左边模板 2017-2-16添加产品品牌列
       $leftJson='[{"tr":[{"dataType":0,"value":"片区","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"名称","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"产品类型","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品品牌","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品型号","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品规格","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""}]}]';
       $leftTemp=json_decode($leftJson,true);
       //左边数据
/*        $sql="select  DISTINCT lx,cpxh,cpgg
        from smcpkcb GROUP BY lx,cpxh,cpgg,date,dept ORDER BY  lx,cpxh,cpgg limit 0,28";
       $result=M()->query($sql); */
       if($version=='20170801'){
           $result=unserialize('a:40:{i:0;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";}i:1;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";}i:2;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";}i:3;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";}i:4;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";}i:5;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";}i:6;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";}i:7;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";}i:8;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:9;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"科凡达";s:4:"cpgg";s:6:"小机";}i:10;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:11;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:12;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:13;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:14;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:15;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:16;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:17;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:18;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:19;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:20;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:21;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:22;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"新和特";s:4:"cpxh";s:9:"新世界";s:4:"cpgg";s:6:"小机";}i:23;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";}i:24;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";}i:25;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";}i:26;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";}i:27;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";}i:28;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"天宫一号-D";s:4:"cpgg";s:6:"小机";}i:29;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星云-D";s:4:"cpgg";s:6:"小机";}i:30;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";}i:31;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"塑壳";s:4:"cpgg";s:6:"塑壳";}i:32;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"铁壳";s:4:"cpgg";s:6:"铁壳";}i:33;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:23:"其他地面波(机子)";s:4:"cpgg";s:23:"其他地面波(机子)";}i:34;a:4:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";}i:35;a:4:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";}i:36;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";}i:37;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";}i:38;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";}i:39;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";}}');
       }
       else{
            $result=unserialize('a:46:{i:0;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";}i:1;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";}i:2;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";}i:3;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";}i:4;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";}i:5;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";}i:6;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";}i:7;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";}i:8;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:9;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:10;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:11;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:12;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:13;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:14;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:15;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:16;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:17;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:18;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:19;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:20;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:21;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:22;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:23;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:24;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:25;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:26;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";}i:27;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";}i:28;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";}i:29;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";}i:30;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";}i:31;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";}i:32;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"200塑壳";}i:33;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"225铁壳";}i:34;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"188铁壳";}i:35;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:7:"DTMB+S2";s:4:"cpxh";s:7:"DTMB+S2";s:4:"cpgg";s:9:"200塑壳";}i:36;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"高清";s:4:"cpxh";s:6:"高清";s:4:"cpgg";s:9:"225铁壳";}i:37;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:10:"高清+OTT";s:4:"cpxh";s:10:"高清+OTT";s:4:"cpgg";s:9:"225铁壳";}i:38;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"其他";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:15:"其他地面波";}i:39;a:4:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";}i:40;a:4:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";}i:41;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";}i:42;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";}i:43;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";}i:44;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";}i:45;a:4:{s:2:"lx";s:12:"电控产品";s:5:"brand";s:12:"电控产品";s:4:"cpxh";s:12:"电控产品";s:4:"cpgg";s:12:"电控产品";}}');
       }
       	
       //每一页的部门（默认4个）
       //左边标题产品数据
       foreach ($result as $key=>$value){
           $leftTemp[$key+4]['tr'][]['value']=$value['lx'];
           $leftTemp[$key + 4]['tr'][]['value'] = empty($value['brand'])?$value['cpgg']:$value['brand'];
           $leftTemp[$key+4]['tr'][]['value']=$value['cpxh'];
           $leftTemp[$key+4]['tr'][]['value']=$value['cpgg'];
       }
      // echo json_encode($leftTemp);
       //左边部分
       $resposeData['leftTitle']=$leftTemp;
        //模板上标题部分
        $tempTitleJ='[{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":"12","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":"12","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"有效","rowspan":1,"colspan":"8","product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"外购收入","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"销售量支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"换货支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"暂存商品收入/支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"有效结存","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"换货收回","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效结存","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""}]}]';
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
                //查询非结存数据
                $sql = "select lx as lx1,brand as brand1,cpgg as cpgg1,cpxh as cpxh1,sum(dbsr) as dbsr ,sum(wgsr) as wgsr ,sum(xszc) as xszc ,
                        sum(dbzc) as dbzc,sum(hhzc) as hhzc,sum(zcspsrzc) as zcspsrzc,sum(qtzc) as qtzc ,
                        0 as yxjc,
                        sum(hhsh) as hhsh,sum(wxdbzc) as wxdbzc,sum(wxqtzc) as wxqtzc,0 as wxjc
                        from new_smcpkcb where dept='$dept_id' and date between '$s_date' and '$e_date'
                        GROUP BY lx,brand,cpgg,cpxh ORDER BY px";
                $tempData = M()->query($sql);
                
			//如果没有录入数据全部初始化为0
            if(empty($tempData)){
                 if($version=='20170801'){
                     //产品加入品牌后的序列化数据
                     $tempData=unserialize('a:40:{i:0;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:1;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:2;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:3;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:4;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:5;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:6;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:7;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:8;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:9;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"科凡达";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:10;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:11;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:12;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:13;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"梦幻";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:14;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"星空";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:15;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"星光";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:16;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:9:"创新号";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:17;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:18;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"星火";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:19;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:20;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:21;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:22;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"新和特";s:5:"cpxh1";s:9:"新世界";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:23;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:24;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:25;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:11:"海旋风-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:26;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:8:"星光-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:27;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:14:"科技先锋-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:28;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:14:"天宫一号-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:29;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:8:"星云-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:30;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:15:"其他定做机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:31;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:6:"塑壳";s:5:"cpgg1";s:6:"塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:32;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:6:"铁壳";s:5:"cpgg1";s:6:"铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:33;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:23:"其他地面波(机子)";s:5:"cpgg1";s:23:"其他地面波(机子)";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:34;a:16:{s:3:"lx1";s:9:"智能卡";s:6:"brand1";s:9:"智能卡";s:5:"cpxh1";s:9:"智能卡";s:5:"cpgg1";s:9:"智能卡";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:35;a:16:{s:3:"lx1";s:9:"高频头";s:6:"brand1";s:9:"高频头";s:5:"cpxh1";s:9:"高频头";s:5:"cpgg1";s:9:"高频头";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:36;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.35天线";s:5:"cpgg1";s:10:"0.35天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:37;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.45天线";s:5:"cpgg1";s:10:"0.45天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:38;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"自产天线";s:5:"cpgg1";s:12:"自产天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:39;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"外购天线";s:5:"cpgg1";s:12:"外购天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}}');
                 }
                 else{
                    $tempData=unserialize('a:46:{i:0;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:1;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:2;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:3;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:4;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:5;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:6;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:7;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:8;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:9;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:10;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:11;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:12;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"梦幻";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:13;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"星空";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:14;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"星光";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:15;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:9:"创新号";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:16;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"星火";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:17;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:18;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:19;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:20;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:21;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"梦幻";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:22;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"星空";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:23;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"星光";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:24;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:9:"创新号";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:25;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"星火";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:26;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:27;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:28;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:11:"海旋风-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:29;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:8:"星光-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:30;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:14:"科技先锋-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:31;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:15:"其他定做机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:32;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:4:"DTMB";s:5:"cpgg1";s:9:"200塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:33;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:4:"DTMB";s:5:"cpgg1";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:34;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:4:"DTMB";s:5:"cpgg1";s:9:"188铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:35;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:7:"DTMB+S2";s:5:"cpxh1";s:7:"DTMB+S2";s:5:"cpgg1";s:9:"200塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:36;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:6:"高清";s:5:"cpxh1";s:6:"高清";s:5:"cpgg1";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:37;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:10:"高清+OTT";s:5:"cpxh1";s:10:"高清+OTT";s:5:"cpgg1";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:38;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:15:"其他地面波";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:39;a:16:{s:3:"lx1";s:9:"智能卡";s:6:"brand1";s:9:"智能卡";s:5:"cpxh1";s:9:"智能卡";s:5:"cpgg1";s:9:"智能卡";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:40;a:16:{s:3:"lx1";s:9:"高频头";s:6:"brand1";s:9:"高频头";s:5:"cpxh1";s:9:"高频头";s:5:"cpgg1";s:9:"高频头";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:41;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.35天线";s:5:"cpgg1";s:10:"0.35天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:42;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.45天线";s:5:"cpgg1";s:10:"0.45天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:43;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"自产天线";s:5:"cpgg1";s:12:"自产天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:44;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"外购天线";s:5:"cpgg1";s:12:"外购天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:45;a:16:{s:3:"lx1";s:12:"电控产品";s:6:"brand1";s:12:"电控产品";s:5:"cpxh1";s:12:"电控产品";s:5:"cpgg1";s:12:"电控产品";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}}');
                 }
             }
            //查询结存
            $sql_jc = "select lx as lx1,brand as brand1,cpgg as cpgg1,cpxh as cpxh1, sum(yxjc)+(select IFNULL((select yxjc from new_smcpkcbqc where
            lx=lx1 and brand=brand1 and xh=cpxh1 and cpgg=cpgg1 and dept='$dept_id' and date=DATE_FORMAT('$s_date','%Y-%m')),0) ) as yxjc,sum(wxjc)+(
            select IFNULL((select wxjc from new_smcpkcbqc where
            lx=lx1 and brand=brand1 and xh=cpxh1 and cpgg=cpgg1 and dept='$dept_id' and date=DATE_FORMAT('$s_date','%Y-%m')),0) ) as wxjc
            from new_smcpkcb where dept='$dept_id' and date between '$yuechu' and '$e_date'
            GROUP BY lx,brand,cpgg,cpxh ORDER BY px";
            $jc = M()->query($sql_jc);
            foreach ($tempData as $kk1 => $vv1)
            {
                foreach ($vv1 as $kk11 =>$vv11)
                {
                    if ($kk11 =='yxjc' || $kk11 =='wxjc')
                    {
						if($jc[$kk1][$kk11] =='')
						{
							$xx =0;
						}else
						{
							$xx = $jc[$kk1][$kk11];
						}
                        $tempData[$kk1][$kk11] = $xx;
                    }
                }
            }

            //产品总类（每一页的行数）每一行具体的数据
            foreach ($tempData as $rowKey=>$row){
              $resposeData['data'][$key]['content'][$rowKey]['tr']=array();
              $temp=array();
               $temp[]['value']=$row['dbsr'];
               $temp[]['value']=$row['wgsr'];
               $temp[]['value']=$row['xszc'];
               $temp[]['value']=$row['dbzc'];
               $temp[]['value']=$row['hhzc'];
               $temp[]['value']=$row['zcspsrzc'];
               $temp[]['value']=$row['qtzc'];
               $temp[]['value']=$row['yxjc'];
               $temp[]['value']=$row['hhsh'];
               $temp[]['value']=$row['wxdbzc'];
               $temp[]['value']=$row['wxqtzc'];
               $temp[]['value']=$row['wxjc'];
               
               $resposeData['data'][$key]['content'][$rowKey]['tr']=$temp;
            }
            
        }
        
        //返回需要打印的excel数组，后台打印请求
        if($flag){
            return $resposeData;
        }
        //页面渲染请求
        else
            echo json_encode($resposeData);
    }

    //打印excel
    public function printExcel($token='',$s_date='',$e_date=''){
        set_time_limit(90);
        header("Access-Control-Allow-Origin: *");
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
         
        $objPHPExcel = new \PHPExcel();
        //待打印的数据
        $printData=$this->search($token,$s_date,$e_date,1,110,true);
        //设置文件名
        $objPHPExcel->getProperties()->setTitle("数码产品库存报表统计表");
        //左边标题部分
        $leftTitle=$printData['leftTitle'];
        /* //打印左边标题
         $objPHPExcel->setActiveSheetIndex(0);
         foreach ($leftTitle as $left_key=>$left_value){
         $value=$left_value['tr'];
         $pos=$left_key+1;
         //左标题前三行部分
         if($left_key<3){
         $objPHPExcel->getActiveSheet()->mergeCells("A$pos:C$pos");
         $objPHPExcel->getActiveSheet()->setCellValue("A$pos",$value[0]['value']);
         }
         //左边数据部分
         $objPHPExcel->getActiveSheet()->setCellValue("A$pos",$value[0]['value']);
         $objPHPExcel->getActiveSheet()->setCellValue("B$pos",$value[1]['value']);
         $objPHPExcel->getActiveSheet()->setCellValue("C$pos",$value[2]['value']);
         }
         */
        //每个部门的数据部分
        $data=$printData['data'];
        //循环打印每一个部门的数据
        foreach ($data as $key_dept=>$value_dept){
            //当前部门的起始列数字索引
            $col_offset_n=$key_dept*12+1;
            //打印topTitle
            $topTitle=$value_dept['topTitle'];
            foreach ($topTitle as $k_top=>$v_top){
                if($k_top==0){
                    //合并列列号
                    $des=$col_offset_n+11;
                    $des=$this->ToNumberSystem26($des);
                    //列的字母索引
                    $col_offset=$this->ToNumberSystem26($col_offset_n);
                    $objPHPExcel->getActiveSheet()->setCellValue($col_offset."1",$v_top['tr'][0]['value']);
                    //合并单元格
                    $from=$col_offset."1";
                    $des.="1";
                    $objPHPExcel->getActiveSheet()->mergeCells("$from:$des");
                     
                }elseif($k_top==1){
                    $des=$col_offset_n+11;
                    $des=$this->ToNumberSystem26($des);
                    $from=$col_offset."2";
                    $des.="2";
                    $objPHPExcel->getActiveSheet()->setCellValue($col_offset."2",$v_top['tr'][0]['value']);
                    $objPHPExcel->getActiveSheet()->mergeCells("$from:$des");
                }elseif($k_top==2){
                    foreach ($v_top['tr'] as $k=>$v){
                        if($k==0){
                            $col_offset=$col_offset_n;
                            $des=$col_offset+7;
                            $col_offset=$this->ToNumberSystem26($col_offset);
                             
                        }
                        else{
                            $col_offset=$col_offset_n+8;
                            $des=$col_offset+3;
                            $col_offset=$this->ToNumberSystem26($col_offset);
                             
                        }
                        $from=$col_offset."3";
    
                        $des=$this->ToNumberSystem26($des)."3";
                        $objPHPExcel->getActiveSheet()->setCellValue($col_offset."3",$v['value']);
                         
                        $objPHPExcel->getActiveSheet()->mergeCells("$from:$des");
                    }
                }elseif($k_top==3){
                    foreach ($v_top['tr'] as $k=>$v){
                        $col_offset=$col_offset_n+$k;
                        $col_offset=$this->ToNumberSystem26($col_offset);
                        $objPHPExcel->getActiveSheet()->setCellValue($col_offset."4",$v['value']);
                    }
                }
            }
            //打印数据部分
            $data=$value_dept['content'];
            foreach ($data as $k_con=>$v_con){
                $row_offset=$k_con+5;
                foreach ($v_con['tr'] as $key=>$value){
                    $col_offset=$col_offset_n+$key;
                    $col_offset=$this->ToNumberSystem26($col_offset);
                    $objPHPExcel->getActiveSheet()->setCellValue("$col_offset$row_offset",$value['value']);
                }
            }
    
             
        }
    
        $objPHPExcel->getActiveSheet()->getStyle('A1:AXT3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    
        //return ;
        //设置宽度
        // $objPHPExcel->getActiveSheet()->getColumnDimension('A1:C32')->setWidth(15);
    
        /*  //设置边框
         $objPHPExcel->getActiveSheet()->getStyle('A:C')->getBorders()->getTop()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
         $objPHPExcel->getActiveSheet()->getStyle('A:C')->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
         $objPHPExcel->getActiveSheet()->getStyle('A:C')->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
         $objPHPExcel->getActiveSheet()->getStyle('A:C')->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);  */
        // 直接输出到浏览器
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //$objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="数码产品库存报表统计表.xlsx"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }    
    

    /**
     * 输出csv格式
     * 
     * @param string $token            
     * @param string $s_date            
     * @param string $e_date            
     */
    public function printCsv($token = '', $s_date = '', $e_date = '', $pageSize = 21)
    { 
        header("Access-Control-Allow-Origin: *");
        if(strtotime($s_date)<strtotime("2017-08-01")&&strtotime($s_date)>=strtotime("2017-08-01")){
            $this->response(retmsg(-1,null,'赞不支持跨版本数据查询！'),'json');
        }
        //根据日期查询对应版本的数据
        if(strtotime($s_date)>=strtotime("2017-08-01"))
            $version="20170801";
        set_time_limit(200);
        
        // 验证token
        $userinfo = checktoken($token);
         if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        } 
		$yuechu = date('Ym01',strtotime($e_date));
        // 部门idsfdds
        $dept_id = $userinfo['dept_id'];
        /* $dept_id=25;
        $pid=100; */
        $dept = $dept_id;
        // 上级部门id
        $pid=$userinfo['pid'];
        
        if ($s_date == '') 
        {
            $s_date = date("Y-m-d");
        }
        if ($e_date == '')
        {
            $e_date = date("Y-m-d");
        }
        $page = 1;
        // 响应数据
        $resposeData = array();
        $resposeData['page'] = $page;
        
        // 分权限查询部门数据
        $sql_total = "select count(*) as total from xsrb_department where ";
        $sql_data = "select t1.dname as dname,t1.id as id,t2.dname as pname from xsrb_department t1,xsrb_department t2
        where t1.pid=t2.id ";
        // 如果为总部查询所有部门
        if ($pid == 0) 
        {
            $sql_total .= " id!=1 and pid!=1";
            $sql_data .= " and t1.id!=1 and t1.pid!=1 ORDER BY t1.pid DESC,t1.qt1 ";
        }        // 如果为片区则查出该片区下的所有部门
        elseif ($pid == 1) 
        {
            $sql_total .= " pid='$dept_id' ";
            $sql_data .= " and t1.pid='$dept_id' ORDER BY t1.pid DESC,t1.qt1 ";
        }         // 具体某个部门的数据
        else 
        {
            $sql_total .= " id='$dept_id' ";
            $sql_data .= " and t1.id='$dept_id'  ";
        }
        $total = M()->query($sql_total);
        $total = $total[0]['total'];
        $cntpage = ceil($total / 21);
        for ($n = 1; $n <= $cntpage; $n ++)
        {
            if ($total < $pageSize)
                $total = 1;
            elseif ($total % $pageSize == 0)
                $total = $total / $pageSize;
            else
                $total = $total / $pageSize + 1;
            $resposeData['total'] = (int) $total;
            $resposeData['page'] = $n;
            //左边模板 2017-2-16添加产品品牌列
           $leftJson='[{"tr":[{"dataType":0,"value":"片区","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"部门","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"名称","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":"","brand":""}]},{"tr":[{"dataType":0,"value":"产品类型","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品品牌","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品型号","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""},{"dataType":0,"value":"产品规格","rowspan":1,"colspan":"1","product_type":"","product":"","type":"","type_detail":"","brand":""}]}]';
           $leftTemp=json_decode($leftJson,true);
           //左边数据
    /*        $sql="select  DISTINCT lx,cpxh,cpgg
            from smcpkcb GROUP BY lx,cpxh,cpgg,date,dept ORDER BY  lx,cpxh,cpgg limit 0,28";
           $result=M()->query($sql); */
           if($version=='20170801'){
               $result=unserialize('a:40:{i:0;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";}i:1;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";}i:2;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";}i:3;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";}i:4;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";}i:5;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";}i:6;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";}i:7;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";}i:8;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:9;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"科凡达";s:4:"cpgg";s:6:"小机";}i:10;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:11;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:12;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:13;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:14;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:15;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:16;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:17;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:18;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:19;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:20;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:21;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:22;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"新和特";s:4:"cpxh";s:9:"新世界";s:4:"cpgg";s:6:"小机";}i:23;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";}i:24;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";}i:25;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";}i:26;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";}i:27;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";}i:28;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"天宫一号-D";s:4:"cpgg";s:6:"小机";}i:29;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星云-D";s:4:"cpgg";s:6:"小机";}i:30;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";}i:31;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"塑壳";s:4:"cpgg";s:6:"塑壳";}i:32;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"铁壳";s:4:"cpgg";s:6:"铁壳";}i:33;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:23:"其他地面波(机子)";s:4:"cpgg";s:23:"其他地面波(机子)";}i:34;a:4:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";}i:35;a:4:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";}i:36;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";}i:37;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";}i:38;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";}i:39;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";}}');
           }
           else{
               $result=unserialize('a:46:{i:0;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";}i:1;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";}i:2;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";}i:3;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";}i:4;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";}i:5;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";}i:6;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";}i:7;a:4:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";}i:8;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:9;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:10;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:11;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:12;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:13;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:14;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:15;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:16;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:17;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";}i:18;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";}i:19;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";}i:20;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（标配）";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";}i:21;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";}i:22;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";}i:23;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";}i:24;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";}i:25;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:24:"处处通达（减配）";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";}i:26;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";}i:27;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";}i:28;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";}i:29;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";}i:30;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";}i:31;a:4:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";}i:32;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"200塑壳";}i:33;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"225铁壳";}i:34;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"188铁壳";}i:35;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:7:"DTMB+S2";s:4:"cpxh";s:7:"DTMB+S2";s:4:"cpgg";s:9:"200塑壳";}i:36;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"高清";s:4:"cpxh";s:6:"高清";s:4:"cpgg";s:9:"225铁壳";}i:37;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:10:"高清+OTT";s:4:"cpxh";s:10:"高清+OTT";s:4:"cpgg";s:9:"225铁壳";}i:38;a:4:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"其他";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:15:"其他地面波";}i:39;a:4:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";}i:40;a:4:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";}i:41;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";}i:42;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";}i:43;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";}i:44;a:4:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";}i:45;a:4:{s:2:"lx";s:12:"电控产品";s:5:"brand";s:12:"电控产品";s:4:"cpxh";s:12:"电控产品";s:4:"cpgg";s:12:"电控产品";}}');
           }
            // 每一页的部门（默认4个）
            // 左边标题产品数据
            foreach ($result as $key => $value)
            {
                $leftTemp[$key + 4]['tr'][]['value'] = $value['lx'];
                $leftTemp[$key + 4]['tr'][]['value'] = empty($value['brand'])?$value['cpgg']:$value['brand'];
                $leftTemp[$key + 4]['tr'][]['value'] = $value['cpxh'];
                $leftTemp[$key + 4]['tr'][]['value'] = $value['cpgg'];
            }
            // 左边部分
            $resposeData['leftTitle'] = $leftTemp;
            // 模板上标题部分
            $tempTitleJ = '[{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":"12","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"","rowspan":1,"colspan":"12","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"有效","rowspan":1,"colspan":"8","product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效","rowspan":1,"colspan":"4","product_type":"","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"外购收入","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"销售量支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"换货支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"暂存商品收入/支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"有效结存","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"换货收回","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"其他支出","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效结存","rowspan":1,"colspan":1,"product_type":"","product":"","type":"","type_detail":""}]}]';
            $tempTitleA = json_decode($tempTitleJ, true);
            $from = ($n - 1) * $pageSize;
            // 正式数据部分
            $sql_one = $sql_data . " limit " . ($n - 1) * $pageSize . ",21";
            $result = M()->query($sql_one);
            foreach ($result as $key => $value)
            {
                $tempTitleA[1]['tr'][0]['value'] = $value['dname'];
                $tempTitleA[0]['tr'][0]['value'] = $value['pname'];
                // 表头标题
                $resposeData['data'][$key]['topTitle'] = array();
                $resposeData['data'][$key]['topTitle'] = $tempTitleA;
                $dept_id = $value['id'];
                $sql = "select lx as lx1,brand as brand1,cpgg as cpgg1,cpxh as cpxh1,sum(dbsr) as dbsr ,sum(wgsr) as wgsr ,sum(xszc) as xszc ,
                        sum(dbzc) as dbzc,sum(hhzc) as hhzc,sum(zcspsrzc) as zcspsrzc,sum(qtzc) as qtzc ,
                        0 as yxjc,
                        sum(hhsh) as hhsh,sum(wxdbzc) as wxdbzc,sum(wxqtzc) as wxqtzc,0 as wxjc
                        from new_smcpkcb where dept='$dept_id' and date between '$s_date' and '$e_date'
                        GROUP BY lx,brand,cpgg,cpxh ORDER BY  px";
                $tempData = M()->query($sql);
                
                //如果没有录入数据全部初始化为0
                if(empty($tempData)){
                     if($version=='20170801'){
                         //产品加入品牌后的序列化数据
                         $tempData=unserialize('a:40:{i:0;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:1;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:2;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:3;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:4;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:5;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:6;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:7;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:8;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:9;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"科凡达";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:10;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:11;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:12;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:13;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"梦幻";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:14;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"星空";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:15;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"星光";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:16;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:9:"创新号";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:17;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:18;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"星火";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:19;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:20;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:21;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:12:"处处通达";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:22;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"新和特";s:5:"cpxh1";s:9:"新世界";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:23;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:24;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:25;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:11:"海旋风-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:26;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:8:"星光-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:27;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:14:"科技先锋-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:28;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:14:"天宫一号-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:29;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:8:"星云-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:30;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:15:"其他定做机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:31;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:6:"塑壳";s:5:"cpgg1";s:6:"塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:32;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:6:"铁壳";s:5:"cpgg1";s:6:"铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:33;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:23:"其他地面波(机子)";s:5:"cpgg1";s:23:"其他地面波(机子)";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:34;a:16:{s:3:"lx1";s:9:"智能卡";s:6:"brand1";s:9:"智能卡";s:5:"cpxh1";s:9:"智能卡";s:5:"cpgg1";s:9:"智能卡";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:35;a:16:{s:3:"lx1";s:9:"高频头";s:6:"brand1";s:9:"高频头";s:5:"cpxh1";s:9:"高频头";s:5:"cpgg1";s:9:"高频头";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:36;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.35天线";s:5:"cpgg1";s:10:"0.35天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:37;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.45天线";s:5:"cpgg1";s:10:"0.45天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:38;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"自产天线";s:5:"cpgg1";s:12:"自产天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:39;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"外购天线";s:5:"cpgg1";s:12:"外购天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}}');
                     }
                     else{
                         $tempData=unserialize('a:46:{i:0;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:1;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁把";s:5:"cpxh1";s:6:"锁把";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:2;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:3;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:9:"智能锁";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:4;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:5;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁体";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:6;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:7;a:16:{s:3:"lx1";s:6:"门配";s:6:"brand1";s:6:"锁芯";s:5:"cpxh1";s:6:"锁芯";s:5:"cpgg1";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:8;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:9;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:10;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:11;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"科海";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:12;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"梦幻";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:13;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"星空";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:14;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"星光";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:15;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:9:"创新号";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:16;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"星火";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:17;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"海星";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:18;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:6:"金品";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:19;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:9:"海旋风";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:20;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(标配)";s:5:"cpxh1";s:9:"海霸王";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:21;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"梦幻";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:22;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"星空";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:23;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"星光";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:24;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:9:"创新号";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:25;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:20:"处处通达(减配)";s:5:"cpxh1";s:6:"星火";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:26;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:27;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:15:"其他定位机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:28;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:11:"海旋风-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:29;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:8:"星光-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:30;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:14:"科技先锋-D";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:31;a:16:{s:3:"lx1";s:9:"三代机";s:6:"brand1";s:9:"定做机";s:5:"cpxh1";s:15:"其他定做机";s:5:"cpgg1";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:32;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:4:"DTMB";s:5:"cpgg1";s:9:"200塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:33;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:4:"DTMB";s:5:"cpgg1";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:34;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:4:"DTMB";s:5:"cpxh1";s:4:"DTMB";s:5:"cpgg1";s:9:"188铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:35;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:7:"DTMB+S2";s:5:"cpxh1";s:7:"DTMB+S2";s:5:"cpgg1";s:9:"200塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:36;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:6:"高清";s:5:"cpxh1";s:6:"高清";s:5:"cpgg1";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:37;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:10:"高清+OTT";s:5:"cpxh1";s:10:"高清+OTT";s:5:"cpgg1";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:38;a:16:{s:3:"lx1";s:9:"地面波";s:6:"brand1";s:6:"其他";s:5:"cpxh1";s:6:"其他";s:5:"cpgg1";s:15:"其他地面波";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:39;a:16:{s:3:"lx1";s:9:"智能卡";s:6:"brand1";s:9:"智能卡";s:5:"cpxh1";s:9:"智能卡";s:5:"cpgg1";s:9:"智能卡";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:40;a:16:{s:3:"lx1";s:9:"高频头";s:6:"brand1";s:9:"高频头";s:5:"cpxh1";s:9:"高频头";s:5:"cpgg1";s:9:"高频头";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:41;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.35天线";s:5:"cpgg1";s:10:"0.35天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:42;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:10:"0.45天线";s:5:"cpgg1";s:10:"0.45天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:43;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"自产天线";s:5:"cpgg1";s:12:"自产天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:44;a:16:{s:3:"lx1";s:6:"天线";s:6:"brand1";s:6:"天线";s:5:"cpxh1";s:12:"外购天线";s:5:"cpgg1";s:12:"外购天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:45;a:16:{s:3:"lx1";s:12:"电控产品";s:6:"brand1";s:12:"电控产品";s:5:"cpxh1";s:12:"电控产品";s:5:"cpgg1";s:12:"电控产品";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}}');
                     }
                 }
                //查询结存
                $sql_jc = "select lx as lx1,brand as brand1,cpgg as cpgg1,cpxh as cpxh1, sum(yxjc)+(select IFNULL((select yxjc from new_smcpkcbqc where
                lx=lx1 and brand=brand1 and xh=cpxh1 and cpgg=cpgg1 and dept='$dept_id' and date=DATE_FORMAT('$s_date','%Y-%m')),0) ) as yxjc,sum(wxjc)+(
                select IFNULL((select wxjc from new_smcpkcbqc where
                lx=lx1 and brand=brand1 and xh=cpxh1 and cpgg=cpgg1 and dept='$dept_id' and date=DATE_FORMAT('$s_date','%Y-%m')),0) ) as wxjc
                from new_smcpkcb where dept='$dept_id' and date between '$yuechu' and '$e_date'
                GROUP BY lx,brand,cpgg,cpxh ORDER BY  px";
                $jc = M()->query($sql_jc);
                foreach ($tempData as $kk1 => $vv1)
                {
                    foreach ($vv1 as $kk11 =>$vv11)
                    {
                        if ($kk11 =='yxjc' || $kk11 =='wxjc')
                        {
							if($jc[$kk1][$kk11] =='')
							{
								$xx =0;
							}else
							{
								$xx = $jc[$kk1][$kk11];
							}							
                            $tempData[$kk1][$kk11] = $xx;
                        }
                    }
                }
                // 产品总类（每一页的行数）每一行具体的数据
                foreach ($tempData as $rowKey => $row) 
                {
                    $resposeData['data'][$key]['content'][$rowKey]['tr'] = array();
                    $temp = array();
                    $temp[]['value'] = $row['dbsr'] ;
                    $temp[]['value'] = $row['wgsr'] ;
                    $temp[]['value'] = $row['xszc'] ;
                    $temp[]['value'] = $row['dbzc'];
                    $temp[]['value'] = $row['hhzc'] ;
                    $temp[]['value'] = $row['zcspsrzc'] ;
                    $temp[]['value'] = $row['qtzc'] ;
                    $temp[]['value'] = $row['yxjc'] ;
                    $temp[]['value'] = $row['hhsh'] ;
                    $temp[]['value'] = $row['wxdbzc'] ;
                    $temp[]['value'] = $row['wxqtzc'] ;
                    $temp[]['value'] = $row['wxjc'] ;
                    
                    $resposeData['data'][$key]['content'][$rowKey]['tr'] = $temp;
                }
            }
            $printData = $resposeData;
            $printData = $printData['data'];
            $str = "";
            if($version=='20170801'){
                $cpgg = unserialize('a:40:{i:0;s:18:"锁把单卖销售";i:1;s:18:"锁把成套销售";i:2;s:21:"智能锁单卖销售";i:3;s:21:"智能锁成套销售";i:4;s:18:"其他单卖销售";i:5;s:18:"其他成套销售";i:6;s:18:"锁芯单卖销售";i:7;s:18:"锁芯成套销售";i:8;s:15:"海旋风小机";i:9;s:15:"科凡达小机";i:10;s:12:"海星大机";i:11;s:12:"金品大机";i:12;s:15:"海霸王小机";i:13;s:12:"梦幻大机";i:14;s:12:"星空小机";i:15;s:12:"星光小机";i:16;s:15:"创新号小机";i:17;s:12:"海星大机";i:18;s:12:"星火小机";i:19;s:12:"金品大机";i:20;s:15:"海旋风小机";i:21;s:15:"海霸王小机";i:22;s:15:"新世界小机";i:23;s:21:"其他定位机大机";i:24;s:21:"其他定位机小机";i:25;s:17:"海旋风-D小机";i:26;s:14:"星光-D小机";i:27;s:20:"科技先锋-D小机";i:28;s:20:"天宫一号-D小机";i:29;s:14:"星云-D小机";i:30;s:21:"其他定做机小机";i:31;s:12:"塑壳塑壳";i:32;s:12:"铁壳铁壳";i:33;s:46:"其他地面波(机子)其他地面波(机子)";i:34;s:18:"智能卡智能卡";i:35;s:18:"高频头高频头";i:36;s:20:"0.35天线0.35天线";i:37;s:20:"0.45天线0.45天线";i:38;s:24:"自产天线自产天线";i:39;s:24:"外购天线外购天线";}');
            }
            else{
                $cpgg = unserialize('a:46:{i:0;s:18:"锁把单卖销售";i:1;s:18:"锁把成套销售";i:2;s:21:"智能锁单卖销售";i:3;s:21:"智能锁成套销售";i:4;s:18:"其他单卖销售";i:5;s:18:"其他成套销售";i:6;s:18:"锁芯单卖销售";i:7;s:18:"锁芯成套销售";i:8;s:12:"海星大机";i:9;s:12:"金品大机";i:10;s:15:"海旋风小机";i:11;s:15:"海霸王小机";i:12;s:12:"梦幻大机";i:13;s:12:"星空小机";i:14;s:12:"星光小机";i:15;s:15:"创新号小机";i:16;s:12:"星火小机";i:17;s:12:"海星大机";i:18;s:12:"金品大机";i:19;s:15:"海旋风小机";i:20;s:15:"海霸王小机";i:21;s:12:"梦幻大机";i:22;s:12:"星空小机";i:23;s:12:"星光小机";i:24;s:15:"创新号小机";i:25;s:12:"星火小机";i:26;s:21:"其他定位机大机";i:27;s:21:"其他定位机小机";i:28;s:17:"海旋风-D小机";i:29;s:14:"星光-D小机";i:30;s:20:"科技先锋-D小机";i:31;s:21:"其他定做机小机";i:32;s:13:"DTMB200塑壳";i:33;s:13:"DTMB225铁壳";i:34;s:13:"DTMB188铁壳";i:35;s:16:"DTMB+S2200塑壳";i:36;s:15:"高清225铁壳";i:37;s:19:"高清+OTT225铁壳";i:38;s:15:"其他地面波";i:39;s:9:"智能卡";i:40;s:9:"高频头";i:41;s:10:"0.35天线";i:42;s:10:"0.45天线";i:43;s:12:"自产天线";i:44;s:12:"外购天线";i:45;s:12:"电控产品";}');
            }
            // 循环生成50行csv数据
            for ($i = 0; $i < 50; $i ++)
            {
                // 前四行的标题部分
                if ($i < 4)
                {
                    $str.=',';
                    // 循环遍历每个部门数据的标题部分
                    foreach ($printData as $temp) 
                    {
                        $topTitle = $temp['topTitle'][$i]['tr'];
                        // 标题第二行特殊显示
                        if ($i == 2)
                        {
                            for ($j = 0; $j < 12; $j ++)
                            {
                                $value = $bb;
                                if ($j == 0)
                                    $value = $topTitle[$j]['value'];
                                if ($j == 1)
                                    $temp = $topTitle[$j]['value'];
                                if ($j == 8)
                                    $value = $temp;
                                if ($value !='')
                                {
                                    $bb = $value;                               
                                }

                                $str .= "$value,";
                            }
                        } else
                        {
                            for ($j = 0; $j < 12; $j ++) {
                                $value = $topTitle[$j]['value'];

                                $str .= "$value,";
                            }
                        }
                    }
                } else 
                {
                    // 循环遍历每个部门数据的数据部分
                    $str.= $cpgg[$i-4].",";
                    foreach ($printData as $temp) {
                        $content = $temp['content'][$i - 4]['tr'];
                        for ($j = 0; $j < 12; $j ++) {
                            $value = $content[$j]['value'];

                            $str .= "$value,";
                        }
                    }
                }
                $str .= "\n";
            }
            $str = iconv("utf-8", "gbk", $str);
            $str = substr($str, 0, - 1); // 去掉最后一个逗号
            
            $filepath = str_replace('\\', '/', realpath(__DIR__ . '/../../../')) . '/excel/' . $n . "-NEW_SMCPKCBTJ-$s_date - $e_date .csv";
            $fp = fopen($filepath, "a"); // 生成csv文件
            fwrite($fp, $str);
            fclose($fp);
            unset($printData);unset($resposeData);      //清除变量值
            $fileNameArr[] = $filepath;
        }
        $filename =str_replace('\\', '/', realpath(__DIR__ . '/../../../')) . '/excel/'. $dept.'-NEW_SMCPKCBTJ-'.$s_date .'-'. $e_date .".zip"; // 最终生成的文件名（含路径）        
        $zip = new \ZipArchive (); // 使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        if ($zip->open ( $filename, \ZIPARCHIVE::CREATE ) !== TRUE) {
            exit ( '无法打开文件，或者文件创建失败' );
        }
        //$fileNameArr 就是一个存储文件路径的数组 比如 array('/a/1.jpg,/a/2.jpg....');
        foreach ( $fileNameArr as $val ) {
            $zip->addFile ( $val, basename ( $val ) ); // 第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
        }
        $zip->close (); // 关闭
        
        $fileNameArr[] = $filename;
        //下面是输出下载;
        header ( "Cache-Control: max-age=0" );
        header ( "Content-Description: File Transfer" );
        header ( 'Content-disposition: attachment; filename=' . basename ( $filename ) ); // 文件名
        header ( "Content-Type: application/zip" ); // zip格式的
        header ( "Content-Transfer-Encoding: binary" ); // 告诉浏览器，这是二进制文件
        header ( 'Content-Length: ' . filesize ( $filename ) ); // 告诉浏览器，文件大小
        @readfile ( $filename );//输出文件;
        foreach ($fileNameArr as $v)
        {
            if (file_exists($v) && is_readable($v))
            {
                unlink($v);       //删除下载在本地的csv文件
            }
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
   
    
}


?>