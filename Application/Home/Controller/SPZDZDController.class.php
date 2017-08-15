<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/13
 * Time: 10:10
 */
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class SPZDZDController extends RestController{
    //商品帐对账单查询
    public function search($token='',$type=1,$excel=0,$download='')
    {
        header("Access-Control-Allow-Origin: *");
        //token检测
        $userinfo = checktoken($token);
        if (!$userinfo) {
            $this->response(retmsg(-2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $yue = date("Y-m", strtotime(TODAY));
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'), "6379");
        $redis->auth(C('REDIS_PWD'));
        if (empty($download)){
            $riqi = M('spzpd')->query("select * from spzpd where dept=$dept and yuefen ='$yue' and flag=1 and  type=$type order by createtime desc limit 1");
            $sdate = $riqi[0]['sdate'];
            $edate = $riqi[0]['edate'];
            $json_pd = $redis->get('spzpd-' . $dept . '-' . $sdate . '-' . $edate . '-' . $type);
        }
        else
            $json_pd = $redis->get($download);
        if (empty($json_pd)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>$yue.'月没有盘点数据!'),'json');
        }
        $data = json_decode($json_pd,true);
        $riqi = date("Y年m月d日",strtotime($data['sdate'])).'至'.date("Y年m月d日",strtotime($data['edate']));
        $dname = M()->query("select * from xsrb_department where id =$dept");
        if ($type ==1)
            $dataType='有效';
        else
            $dataType = '无效';
        $temp = '{"data":[{"tr":[{"dataType":0,"colspan":5,"value":"'.$dataType.'商品对帐单(防盗门)"}]},{"tr":[{"dataType":0,"colspan":2,"value":"编制单位:'.$dname[0]['dname'].'"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":2,"value":"扎账区间: '.$riqi.'"}]},{"tr":[{"dataType":0,"colspan":2,"value":"收入项目"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":2,"value":"支出项目"}]},{"tr":[{"dataType":0,"colspan":1,"value":"期初项目"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"本月销售成本"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"本月调入"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"本月调出"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":1,"colspan":1,"value":"手动输入对方部门"},{"dataType":1,"colspan":1,"value":"手动输入对应金额"},{"dataType":0,"colspan":1,"value":""},{"dataType":1,"colspan":1,"value":"手动输入对方部门"},{"dataType":1,"colspan":1,"value":"手动输入对应金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":"送货收回"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"送货支出"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"铺货商品收入"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"铺货商品支出"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"暂存商品收入"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"暂存商品支出"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"其他收入"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"其他支出"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"调价升值"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"调价降值"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"盘点升溢"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"盘点短缺"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"本月结存"},{"dataType":0,"colspan":1,"value":"0"}]},{"tr":[{"dataType":0,"colspan":1,"value":"合计"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"合计"},{"dataType":0,"colspan":1,"value":"0"}]}]}';
        $json = json_decode($temp,true);
        //商品帐对账单收入,支出计算
        $json['data'][3]['tr'][1]['value'] = $data['data'][3]['tr'][14]['value'];        $json['data'][3]['tr'][4]['value'] = $data['data'][3]['tr'][28]['value'] + $data['data'][3]['tr'][26]['value'];        $json['data'][4]['tr'][1]['value'] = $data['data'][3]['tr'][16]['value'];        $json['data'][4]['tr'][4]['value'] = $data['data'][3]['tr'][30]['value'];        $json['data'][6]['tr'][1]['value'] = $data['data'][3]['tr'][22]['value'];        $json['data'][6]['tr'][4]['value'] = $data['data'][3]['tr'][36]['value'];        $json['data'][7]['tr'][1]['value'] = $data['data'][3]['tr'][20]['value'];        $json['data'][7]['tr'][4]['value'] = $data['data'][3]['tr'][34]['value'];        $json['data'][8]['tr'][1]['value'] = $data['data'][3]['tr'][18]['value'];        $json['data'][8]['tr'][4]['value'] = $data['data'][3]['tr'][32]['value'];        $json['data'][9]['tr'][1]['value'] = $data['data'][3]['tr'][24]['value'];        $json['data'][9]['tr'][4]['value'] = $data['data'][3]['tr'][38]['value'];        $json['data'][10]['tr'][1]['value'] = $data['data'][3]['tr'][45]['value'];        $json['data'][10]['tr'][4]['value'] = $data['data'][3]['tr'][48]['value'];        $json['data'][11]['tr'][1]['value'] = $data['data'][3]['tr'][44]['value'];        $json['data'][11]['tr'][4]['value'] = $data['data'][3]['tr'][47]['value'];        $json['data'][12]['tr'][4]['value'] = $data['data'][3]['tr'][42]['value'];
        //合计计算
        $json['data'][13]['tr'][1]['value'] = $json['data'][3]['tr'][1]['value']+$json['data'][4]['tr'][1]['value']+$json['data'][11]['tr'][1]['value']+$json['data'][6]['tr'][1]['value']+$json['data'][7]['tr'][1]['value']+$json['data'][8]['tr'][1]['value']+$json['data'][9]['tr'][1]['value']+$json['data'][10]['tr'][1]['value'];
        $json['data'][13]['tr'][4]['value'] = $json['data'][3]['tr'][4]['value']+$json['data'][4]['tr'][4]['value']+$json['data'][6]['tr'][4]['value']+$json['data'][7]['tr'][4]['value']+$json['data'][8]['tr'][4]['value']+$json['data'][9]['tr'][4]['value']+$json['data'][10]['tr'][4]['value']+$json['data'][11]['tr'][4]['value']+$json['data'][12]['tr'][4]['value'];
        $sql_dbsr = "select shouzhilb ,shouzzhimx,sum(shuliang*danjia) as jine from shouzhimx where `type` =$type and dept =$dept and create_date BETWEEN '".$data['sdate']."' and '".$data['edate']."' and shouzhilb ='调拨收入' group by shouzhilb,shouzzhimx";
        $sql_dbzc = "select shouzhilb ,shouzzhimx,sum(shuliang*danjia) as jine from shouzhimx where `type` =$type and dept =$dept and create_date BETWEEN '".$data['sdate']."' and '".$data['edate']."' and shouzhilb ='调拨支出' group by shouzhilb,shouzzhimx";
        $dbsr = M()->query($sql_dbsr);//调拨收入
        $dbzc = M()->query($sql_dbzc);//调拨支出
//        p($dbsr);p($dbzc);return;
        $i = count($dbsr);
        $j = count($dbzc);
        $n = ($i>=$j)?$i:$j;
        for ($k = 0;$k<$n;$k++){
            $arr[$k]['tr'][0] = array('dataType'=>0,'colspan'=>1,'value'=>$dbsr[$k]['shouzzhimx']);
            $arr[$k]['tr'][1] = array('dataType'=>0,'colspan'=>1,'value'=>$dbsr[$k]['jine']);
            $arr[$k]['tr'][2] = array('dataType'=>0,'colspan'=>1,'value'=>'');
            $arr[$k]['tr'][3] = array('dataType'=>0,'colspan'=>1,'value'=>$dbzc[$k]['shouzzhimx']);
            $arr[$k]['tr'][4] = array('dataType'=>0,'colspan'=>1,'value'=>$dbzc[$k]['jine']);
        }
        //收支明细 调拨收入
        unset($json['data'][5]);
        array_splice($json['data'],4,0,$arr);
        if ($excel ==1){    //导出对账单数据
            return json_encode($json);
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$json['data']),'json');
    }

    //下载excel
    public function toexcel($token='',$type=1,$redis=''){
        header("Access-Control-Allow-Origin: *");
        //token检测
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];

        if (empty($redis)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'没有盘点数据!'),'json');
        }
        $yue = substr($redis,-23,21);
        $json_dzd = $this->search($token,$type,1,$redis);
        $data = json_decode($json_dzd,true);
        $objPHPExcel = new \PHPExcel();
        foreach ($data['data'] as $key => $val ){
            foreach($val['tr'] as $k=>$v){
                if ($key >=3){
                    $dingwei = chr(ord('A')+$k).($key+1);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( $dingwei, $v['value']);
                }
            }
        }
        if ($type ==1)
            $typename='有效';
        else
            $typename = '无效';
        //excel表头
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$typename.'商品对帐单(防盗门)'); $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2',$data['data'][1]['tr'][0]['value']); $objPHPExcel->getActiveSheet()->mergeCells('A2:B2');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2','');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2',$data['data'][1]['tr'][2]['value']); $objPHPExcel->getActiveSheet()->mergeCells('D2:E2');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A3','收入项目'); $objPHPExcel->getActiveSheet()->mergeCells('A3:B3');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3','');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D3','支出项目'); $objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'A')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'B')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'D')->setWidth(15);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'E')->setWidth(15);
        $dname = M()->query("select * from xsrb_department where id =$dept");
        if ($type ==1)
            $fileName = $dname[0]['dname'].'-有效商品对账单-'.$yue.'.xls';
        else
            $fileName = $dname[0]['dname'].'-无效商品对账单-'.$yue.'.xls';
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save('php://output'); //文件通过浏览器下载
        return;
    }
}