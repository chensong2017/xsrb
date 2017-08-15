<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/14
 * Time: 11:51
 */
namespace NewSPZ\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
//经营部商品帐明细list表
class SPZQCController extends RestController{
    /**
     * 商品帐期初查询
     * @param string $token
     * @param string $date
     */
    public function search($token='',$date='',$type=1){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
//        $dname = M()->query("select dname from xsrb_department where id =$dept");
        if ($date =='')
            $yue = date("Y-m",strtotime(TODAY));
        $sql_info = "select create_time,shangpinjc,zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,
                            menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,
                            fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita,qichusl,benyuedj,xiayuedj,qichuje,product_md5 from new_spzmxqc where dept=$dept and `month`='$yue' and `type`=$type order by dangci ";
        $result = M()->query($sql_info);
        if ($type ==1)
            $dataType='有效';
        else
            $dataType = '无效';
        $temp_json = '{"data":[{"tr":[{"dataType":0,"colspan":26,"value":"'.$dataType.'的商品帐产品信息"},
        {"dataType":0,"colspan":4,"value":"期初数据"}]},
        {"tr":[{"dataType":0,"colspan":1,"value":"商品简称"},{"dataType":0,"colspan":1,"value":"制造部门"},
        {"dataType":0,"colspan":1,"value":"订单类别"},
        {"dataType":0,"colspan":1,"value":"档次"},{"dataType":0,"colspan":1,"value":"门框"},
        {"dataType":0,"colspan":1,"value":"框厚"},{"dataType":0,"colspan":1,"value":"前板厚"},
        {"dataType":0,"colspan":1,"value":"后板厚"},{"dataType":0,"colspan":1,"value":"底框材料"},
        {"dataType":0,"colspan":1,"value":"门扇"},{"dataType":0,"colspan":1,"value":"规格"},
        {"dataType":0,"colspan":1,"value":"开向"},
        {"dataType":0,"colspan":1,"value":"铰链"},{"dataType":0,"colspan":1,"value":"花色"},
        {"dataType":0,"colspan":1,"value":"表面方式"},{"dataType":0,"colspan":1,"value":"表面要求"},
        {"dataType":0,"colspan":1,"value":"窗花"},{"dataType":0,"colspan":1,"value":"猫眼"},
        {"dataType":0,"colspan":1,"value":"标牌"},{"dataType":0,"colspan":1,"value":"主锁"},
        {"dataType":0,"colspan":1,"value":"副锁"},{"dataType":0,"colspan":1,"value":"锁把"},
        {"dataType":0,"colspan":1,"value":"标件"},{"dataType":0,"colspan":1,"value":"包装品牌"},
        {"dataType":0,"colspan":1,"value":"包装方式"},{"dataType":0,"colspan":1,"value":"其他"},
        {"dataType":0,"colspan":1,"value":"期初数量"},{"dataType":0,"colspan":1,"value":"本月单价"},
        {"dataType":0,"colspan":1,"value":"下月单价"},{"dataType":0,"colspan":1,"value":"期初金额"}]},
        {"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"必填列"},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""}]}]}';
        $data = json_decode($temp_json,true);
        $data['data'][2]['tr'][29]['value'] =0;
        $data['data'][2]['tr'][26]['value'] =0;
        foreach($result as $k=> $v){
            $json ='{"tr":[
                {"dataType":1,"colspan":1,"value":"'.$v['shangpinjc'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['zhizaobm'].'","create_time":"'.$v['create_time'].'","product_md5":"'.$v['product_md5'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['dingdanlb'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['dangci'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['menkuang'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['kuanghou'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['qianbanhou'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['houbanhou'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['dikuangcl'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['menshan'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['guige'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['kaixiang'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['jiaolian'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['huase'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['biaomianfs'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['biaomianyq'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['chuanghua'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['maoyan'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['biaopai'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['zhusuo'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['fusuo'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['suoba'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['biaojian'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['baozhuangpp'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['baozhuangfs'].'"},           
                {"dataType":0,"colspan":1,"value":"'.$v['qita'].'"},
                {"dataType":1,"colspan":1,"value":"'.$v['qichusl'].'"},
                {"dataType":1,"colspan":1,"value":"'.$v['benyuedj'].'"},
                {"dataType":1,"colspan":1,"value":"'.$v['xiayuedj'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['qichusl']*$v['benyuedj'].'"}
                ]}';
            $data['data'][]=json_decode($json,true);
            $data['data'][2]['tr'][29]['value'] +=$v['qichusl']*$v['benyuedj'];
            $data['data'][2]['tr'][26]['value'] +=$v['qichusl'];
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$data['data']),'json');
    }
}