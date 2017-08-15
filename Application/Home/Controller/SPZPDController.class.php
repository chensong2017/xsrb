<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/8
 * Time: 16:53
 */
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class SPZPDController extends RestController{
    //商品帐盘点查询
    public function search($token='',$sdate='',$edate='',$type=1){
        header("Access-Control-Allow-Origin:*");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
		
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $yueshu = date("Y-m",strtotime(TODAY));

        if ($sdate == '' || $edate ==''){
            //获取本月最近一次盘点开始与结束时间
            $riqi = M('spzpd')->query("select * from spzpd where dept=$dept and yuefen='$yueshu' and type=$type order by createtime desc limit 1");
            $sdate = $riqi[0]['sdate'];
            $edate = $riqi[0]['edate'];
			
        }

        //获取已盘点数据
        $json = $redis->get('spzpd-'.$dept.'-'.$sdate.'-'.$edate.'-'.$type);
        //当前月份
        $yue = date("Y-m",strtotime(TODAY));
        $arr = json_decode($json,true);

        //有redis数据就显示该查询数据
        if (!empty($json)){
            if (date('Ymd',strtotime($arr['edate'])) == TODAY){
                echo $json;return;
            }else{
                echo str_replace('"dataType":1','"dataType":0',$json);return;
            }
        }
        if ($sdate =='' && $edate ==''){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'盘点时间段未设置!'),'json');
        }
        if (date("Y-m",strtotime($sdate)) != date("Y-m",strtotime($edate))){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'盘点时间不在同一个月份!'),'json');
        }

        $yuechu = date("Ym01",strtotime($sdate));

        $date = $edate;
        if (date("d",strtotime($sdate)) !=01){      //日期不是1号时,计算期初时间往前一天
            $dateqc1 = date("Y-m-d",(strtotime($date) - 3600*24));
            $dateqc = "select sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc) as qcsl from spzmx as m where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN '$yuechu' and '$dateqc1'";
        }else
            $dateqc = 0;
        $sql_qichu = "select zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita, ((select ifnull(($dateqc),0)as value )+qichusl) as qcsl,benyuedj,xiayuedj,qichuje,(select ifnull((select sum(dbsr)as dbsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao and  dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as dbsr,0 as je1,(select ifnull((select sum(zcsr)as zcsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as zcsr,0 as je2,(select ifnull((select sum(phsr)as phsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as phsr,0 as je3,(select ifnull((select sum(shsh)as shsh from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as shsh,0 as je4,(select ifnull((select sum(qtsr)as qtsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as qtsr,0 as je5,(select ifnull((select sum(zfxszc)as zfxszc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as zfxszc,0 as je6,(select ifnull((select sum(kfxszc)as kfxszc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as kfxszc,0 as je7,(select ifnull((select sum(dbzc)as dbzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as dbzc,0 as je8,(select ifnull((select sum(zczc)as zczc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as zczc,0 as je9,(select ifnull((select sum(phzc)as phzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as phzc,0 as je10,(select ifnull((select sum(shzc)as shzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as shzc,0 as je11,(select ifnull((select sum(qtzc)as qtzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and feibiao=q.feibiao  and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN  '$sdate' and '$date'),0))as qtzc,0 as je12,0 as jiecun ,0 as je13 from spzmxqc as q where `type`=$type and dept=$dept and `date`='$yue' order by dalei";
        $qichu = M()->query($sql_qichu);
        if (!count($qichu)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'期初数据未录入!'),'json');
        }
        foreach($qichu as $k=> $v){
            $data[$k]=$v;
            $data[$k]['qichuje'] = $v['qcsl']*$v['benyuedj'];
            $data[$k]['je1'] = $v['dbsr']*$v['benyuedj'];
            $data[$k]['je2'] = $v['zcsr']*$v['benyuedj'];
            $data[$k]['je3'] = $v['phsr']*$v['benyuedj'];
            $data[$k]['je4'] = $v['shsh']*$v['benyuedj'];
            $data[$k]['je5'] = $v['qtsr']*$v['benyuedj'];
            $data[$k]['je6'] = $v['zfxszc']*$v['benyuedj'];
            $data[$k]['je7'] = $v['kfxszc']*$v['benyuedj'];
            $data[$k]['je8'] = $v['dbzc']*$v['benyuedj'];
            $data[$k]['je9'] = $v['zczc']*$v['benyuedj'];
            $data[$k]['je10'] = $v['phzc']*$v['benyuedj'];
            $data[$k]['je11'] = $v['shzc']*$v['benyuedj'];
            $data[$k]['je12'] = $v['qtzc']*$v['benyuedj'];
            $data[$k]['jiecun']=$v['dbsr']+$v['zcsr']+$v['phsr']+$v['shsh']+$v['qtsr']-$v['zfxszc']-$v['kfxszc']-$v['dbzc']-$v['zczc']-$v['shzc']-$v['qtzc']-$v['phzc']+$v['qcsl'];
            $data[$k]['je13'] = ($v['dbsr']+$v['zcsr']+$v['phsr']+$v['shsh']+$v['qtsr']-$v['zfxszc']-$v['kfxszc']-$v['dbzc']-$v['zczc']-$v['shzc']-$v['qtzc']-$v['phzc']+$v['qcsl'])*$v['benyuedj'];
        }
        if ($type ==1)
            $dataType='有效';
        else
            $dataType = '无效';
        $temp_json = '{"data":[{"tr":[{"dataType":0,"colspan":11,"value":"'.$dataType.'的产品信息"},{"dataType":0,"colspan":4,"value":"期初数据"},{"dataType":0,"colspan":10,"value":"当日收入"},{"dataType":0,"colspan":14,"value":"当日支出"},{"dataType":0,"colspan":2,"value":"当日财务结存"},{"dataType":0,"colspan":2,"value":"当日实物结存"},{"dataType":0,"colspan":6,"value":"盈亏及升降值"}]},{"tr":[{"dataType":0,"colspan":1,"rowspan":2,"value":"制造部门"},{"dataType":0,"colspan":1,"rowspan":2,"value":"大类"},{"dataType":0,"colspan":1,"rowspan":2,"value":"非标"},{"dataType":0,"colspan":1,"rowspan":2,"value":"板厚"},{"dataType":0,"colspan":1,"rowspan":2,"value":"规格"},{"dataType":0,"colspan":1,"rowspan":2,"value":"表面要求"},{"dataType":0,"colspan":1,"rowspan":2,"value":"门框"},{"dataType":0,"colspan":1,"rowspan":2,"value":"花色"},{"dataType":0,"colspan":1,"rowspan":2,"value":"锁具"},{"dataType":0,"colspan":1,"rowspan":2,"value":"开向"},{"dataType":0,"colspan":1,"rowspan":2,"value":"其他"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初数量"},{"dataType":0,"colspan":1,"rowspan":2,"value":"本月单价"},{"dataType":0,"colspan":1,"rowspan":2,"value":"下月单价"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初金额"},{"dataType":0,"colspan":2,"value":"调拨收入"},{"dataType":0,"colspan":2,"value":"暂存收入"},{"dataType":0,"colspan":2,"value":"铺货收入"},{"dataType":0,"colspan":2,"value":"送货收回"},{"dataType":0,"colspan":2,"value":"其他收入"},{"dataType":-1,"colspan":2,"value":"直发销售支出"},{"dataType":-1,"colspan":2,"value":"库房销售支出"},{"dataType":0,"colspan":2,"value":"调拨支出"},{"dataType":0,"colspan":2,"value":"暂存支出"},{"dataType":0,"colspan":2,"value":"铺货支出"},{"dataType":0,"colspan":2,"value":"送货支出"},{"dataType":0,"colspan":2,"value":"其他支出"},{"dataType":0,"rowspan":2,"value":"数量"},{"dataType":0,"rowspan":2,"value":"金额"},{"dataType":0,"rowspan":2,"value":"数量"},{"dataType":0,"rowspan":2,"value":"金额"},{"dataType":0,"colspan":2,"value":"盘盈"},{"dataType":0,"colspan":1,"value":"调价升值"},{"dataType":0,"colspan":2,"value":"盘亏"},{"dataType":0,"colspan":1,"value":"调价降值"}]},{"tr":[{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":-1,"colspan":1,"value":"数量"},{"dataType":-1,"colspan":1,"value":"金额"},{"dataType":-1,"colspan":1,"value":"数量"},{"dataType":-1,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":-1,"colspan":1,"value":""},{"dataType":-1,"colspan":1,"value":"0"},{"dataType":-1,"colspan":1,"value":""},{"dataType":-1,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"}]}]}';
        $dataJson = json_decode($temp_json,true);
        foreach($data as $kk=>$vv){
            //计算金额合计
            $dataJson['data']['3']['tr'][14]['value'] += $vv['qichuje'];
            $dataJson['data']['3']['tr'][16]['value'] += $vv['je1'];
            $dataJson['data']['3']['tr'][18]['value'] += $vv['je2'];
            $dataJson['data']['3']['tr'][20]['value'] += $vv['je3'];
            $dataJson['data']['3']['tr'][22]['value'] += $vv['je4'];
            $dataJson['data']['3']['tr'][24]['value'] += $vv['je5'];
            $dataJson['data']['3']['tr'][26]['value'] += $vv['je6'];
            $dataJson['data']['3']['tr'][28]['value'] += $vv['je7'];
            $dataJson['data']['3']['tr'][30]['value'] += $vv['je8'];
            $dataJson['data']['3']['tr'][32]['value'] += $vv['je9'];
            $dataJson['data']['3']['tr'][34]['value'] += $vv['je10'];
            $dataJson['data']['3']['tr'][36]['value'] += $vv['je11'];
            $dataJson['data']['3']['tr'][38]['value'] += $vv['je12'];
            $dataJson['data']['3']['tr'][40]['value'] += $vv['je13'];
            $json ='{"tr":[{"dataType":0,"colspan":1,"value":"'.$vv['zhizaobm'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['dalei'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['feibiao'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['banhou'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['guige'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['biaomianyq'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['menkuang'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['huase'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['suoju'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['kaixiang'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qita'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qcsl'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['benyuedj'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['xiayuedj'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qichuje'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['dbsr'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je1'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['zcsr'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je2'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['phsr'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je3'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['shsh'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je4'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qtsr'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je5'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['zfxszc'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['je6'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['kfxszc'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['je7'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['dbzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je8'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['zczc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je9'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['phzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je10'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['shzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je11'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qtzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je12'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['jiecun'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je13'].'"},{"dataType":1,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"}]}';
            $dataJson['data'][]=json_decode($json,true);
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','sdate'=>$sdate,'edate'=>$edate,'data'=>$dataJson['data']),'json');
    }
    //商品帐盘点保存
    public function submit($token='',$type=1){
        header("Access-Control-Allow-Origin:*");
        switch ($this->_method){
            case 'post':{
                break;
            }
            case 'get':{
                $this->response(array('resultcode'=>-1,'resultmsg'=>'数据传递方式错误!'),'json');
                break;
            }
        }
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $json = file_get_contents("php://input");
        $data = json_decode($json,true);
        if (date('Ymd',strtotime($data['edate'])) != TODAY){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'盘点结束时间不正确!'),'json');
            return;
        }
        //判断盘点结束时间是月中或者是月末
        $sdate = $data['sdate'];
        $edate = $data['edate'];
        $firstday = date("Y-m-01",strtotime($edate));
        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
        $nextyue = date("Y-m",strtotime("$lastday +1day"));
        $nextday = date("Y-m-d",strtotime("$edate +1 day"));
        $yue = date("Ym",strtotime(TODAY));
        //处理盘点时间段数据
        $data['data'][3]['tr'][42]['value'] = 0;    //实物金额,盘亏金额初始值
        $data['data'][3]['tr'][44]['value'] = 0;
        $data['data'][3]['tr'][45]['value'] = 0;
        $data['data'][3]['tr'][47]['value'] = 0;
        $data['data'][3]['tr'][48]['value'] = 0;
        foreach($data['data'] as $key => $val){
            if ($key>3){
                $v = $val['tr'];
                foreach($v as $k => $v1){
                    if ($k ==42){   //计算盘点表当日实物结存金额
                        $data['data'][$key]['tr'][$k]['value'] = $data['data'][$key]['tr'][$k-1]['value'] * $data['data'][$key]['tr'][12]['value'];
                    }
                    if ($k==43){    //计算盘盈数量与金额
                        if (($data['data'][$key]['tr'][41]['value']-$data['data'][$key]['tr'][39]['value'])>0)
                            $pysl = $data['data'][$key]['tr'][41]['value']-$data['data'][$key]['tr'][39]['value'];
                        else
                            $pysl = 0;
                        $data['data'][$key]['tr'][$k]['value'] = $pysl;
                        $data['data'][$key]['tr'][$k+1]['value'] = $pysl*$data['data'][$key]['tr'][12]['value'];
                    }
                    if ($k==45){    //计算调价升值
                        if (($data['data'][$key]['tr'][13]['value']-$data['data'][$key]['tr'][12]['value'])>0)
                            $djsz = bcsub($data['data'][$key]['tr'][13]['value'],$data['data'][$key]['tr'][12]['value'],2)*$data['data'][$key]['tr'][41]['value'];
                        else
                            $djsz = 0;
                        $data['data'][$key]['tr'][$k]['value'] =$djsz;
                    }
                    if ($k==46){    //计算盘亏数量与金额
                        if (($data['data'][$key]['tr'][41]['value']-$data['data'][$key]['tr'][39]['value'])<0)
                            $pksl = $data['data'][$key]['tr'][39]['value'] - $data['data'][$key]['tr'][41]['value'];
                        else
                            $pksl = 0;
                        $data['data'][$key]['tr'][$k]['value'] = $pksl;
                        $data['data'][$key]['tr'][$k+1]['value'] = $pksl*$data['data'][$key]['tr'][12]['value'];
                    }
                    if ($k==48){       //计算调价降值
                        if (($data['data'][$key]['tr'][13]['value']-$data['data'][$key]['tr'][12]['value'])<0)
                            $djjz = bcsub($data['data'][$key]['tr'][12]['value'] , $data['data'][$key]['tr'][13]['value'],2)*$data['data'][$key]['tr'][41]['value'];
                        else
                            $djjz = 0;
                        $data['data'][$key]['tr'][$k]['value'] =$djjz;
                    }
                }
                //盘点表-盘盈盘亏数据合计
                $sql_yuemo .= "($type,$dept,'$nextyue','".$v[0]['value']."','".$v[1]['value']."','".$v[2]['value']."','".$v[3]['value']."','".$v[4]['value']."','".$v[5]['value']."','".$v[6]['value']."','".$v[7]['value']."','".$v[8]['value']."','".$v[9]['value']."','".$v[10]['value']."','".$v[41]['value']."','".$v[13]['value']."','0','".$v[41]['value']*$v[13]['value']."'),";
                $jiecun = $data['data'][$key]['tr'][43]['value']-$data['data'][$key]['tr'][46]['value'];
				$sql_yuezhong .= "($type,$dept,'$nextday','".$v[0]['value']."','".$v[1]['value']."','".$v[2]['value']."','".$v[3]['value']."','".$v[4]['value']."','".$v[5]['value']."','".$v[6]['value']."','".$v[7]['value']."','".$v[8]['value']."','".$v[9]['value']."','".$v[10]['value']."','".$data['data'][$key]['tr'][43]['value']."','".$data['data'][$key]['tr'][46]['value']."',$jiecun),";
                if ($data['data'][$key]['tr'][43]['value'] != false)
                    $sql_shouzhimx .= "($type,$dept,'$nextday','".$v[0]['value']."','".$v[1]['value']."','".$v[2]['value']."','".$v[3]['value']."','".$v[4]['value']."','".$v[5]['value']."','".$v[6]['value']."','".$v[7]['value']."','".$v[8]['value']."','".$v[9]['value']."','".$v[10]['value']."','".$data['data'][$key]['tr'][43]['value']."','其他收入','有效调无效','".$data['data'][$key]['tr'][13]['value']."',1),";
                if ($data['data'][$key]['tr'][46]['value'] != false)
                    $sql_shouzhimx .= "($type,$dept,'$nextday','".$v[0]['value']."','".$v[1]['value']."','".$v[2]['value']."','".$v[3]['value']."','".$v[4]['value']."','".$v[5]['value']."','".$v[6]['value']."','".$v[7]['value']."','".$v[8]['value']."','".$v[9]['value']."','".$v[10]['value']."','".$data['data'][$key]['tr'][46]['value']."','其他支出','报废支出','".$data['data'][$key]['tr'][13]['value']."',1),";

                //计算盘盈盘亏合计
                $data['data'][3]['tr'][42]['value'] += $data['data'][$key]['tr'][42]['value'];
                $data['data'][3]['tr'][44]['value'] += $data['data'][$key]['tr'][44]['value'];
                $data['data'][3]['tr'][45]['value'] += $data['data'][$key]['tr'][45]['value'];
                $data['data'][3]['tr'][47]['value'] += $data['data'][$key]['tr'][47]['value'];
                $data['data'][3]['tr'][48]['value'] += $data['data'][$key]['tr'][48]['value'];
            }
        }
        if ($lastday == $edate){        //月末盘点,实物结存为下月期初数据
            //处理月末盘点数据
            $sql_ins = "replace into spzmxqc(`type`,dept,`date`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,qichusl,benyuedj,xiayuedj,qichuje)values" . rtrim($sql_yuemo, ',');
        }else {       //月中盘点,盘盈亏数据录入次日其他收入,其他支出里面
            $sql_ins = "replace into spzmx(`type`,dept,`date`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,qtsr,qtzc,jiecun)values" . rtrim($sql_yuezhong, ',');
            M()->execute("delete from shouzhimx where dept =$dept and `create_date`='$nextday' and `type` =$type" );//清理
            if ($sql_shouzhimx !=''){       //收支明细添加
                $sql_mx = "replace into shouzhimx(`type`,dept,`create_date`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,shuliang,shouzhilb,shouzzhimx,danjia,pandianId)values" . rtrim($sql_shouzhimx, ',');
                M()->execute($sql_mx);
            }
        }
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $redis->set('spzpd-'.$dept.'-'.$sdate.'-'.$edate.'-'.$type,json_encode($data));
        M()->execute($sql_ins);
        M()->execute("replace into spzpd (dept,createtime,flag,`type`,yuefen,sdate,edate)values($dept,now(),1,$type,'".date('Y-m',strtotime(TODAY))."','$sdate','$edate')");
        $this->response(array('resultcode'=>0,'resultmsg'=>'保存成功!'),'json');
    }
    //商品帐盘点导出
    public function toexcel($token='',$redis='',$type=1){
        header("Access-Control-Allow-Origin: *");
        //token检测
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $yue = date("Ym",strtotime(TODAY));
        $redis_con = new \Redis();
        $redis_con->connect(C('REDIS_URL'),"6379");
        $redis_con->auth(C('REDIS_PWD'));
        $json_pd = $redis_con->get($redis); //获取本月的对账单数据
        if (empty($json_pd)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'没有盘点数据,请录入!'),'json');
        }

        $check_pd = M()->query("select flag from spzpd where dept=$dept and `type`=$type and yuefen ='".date("Y-m",strtotime(TODAY))."' and flag =0");
        if (count($check_pd)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'盘点数据未保存!'),'json');
        }
        $data = json_decode($json_pd,true);
        $objPHPExcel = new \PHPExcel();
        foreach ($data['data'] as $key => $val ){
            if ($key >2){
                if ($type == 0 ){
                    unset($val['tr'][25]);unset($val['tr'][26]);unset($val['tr'][27]);unset($val['tr'][28]);
                    $arr =  array_merge($val['tr']);
                }else
                    $arr = $val['tr'];
                foreach($arr as $k=>$v){
                    if ((ord('A')+$k) >90)     //ascii码值超过Z时,key从AA开始增加
                    {
                        $dingwei = 'A'.chr(ord('A')+$k-26).($key+1);
                    }else
                    {
                        $dingwei = chr(ord('A')+$k).($key+1);
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($dingwei,$v['value']);
                }
            }
        }
//        exit();
        //盘点表表头数据
        if ($type ==1){
            $title = array(
                //第一行
                array('A1','产品信息'),array('L1','期初数据'), array('P1','当日收入'),array('Z1','当日支出'),array('AN1','当日账务结存'), array('AP1','当日实物结存'),array('AR1','盘亏及升降值'),
                //第二行
                array('A2','制造部门'), array('B2','大类') ,array('C2','非标'),array('D2','板厚'),array('E2','规格'),array('F2','表面要求'),array('G2','门框'),
                array('H2','花色'), array('I2','锁具'),array('J2','开向'),array('K2','其他'),array('L2','期初数量'),array('M2','本月单价'),array('N2','下月单价'),
                array('O2','期初金额'),array('P2','调拨收入'), array('R2','暂存收入'), array('T2','铺货收入'),array('V2','送货收回'), array('X2','其他收入'),
                array('Z2','直发销售支出'),array('AB2','库房销售支出'),array('AD2','调拨支出'), array('AF2','暂存支出'), array('AH2','铺货支出'),
                array('AJ2','送货支出'),array('AL2','其他支出'),
                //第三行
                array('P3','数量'),array('Q3','金额'),array('R3','数量'),array('S3','金额'), array('T3','数量'),array('U3','金额'),array('V3','数量'),
                array('W3','金额'),array('X3','数量'),array('Y3','金额'),array('Z3','数量'),array('AA3','金额'), array('AB3','数量'),array('AC3','金额'),
                array('AD3','数量'),array('AE3','金额'), array('AF3','数量'),array('AG3','金额'),array('AH3','数量'),array('AI3','金额'),  array('AJ3','数量'),
                array('AK3','金额'),array('AL3','数量'),array('AM3','金额'),array('AN2','数量'),array('AO2','金额'),
                array('AP2','数量'),            array('AQ2','金额'),            array('AR2','盘盈'),            array('AT2','调价升值'),
                array('AU2','盘亏'),            array('AW2','调价降值'),        array('AR3','数量'),            array('AS3','金额'),
                array('AT3','金额'),            array('AU3','数量'),            array('AV3','金额'),            array('AW3','金额')
            );
            //合并单元格
            $hebing = array(
                'AN2:AN3','AO2:AO3','AP2:AP3','AQ2:AQ3','AR2:AS2','AU2:AV2','Z1:AM1','AN1:AO1','AP1:AQ1','AR1:AW1','AJ2:AK2','AL2:AM2','AB2:AC2','AD2:AE2','AF2:AG2','AH2:AI2','P2:Q2','R2:S2','T2:U2','V2:W2','X2:Y2','Z2:AA2','J2:J3','K2:K3','L2:L3','M2:M3','N2:N3','O2:O3','A2:A3','B2:B3','C2:C3','D2:D3','E2:E3','F2:F3','G2:G3','H2:H3','I2:I3','A1:K1','L1:O1','P1:Y1'
            );
        }else{
            $title = array(
                //第一行
                array('A1','产品信息'),array('L1','期初数据'), array('P1','合计收入'),array('Z1','合计支出'),array('AJ1','当日账务结存'), array('AL1','当日实物结存'),array('AN1','盘亏及升降值'),
                //第二行
                array('A2','制造部门'), array('B2','大类') ,array('C2','非标'),array('D2','板厚'),array('E2','规格'),array('F2','表面要求'),array('G2','门框'),
                array('H2','花色'), array('I2','锁具'),array('J2','开向'),array('K2','其他'),array('L2','期初数量'),array('M2','本月单价'),array('N2','下月单价'),
                array('O2','期初金额'),array('P2','调拨收入'), array('R2','暂存收入'), array('T2','铺货收入'),array('V2','送货收回'), array('X2','其他收入'),
                array('Z2','调拨支出'),array('AB2','暂存支出'),array('AD2','铺货支出'), array('AF2','送货支出'), array('AH2','其他支出'),
                //第三行
                array('P3','数量'),array('Q3','金额'),array('R3','数量'),array('S3','金额'), array('T3','数量'),array('U3','金额'),array('V3','数量'),
                array('W3','金额'),array('X3','数量'),array('Y3','金额'),array('Z3','数量'),array('AA3','金额'), array('AB3','数量'),array('AC3','金额'),
                array('AD3','数量'),array('AE3','金额'), array('AF3','数量'),array('AG3','金额'),array('AH3','数量'),array('AI3','金额'),
                array('AN3','数量'),            array('AO3','金额'),array('AP3','金额'),array('AQ3','数量'),array('AR3','金额'),array('AS3','金额'),
                array('AJ2','数量'),array('AK2','金额'),
                array('AL2','数量'),            array('AM2','金额'),            array('AN2','盘盈'),            array('AP2','调价升值'),
                array('AQ2','盘亏'),            array('AS2','调价降值')
            );
            //合并单元格
            $hebing = array(
                'AL2:AL3','AM2:AM3','AN2:AO2','AQ2:AR2','AB2:AC2','AD2:AE2','AF2:AG2','AH2:AI2','AJ2:AJ3','AK2:AK3','P2:Q2','R2:S2','T2:U2','V2:W2','X2:Y2','Z2:AA2','J2:J3','K2:K3','L2:L3','M2:M3','N2:N3','O2:O3','AN1:AS1','A2:A3','B2:B3','C2:C3','D2:D3','E2:E3','F2:F3','G2:G3','H2:H3','I2:I3','A1:K1','L1:O1','P1:Y1','Z1:AI1','AJ1:AK1','AL1:AM1'
            );
        }
        foreach($title as $vtitle){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($vtitle[0],$vtitle[1]);
        }
        foreach($hebing as $vhebing){
            $objPHPExcel->getActiveSheet()->mergeCells($vhebing);
        }
        //盘点表excel表头设置
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'A')->setWidth(15);
        $dname = M()->query("select * from xsrb_department where id =$dept");
        if ($type ==1)
            $typename = '有效';
        else
            $typename = '无效';
        $name = $dname[0]['dname'].'盘点表:'.$data['sdate'].'至'.$data['edate'].'-'.$typename;
        $fileName = $name.'.xls';
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $objWriter->save('php://output'); //文件通过浏览器下载
        return;
    }

    public function downloadList($token = '',$type = 1,$biao='SPZPD'){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $arrBiao = array('SPZPD','SPZDZD');
        if (!in_array($biao,$arrBiao)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'表名错误!'),'json');
        }
        $sql_list = "select sdate,edate from spzpd where dept = $dept and `type`=$type and flag =1 and edate >='2017-05-01' order by createtime desc limit 10";
        $list = M()->query($sql_list);
        foreach($list as $klist=>$vlist){
            $redis = "spzpd-$dept-".$vlist['sdate'].'-'.$vlist['edate']."-$type";
            $data[] = array(
                'yuefen'=>$vlist['sdate'].'至'.$vlist['edate'],
                'excel_url'=>C('Controller_url')."/$biao/toexcel/token/".$token."/redis/$redis/type/$type"
            );
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功!','data'=>$data),'json');
    }
}