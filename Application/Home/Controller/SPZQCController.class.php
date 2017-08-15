<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/14
 * Time: 11:51
 */
namespace Home\Controller;
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
        //$dname = M()->query("select dname from xsrb_department where id =$dept");
        if ($date =='')
            $yue = date("Y-m",strtotime(TODAY));
        $sql_info = "select create_time,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,qichusl,benyuedj,xiayuedj,qichuje from spzmxqc where dept=$dept and `date`='$yue' and `type`=$type order by dalei ";
        $result = M()->query($sql_info);
        if ($type ==1)
            $dataType='有效';
        else
            $dataType = '无效';
        $temp_json = '{"data":[{"tr":[{"dataType":0,"colspan":11,"value":"'.$dataType.'的商品帐产品信息"},{"dataType":0,"colspan":4,"value":"期初数据"}]},{"tr":[{"dataType":0,"colspan":1,"value":"制造部门"},{"dataType":0,"colspan":1,"value":"大类"},{"dataType":0,"colspan":1,"value":"非标"},{"dataType":0,"colspan":1,"value":"板厚"},{"dataType":0,"colspan":1,"value":"规格"},{"dataType":0,"colspan":1,"value":"表面要求"},{"dataType":0,"colspan":1,"value":"门框"},{"dataType":0,"colspan":1,"value":"花色"},{"dataType":0,"colspan":1,"value":"锁具"},{"dataType":0,"colspan":1,"value":"开向"},{"dataType":0,"colspan":1,"value":"其他"},{"dataType":0,"colspan":1,"value":"期初数量"},{"dataType":0,"colspan":1,"value":"本月单价"},{"dataType":0,"colspan":1,"value":"下月单价"},{"dataType":0,"colspan":1,"value":"期初金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":"必填列"},{"dataType":0,"colspan":1,"value":"必填列"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"}]}]}';
        $data = json_decode($temp_json,true);
        $data['data'][2]['tr'][14]['value'] =0;
        $data['data'][2]['tr'][11]['value'] =0;

        foreach($result as $k=> $v){
            $json ='{"tr":[
                {"dataType":0,"colspan":1,"value":"'.$v['zhizaobm'].'","create_time":"'.$v['create_time'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['dalei'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['feibiao'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['banhou'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['guige'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['biaomianyq'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['menkuang'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['huase'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['suoju'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['kaixiang'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['qita'].'"},
                {"dataType":1,"colspan":1,"value":"'.$v['qichusl'].'"},
                {"dataType":1,"colspan":1,"value":"'.$v['benyuedj'].'"},
                {"dataType":1,"colspan":1,"value":"'.$v['xiayuedj'].'"},
                {"dataType":0,"colspan":1,"value":"'.$v['qichusl']*$v['benyuedj'].'"}
                ]}';
            $data['data'][]=json_decode($json,true);
            $data['data'][2]['tr'][14]['value'] +=$v['qichusl']*$v['benyuedj'];
            $data['data'][2]['tr'][11]['value'] +=$v['qichusl'];
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$data['data']),'json');
    }
}