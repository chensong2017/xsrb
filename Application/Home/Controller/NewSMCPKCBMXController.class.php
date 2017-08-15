<?php
namespace Home\Controller;

require_once 'XSRBUtil.php';
use Think\Controller\RestController;
//require_once 'XSRBUtil1.php';
/**
 * 数码产品库存表明细
 * @author Administrator
 *
 */
define('WWW_PATH',str_replace('\\','/',realpath(__DIR__.'/../../../')));
//define('UPLOAD_URL',$_SERVER['SERVER_NAME']."/xsrb_local_test/upload/uploadfile1.php?xlspath=$savepath");
class NewSMCPKCBMXController extends RestController{
    /**
     * 查询指定的数码库存表明细
     * @param string $token
     * @param string $s_date
     * @param string $e_date
     * @param number $page
     * @param number $pageSize 每页显示的部门默认显示一个部门
     * @param $flag 打印excel标志
     * @param $dept 部门信息 后台定时调度上传excel
     * @return 如果$flag=true 返回待打印的数组否则返回json
     */
     public function search($token='',$date=TODAY,$page=1,$pageSize=1,$flag=false,$dept=""){
         set_time_limit(600);
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
            /* $dept_id=25;
            $qt1=1; */
        }elseif($dept!=""){
            //部门id
             $dept_id=$dept['dept_id'];
            //qt1判断是否是片区的标志
              $qt1=$dept['qt1'];
        }else 
           // return;
        {
            $dept_id=1;
            $qt1=1;
        }
        if($date==''){
            $date=date("Y-m-d");
        }
        //响应数据
        $resposeData=array();
        $resposeData['page']=$page;
        
        //分权限查询部门数据
        $sql_total="select count(*) as total from xsrb_department where ";
       $sql_data="select id,dname from xsrb_department  where  ";
        //如果为总部查询所有部门
        if($dept_id==1){
            $sql_total.=" qt1!=0 ";
            $sql_data.=" qt1!=0  ";
        }
        //如果为片区则查出该片区下的所有部门
        elseif($qt1==0){
            $sql_total.=" pid='$dept_id' ";
            $sql_data.=" pid='$dept_id' ";
        }
        //具体某个部门的数据
        else{
            $sql_total.=" id='$dept_id' ";
            $sql_data.=" id='$dept_id'  ";
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
       //表头部分
       $title=array(
           //第一行
           array("tds"=>array(array("value"=>"新能源数码产品库存明细表","colspan"=>16))),
           //第二行
           array("tds"=>array(array("value"=>"名称","colspan"=>4),array("value"=>"有效","colspan"=>8),
               array("value"=>"无效","colspan"=>4)
           )),
           //第三行 2017-2-13添加产品品牌
           array("tds"=>array(
               array("value"=>"产品类型","colspan"=>1),array("value"=>"产品品牌","colspan"=>1),
               array("value"=>"产品型号","colspan"=>1),array("value"=>"产品规格","colspan"=>1),
               array("value"=>"调拨收入","colspan"=>1),array("value"=>"外购收入","colspan"=>1),
               array("value"=>"销售支出","colspan"=>1),array("value"=>"调拨支出","colspan"=>1),
               array("value"=>"换货支出","colspan"=>1),array("value"=>"暂存商品收入/支出","colspan"=>1),
               array("value"=>"其他支出","colspan"=>1),array("value"=>"有效结存","colspan"=>1),
               array("value"=>"换货收回","colspan"=>1),array("value"=>"调拨支出","colspan"=>1),
               array("value"=>"其他支出","colspan"=>1),array("value"=>"无效结存","colspan"=>1)
           )),
           //第四行
           array(
               "tds"=>array(
                   array("value"=>"","colspan"=>16)
               )
           )
       );
        //正式数据部分
         $from=($page-1)*$pageSize;
        $sql_data.=" limit $from,$pageSize";
        $result=M()->query($sql_data);
        foreach ($result as $key=>$value){
            $dname=$value['dname'];
            //部门名称
            $title[3]['tds'][0]['value']=$dname;
            //标题部分
            $resposeData['dept'][$key]['title']=$title;
            $dept_id=$value['id'];
                $sql="select * from new_smcpkcb where dept='$dept_id' and date='$date'  
                    order by px";
            $tempData=M()->query($sql);
            //如果没有录入数据全部初始化为0,排序之后的数组序列化数据
            if(empty($tempData)){
               if($version=='20170801'){
                   $tempData=unserialize('a:40:{i:0;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:1;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:2;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:3;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:4;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:5;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:6;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:7;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:8;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:9;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"科凡达";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:10;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:11;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:12;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:13;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:14;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:15;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:16;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:17;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:18;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:19;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:20;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:21;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:12:"处处通达";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:22;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"新和特";s:4:"cpxh";s:9:"新世界";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:23;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:24;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:25;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:26;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:27;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:28;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"天宫一号-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:29;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星云-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:30;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:31;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"塑壳";s:4:"cpgg";s:6:"塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:32;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:6:"铁壳";s:4:"cpgg";s:6:"铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:33;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:23:"其他地面波(机子)";s:4:"cpgg";s:23:"其他地面波(机子)";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:34;a:16:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:35;a:16:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:36;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:37;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:38;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}i:39;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:4:"0.00";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:4:"0.00";}}');
               }else{
                   $tempData=unserialize('a:46:{i:0;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:1;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁把";s:4:"cpxh";s:6:"锁把";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:2;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:3;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:9:"智能锁";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:4;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:5;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁体";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:6;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"单卖销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:7;a:16:{s:2:"lx";s:6:"门配";s:5:"brand";s:6:"锁芯";s:4:"cpxh";s:6:"锁芯";s:4:"cpgg";s:12:"成套销售";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:8;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:9;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:10;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:11;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"科海";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:12;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:13;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:14;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:15;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:16;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:17;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:6:"海星";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:18;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:6:"金品";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:19;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:9:"海旋风";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:20;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(标配)";s:4:"cpxh";s:9:"海霸王";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:21;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(减配)";s:4:"cpxh";s:6:"梦幻";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:22;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(减配)";s:4:"cpxh";s:6:"星空";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:23;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(减配)";s:4:"cpxh";s:6:"星光";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:24;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(减配)";s:4:"cpxh";s:9:"创新号";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:25;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:20:"处处通达(减配)";s:4:"cpxh";s:6:"星火";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:26;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"大机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:27;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:6:"其他";s:4:"cpxh";s:15:"其他定位机";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:28;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:11:"海旋风-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:29;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:8:"星光-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:30;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:14:"科技先锋-D";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:31;a:16:{s:2:"lx";s:9:"三代机";s:5:"brand";s:9:"定做机";s:4:"cpxh";s:15:"其他定做机";s:4:"cpgg";s:6:"小机";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:32;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"200塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:33;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:34;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:4:"DTMB";s:4:"cpxh";s:4:"DTMB";s:4:"cpgg";s:9:"188铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:35;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:7:"DTMB+S2";s:4:"cpxh";s:7:"DTMB+S2";s:4:"cpgg";s:9:"200塑壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:36;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"高清";s:4:"cpxh";s:6:"高清";s:4:"cpgg";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:37;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:10:"高清+OTT";s:4:"cpxh";s:10:"高清+OTT";s:4:"cpgg";s:9:"225铁壳";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:38;a:16:{s:2:"lx";s:9:"地面波";s:5:"brand";s:6:"其他";s:4:"cpxh";s:6:"其他";s:4:"cpgg";s:15:"其他地面波";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:39;a:16:{s:2:"lx";s:9:"智能卡";s:5:"brand";s:9:"智能卡";s:4:"cpxh";s:9:"智能卡";s:4:"cpgg";s:9:"智能卡";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:40;a:16:{s:2:"lx";s:9:"高频头";s:5:"brand";s:9:"高频头";s:4:"cpxh";s:9:"高频头";s:4:"cpgg";s:9:"高频头";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:41;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.35天线";s:4:"cpgg";s:10:"0.35天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:42;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:10:"0.45天线";s:4:"cpgg";s:10:"0.45天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:43;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"自产天线";s:4:"cpgg";s:12:"自产天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:44;a:16:{s:2:"lx";s:6:"天线";s:5:"brand";s:6:"天线";s:4:"cpxh";s:12:"外购天线";s:4:"cpgg";s:12:"外购天线";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}i:45;a:16:{s:2:"lx";s:12:"电控产品";s:5:"brand";s:12:"电控产品";s:4:"cpxh";s:12:"电控产品";s:4:"cpgg";s:12:"电控产品";s:4:"dbsr";s:4:"0.00";s:4:"wgsr";s:4:"0.00";s:4:"xszc";s:4:"0.00";s:4:"dbzc";s:4:"0.00";s:4:"hhzc";s:4:"0.00";s:8:"zcspsrzc";s:4:"0.00";s:4:"qtzc";s:4:"0.00";s:4:"yxjc";s:1:"0";s:4:"hhsh";s:4:"0.00";s:6:"wxdbzc";s:4:"0.00";s:6:"wxqtzc";s:4:"0.00";s:4:"wxjc";s:1:"0";}}');
               }
              
            }
			
            $sdate =date("Ym01",strtotime($date));		//本月1号开始累加
            $syue = date("Y-m",strtotime($date));		//期初
            
            //计算结存
            $sql_qc="select yxjc,wxjc from new_smcpkcbqc where dept='$dept_id' and date='$syue' order by px";//查询期初
            $ret_qc=M()->query($sql_qc);
            $sql_lj="select sum(yxjc) as yxjc,sum(wxjc) as wxjc from new_smcpkcb t1 where dept='$dept_id' 
                and date between '$sdate' and '$date' group by lx,brand,cpxh,cpgg order by px; ";//查询月累计
            $ret_lj=M()->query($sql_lj);
            //遍历产品设置结存值
            foreach ($tempData as $k1 => $v1)
            {
                $yxjc=$ret_qc[$k1]['yxjc']+$ret_lj[$k1]['yxjc'];
                $wxjc=$ret_qc[$k1]['wxjc']+$ret_lj[$k1]['wxjc'];
                $tempData[$k1]['yxjc']=empty($yxjc)?0:$yxjc;
                $tempData[$k1]['wxjc']=empty($wxjc)?0:$wxjc;
            }            
            //产品总类（每一页的行数）每一行具体的数据
            foreach ($tempData as $rowKey=>$row){
              $resposeData['dept'][$key]['data'][$rowKey]['tds']=array();
               $temp=array();
               $temp[]['value']=$row['lx'];
               $temp[]['value']=empty($row['brand'])?$row['cpgg']:$row['brand'];
               $temp[]['value']=$row['cpxh'];
               $temp[]['value']=$row['cpgg'];
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
               $resposeData['dept'][$key]['data'][$rowKey]['tds']=$temp;
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
      
       //导入phpexcel所须文件
       vendor("PHPExcel.Classes.PHPExcel");
       vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
       
       $objPHPExcel = new \PHPExcel();
       //$objPHPExcel = \PHPExcel_IOFactory::load(APP_PATH."/Home/Controller/test.xls");
       
       //待打印的数据
       $printData=$this->search($token,$date,1,1000,true,$dept);
       //print_r($printData);return;
       $printData=$printData['dept'];
       $objPHPExcel->setActiveSheetIndex(0);
       //excel列索引
       $index=array("A","B","C","D","E","F","G","H","I",
           "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
       
       //循环打印每个部门的数据
       foreach ($printData as $dept_key=>$dept_value){
           $dept_title=$dept_value['title'];
           $row_offset=count($dept_value['title'])+count($dept_value['data']);
           //表头部分
           foreach ($dept_title as $k=>$v){
               
               $row_pos=$dept_key*$row_offset+$k+1;
               $v=$v['tds'];
               //表头第一行
               if($k==0){
                   $objPHPExcel->getActiveSheet()->setCellValue("B$row_pos",$v[0]['value']);
                   $objPHPExcel->getActiveSheet()->mergeCells("B$row_pos:Q$row_pos");
       
               }
               //表头第2行
               if($k==1){
                   $objPHPExcel->getActiveSheet()->setCellValue("B$row_pos",$v[0]['value']);
                   $objPHPExcel->getActiveSheet()->mergeCells("B$row_pos:D$row_pos");
                   $objPHPExcel->getActiveSheet()->setCellValue("E$row_pos",$v[1]['value']);
                   $objPHPExcel->getActiveSheet()->mergeCells("E$row_pos:M$row_pos");
                   $objPHPExcel->getActiveSheet()->setCellValue("N$row_pos",$v[2]['value']);
                   $objPHPExcel->getActiveSheet()->mergeCells("N$row_pos:Q$row_pos");
               }
               //表头第3行
               if($k==2){
                   //从B列开始打印16列
                   for($i=0;$i<16;$i++){
                       $offset=$index[$i+1];
                       $objPHPExcel->getActiveSheet()->setCellValue("$offset$row_pos",$v[$i]['value']);
                   }
               }
               //表头第4行
               if($k==3){
                   $objPHPExcel->getActiveSheet()->setCellValue("B$row_pos",$v[0]['value']);
                   $objPHPExcel->getActiveSheet()->getStyle("B$row_pos")->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                   $objPHPExcel->getActiveSheet()->getStyle("B$row_pos")->getFill()->getStartColor()->setRGB("99CCFF");
                   $objPHPExcel->getActiveSheet()->mergeCells("B$row_pos:Q$row_pos");
               }
               //设置居中
               $objPHPExcel->getActiveSheet()->getStyle("B$row_pos:Q$row_pos")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
           }
           //数据部分
           $dept_data=$dept_value['data'];
           $sheet = $objPHPExcel->getActiveSheet();
           foreach ($dept_data as $row_key=>$row_value){
               $row_pos=$dept_key*$row_offset+$row_key+1+count($dept_title);
               $row_value=$row_value['tds'];
               //从B列开始打印16列
               for($i=0;$i<16;$i++){
                   $offset=$index[$i+1];
                   //if(!$row_value[$i]['value'])
                   $sheet->setCellValue("$offset$row_pos",$row_value[$i]['value']);
               }
           }
       }
       
       //设置宽度
       $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
       $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
       $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
       $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
       //设置边框
       $objPHPExcel->getActiveSheet()->getStyle()->getBorders('A')->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
       $objPHPExcel->getActiveSheet()->getStyle()->getBorders()->getLeft()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
       $objPHPExcel->getActiveSheet()->getStyle()->getBorders()->getRight()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
       $objPHPExcel->getActiveSheet()->getStyle()->getBorders()->getBottom()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
       //设置填充色
       /* $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFill()->getStartColor()->setARGB('FF808080');
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFill()->getStartColor()->setARGB('FF808080'); */
       // 直接输出到浏览器
       $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        
       //定时调度上传
       if($flag){
           $dept_id=$dept['dept_id'];
		   //var_dump($objWriter);
           return \XSRBUtil::uploadExcel($dept_id, $objWriter,'NEW_SMCPKCBMX',$date);
       }else 
           return $objWriter;
   }
     //打印excel
     public function printExcel($token='',$date=''){
            set_time_limit(600);  //设置服务器执行此接口的时间
        ini_set('memory_limit', "-1");
         
         header("Access-Control-Allow-Origin: *");
         
       // return;
             //验证token
             $userinfo = checktoken($token);
             if(!$userinfo)
             {
                 $this->response(retmsg(-2),'json');
                 return;
             } 
             //部门id
             $dept_id= $userinfo['dept_id'];
             //$dept_id=25;
             //qt1判断是否是片区的标志
            $qt1=$userinfo['qt1'];
            //$qt1=100;
        // $pid=1;$dept_id=14;
           //如果是片区或者是总部则直接访问上传的excle
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
             
            $a = \XSRBUtil::download($dept_id,$date,'NEW_SMCPKCBMX');
            if ($a ==-1)
            {
                $objWriter=$this->toExcel($token,$date);
                $filename = "SMCPMX-$date-$dept_id.xls";
                
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
			         $filename = "NEW_SMCPKCBMX-$date-$dept_id.xls";
             $objWriter=$this->toExcel($token,$date);
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
     
     /**
      * 定时调度上传各部门的excel
      */
     public function uploadExcel($date=TODAY){
		  header("Access-Control-Allow-Origin: *");
         set_time_limit(600);
         $dept=array();
         $biao='NEW_SMCPKCBMX';
        $sql="select id,qt1 from xsrb_department where qt1 in (0,1)  and id not in(
             select dept_id from xsrb_excel where date='$date' and biao='$biao')";
         $depts=M()->query($sql);
		 $ret=1;
         foreach ($depts as $row){
             $dept['dept_id']=$row['id'];
             $dept['qt1']=$row['qt1'];
             $ret=$this->toExcel("",$date,$dept,true); 
         }
         if($ret)
            return '{"resultcode":1,"resultmsg":"新数码库存表明细上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"新数码库存表明细上传失败"}';
     }
          
     
}
?>