<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/17
 * Time: 10:21
 */
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class SPZMXController extends RestController{

    /**
     * 商品帐明细表查询
     * @param string $token
     * @param string $date
     * @param string $type
     */
    public function search($token='',$date='',$type=1,$bumen_id='',$page=1){
        header("Access-Control-Allow-Origin:*");
        $dept = $bumen_id;
        if ($bumen_id ==''){
            $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept = $userinfo['dept_id'];
        }
        if ($date ==''){
            $date = TODAY;
        }else
            $date = date('Ymd',strtotime($date));
        $yue = date("Y-m",strtotime($date));
        $yuechu = date("Ym01",strtotime($date));
        if ($dept == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname from xsrb_department where pid != 1 and qt1 !=0 ";
        } else
        {
            // 片区部门
            $sql = "select id,dname from xsrb_department where qt2 like '%." . $dept . "'";

            // 判断部门(非片区and总部)的查询
        }
        $pagination = M()->query($sql);
        $count1 = count($pagination); //部门数
        //部门查询时,只显示本部门
        if ($count1 == 0)
        {
            $sql = "select id,dname from xsrb_department where id =" . $dept;
            $count1 = 1;
        }
        if ($bumen_id ==''){
            $bumen = M()->query($sql.' limit '.($page-1).',1');
            $dept = $bumen[0]['id'];
            if (date("d",strtotime($date)) !=01){      //日期不是1号时,计算期初时间往前一天
                $dateqc1 = date("Y-m-d",(strtotime($date) - 3600*24));
                $dateqc = "select sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc) as qcsl from spzmx as m where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN '$yuechu' and '$dateqc1'";
            }else
                $dateqc = 0;
            $sql_qichu = "select zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita, ((select ifnull(($dateqc),0)as value )+qichusl) as qcsl,benyuedj,qichuje,(select ifnull((select dbsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as dbsr,0 as je1,(select ifnull((select zcsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as zcsr,0 as je2,(select ifnull((select phsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as phsr,0 as je3,(select ifnull((select shsh from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as shsh,0 as je4,(select ifnull((select qtsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as qtsr,0 as je5,(select ifnull((select zfxszc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as zfxszc,0 as je6,(select ifnull((select kfxszc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as kfxszc,0 as je7,(select ifnull((select dbzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as dbzc,0 as je8,(select ifnull((select zczc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as zczc,0 as je9,(select ifnull((select phzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as phzc,0 as je10,(select ifnull((select shzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as shzc,0 as je11,(select ifnull((select qtzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as qtzc,0 as je12,0 as jiecun ,0 as je13 from spzmxqc as q where `type`=$type and dept=$dept and `date`='$yue' order by dalei ";
            $qichu = M()->query($sql_qichu);
            if (!count($qichu)){
                if ($bumen_id ==''){
                    //$this->response(array('resultcode'=>-1,'resultmsg'=>$bumen[0]['dname'].'期初数据未录入!'),'json');
                }
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
            $dname = M()->query("select dname from xsrb_department where id =$dept");
            $temp_json = '{"data":[{"tr":[{"dataType":0,"colspan":11,"value":"'.$dname[0]['dname'].'的产品信息"},{"dataType":0,"colspan":3,"value":"期初数据"},{"dataType":0,"colspan":10,"value":"当日收入"},{"dataType":0,"colspan":14,"value":"当日支出"},{"dataType":0,"colspan":2,"value":"当日财务结存"}]},{"tr":[{"dataType":0,"colspan":1,"rowspan":2,"value":"制造部门"},{"dataType":0,"colspan":1,"rowspan":2,"value":"大类"},{"dataType":0,"colspan":1,"rowspan":2,"value":"非标"},{"dataType":0,"colspan":1,"rowspan":2,"value":"板厚"},{"dataType":0,"colspan":1,"rowspan":2,"value":"规格"},{"dataType":0,"colspan":1,"rowspan":2,"value":"表面要求"},{"dataType":0,"colspan":1,"rowspan":2,"value":"门框"},{"dataType":0,"colspan":1,"rowspan":2,"value":"花色"},{"dataType":0,"colspan":1,"rowspan":2,"value":"锁具"},{"dataType":0,"colspan":1,"rowspan":2,"value":"开向"},{"dataType":0,"colspan":1,"rowspan":2,"value":"其他"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初数量"},{"dataType":0,"colspan":1,"rowspan":2,"value":"本月单价"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初金额"},{"dataType":0,"colspan":2,"value":"调拨收入"},{"dataType":0,"colspan":2,"value":"暂存收入"},{"dataType":0,"colspan":2,"value":"铺货收入"},{"dataType":0,"colspan":2,"value":"送货收回"},{"dataType":0,"colspan":2,"value":"其他收入"},{"dataType":-1,"colspan":2,"value":"直发销售支出"},{"dataType":-1,"colspan":2,"value":"库房销售支出"},{"dataType":0,"colspan":2,"value":"调拨支出"},{"dataType":0,"colspan":2,"value":"暂存支出"},{"dataType":0,"colspan":2,"value":"铺货支出"},{"dataType":0,"colspan":2,"value":"送货支出"},{"dataType":0,"colspan":2,"value":"其他支出"},{"dataType":0,"rowspan":2,"value":"数量"},{"dataType":0,"rowspan":2,"value":"金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":-1,"colspan":1,"value":"数量"},{"dataType":-1,"colspan":1,"value":"金额"},{"dataType":-1,"colspan":1,"value":"数量"},{"dataType":-1,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":-1,"colspan":1,"value":""},{"dataType":-1,"colspan":1,"value":"0"},{"dataType":-1,"colspan":1,"value":""},{"dataType":-1,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"}]}]}';
            $dataJson = json_decode($temp_json,true);
            foreach($data as $kk=>$vv){
                $dataJson['data']['3']['tr'][13]['value'] += $vv['qichuje'];
                $dataJson['data']['3']['tr'][11]['value'] += $vv['qcsl'];
                $dataJson['data']['3']['tr'][14]['value'] += $vv['dbsr'];
                $dataJson['data']['3']['tr'][15]['value'] += $vv['je1'];
                $dataJson['data']['3']['tr'][16]['value'] += $vv['zcsr'];
                $dataJson['data']['3']['tr'][17]['value'] += $vv['je2'];
                $dataJson['data']['3']['tr'][18]['value'] += $vv['phsr'];
                $dataJson['data']['3']['tr'][19]['value'] += $vv['je3'];
                $dataJson['data']['3']['tr'][20]['value'] += $vv['shsh'];
                $dataJson['data']['3']['tr'][21]['value'] += $vv['je4'];
                $dataJson['data']['3']['tr'][22]['value'] += $vv['qtsr'];
                $dataJson['data']['3']['tr'][23]['value'] += $vv['je5'];
                $dataJson['data']['3']['tr'][24]['value'] += $vv['zfxszc'];
                $dataJson['data']['3']['tr'][25]['value'] += $vv['je6'];
                $dataJson['data']['3']['tr'][26]['value'] += $vv['kfxszc'];
                $dataJson['data']['3']['tr'][27]['value'] += $vv['je7'];
                $dataJson['data']['3']['tr'][28]['value'] += $vv['dbzc'];
                $dataJson['data']['3']['tr'][29]['value'] += $vv['je8'];
                $dataJson['data']['3']['tr'][30]['value'] += $vv['zczc'];
                $dataJson['data']['3']['tr'][31]['value'] += $vv['je9'];
                $dataJson['data']['3']['tr'][32]['value'] += $vv['phzc'];
                $dataJson['data']['3']['tr'][33]['value'] += $vv['je10'];
                $dataJson['data']['3']['tr'][34]['value'] += $vv['shzc'];
                $dataJson['data']['3']['tr'][35]['value'] += $vv['je11'];
                $dataJson['data']['3']['tr'][36]['value'] += $vv['qtzc'];
                $dataJson['data']['3']['tr'][37]['value'] += $vv['je12'];
                $dataJson['data']['3']['tr'][38]['value'] += $vv['jiecun'];
                $dataJson['data']['3']['tr'][39]['value'] += $vv['je13'];
                $json ='{"tr":[
                {"dataType":0,"colspan":1,"value":"'.$vv['zhizaobm'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['dalei'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['feibiao'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['banhou'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['guige'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['biaomianyq'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['menkuang'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['huase'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['suoju'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['kaixiang'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qita'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qcsl'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['benyuedj'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qichuje'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['dbsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je1'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['zcsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je2'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['phsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je3'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['shsh'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je4'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qtsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je5'].'"},                {"dataType":-1,"colspan":1,"value":"'.$vv['zfxszc'].'"},                {"dataType":-1,"colspan":1,"value":"'.$vv['je6'].'"},                {"dataType":-1,"colspan":1,"value":"'.$vv['kfxszc'].'"},                {"dataType":-1,"colspan":1,"value":"'.$vv['je7'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['dbzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je8'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['zczc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je9'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['phzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je10'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['shzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je11'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qtzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je12'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['jiecun'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je13'].'"}                ]}';
                $dataJson['data'][]=json_decode($json,true);
            }
        }else{
            $bumen = M()->query($sql);
            foreach($bumen as $vbumen){  //导出数据处理
                $dept = $vbumen['id'];
                if (date("d",strtotime($date)) !=01){      //日期不是1号时,计算期初时间往前一天
                    $dateqc1 = date("Y-m-d",(strtotime($date) - 3600*24));
                    $dateqc = "select sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc) as qcsl from spzmx as m where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` BETWEEN '$yuechu' and '$dateqc1'";
                }else
                    $dateqc = 0;
                $sql_qichu = "select zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita, ((select ifnull(($dateqc),0)as value )+qichusl) as qcsl,benyuedj,qichuje,(select ifnull((select dbsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as dbsr,0 as je1,(select ifnull((select zcsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as zcsr,0 as je2,(select ifnull((select phsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as phsr,0 as je3,(select ifnull((select shsh from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as shsh,0 as je4,(select ifnull((select qtsr from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as qtsr,0 as je5,(select ifnull((select zfxszc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as zfxszc,0 as je6,(select ifnull((select kfxszc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as kfxszc,0 as je7,(select ifnull((select dbzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as dbzc,0 as je8,(select ifnull((select zczc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as zczc,0 as je9,(select ifnull((select phzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as phzc,0 as je10,(select ifnull((select shzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as shzc,0 as je11,(select ifnull((select qtzc from spzmx where `type`=$type and zhizaobm =q.zhizaobm and dalei=q.dalei and feibiao=q.feibiao and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept=$dept and `date` ='$date'),0))as qtzc,0 as je12,0 as jiecun ,0 as je13 from spzmxqc as q where `type`=$type and dept=$dept and `date`='$yue' order by dalei ";
                $qichu = M()->query($sql_qichu);
                foreach($qichu as $k=> $v){     //计算金额
                    $data[$k] = array(
                        $v['zhizaobm'],$v['dalei'],$v['feibiao'],$v['banhou'],$v['guige'],$v['biaomianyq'],$v['menkuang'],$v['huase'], $v['suoju'],$v['kaixiang'], $v['qita'],
                        $v['qcsl'],  $v['benyuedj'],$v['qcsl']*$v['benyuedj'],
                        $v['dbsr'],  $v['dbsr']*$v['benyuedj'],
                        $v['zcsr'],                        $v['zcsr']*$v['benyuedj'],
                        $v['phsr'],                        $v['phsr']*$v['benyuedj'],
                        $v['shsh'],                        $v['shsh']*$v['benyuedj'],
                        $v['qtsr'],                        $v['qtsr']*$v['benyuedj'],
                        $v['zfxszc'],                      $v['zfxszc']*$v['benyuedj'],
                        $v['kfxszc'],                      $v['kfxszc']*$v['benyuedj'],
                        $v['dbzc'],                        $v['dbzc']*$v['benyuedj'],
                        $v['zczc'],                        $v['zczc']*$v['benyuedj'],
                        $v['phzc'],                        $v['phzc']*$v['benyuedj'],
                        $v['shzc'],                        $v['shzc']*$v['benyuedj'],
                        $v['qtzc'],                        $v['qtzc']*$v['benyuedj'],
                        ($v['dbsr']+$v['zcsr']+$v['phsr']+$v['shsh']+$v['qtsr']-$v['zfxszc']-$v['kfxszc']-$v['dbzc']-$v['zczc']-$v['shzc']-$v['qtzc']-$v['phzc']+$v['qcsl']),
                        ($v['dbsr']+$v['zcsr']+$v['phsr']+$v['shsh']+$v['qtsr']-$v['zfxszc']-$v['kfxszc']-$v['dbzc']-$v['zczc']-$v['shzc']-$v['qtzc']-$v['phzc']+$v['qcsl'])*$v['benyuedj']
                    );
                }
                $dataJson[$vbumen['dname']] = $data;
                unset($data);
            }
        }
        if ($bumen_id =='') //查询输出数据
            $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','page'=>$page,'cntperpage'=>$count1,'data'=>$dataJson['data']),'json');
        else
            return $dataJson;   //返回导出需要的数据
    }

    public function uploadExcel($date ='',$bumen_id ='',$type=1)
    {
        ini_set('max_execution_time',2000);
        ini_set('memory_limit', "-1");
        header("Access-Control-Allow-Origin:*");
        if($date =='')
        {
            $date = date("Ymd",strtotime("-1 day"));		//根据昨天的数据生成Excel
        }
        $ret = 1;
        $sql = "select id,dname from xsrb_department where id =1 or qt1 =0 order by id desc";
        $dept_id = M()->query($sql);
        if ($bumen_id !='' && isset($bumen_id))
        {
            $dname = M()->query("select * from xsrb_department where id =$bumen_id");
            $dept_id = array(
                array('id' =>$bumen_id,'dname'=>$dname[0]['dname'])
            );
        }
        foreach ($dept_id as $k1 => $v1)
        {
            //商品帐明细表表头数据
            if ($type ==1){
                $title = ",,,,,,产品信息(B-L),,,,,,,期初数据(M-O),,,,,,,当日收入(P-Y),,,,,,,,,,,,当日支出(Z-AM),,,,,,,当日账务结存(AN-AO),,\n";
                $title .=",,,,,,,,,,,,,,,调拨收入,,暂存收入,,铺货收入,,送货收回,,其他收入,,直发销售支出,,库房消息支出,,调拨支出,,暂存支出,,铺货支出,,送货支出,,其他支出,,,\n";
                $title .="部门信息,制造部门,大类,非标,板厚,规格,表面要求,门框,花色,锁具,开向,其他,期初数量,本月单价,期初金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,\n";
            }else{
                $title = ",,,,,,产品信息(B-L),,,,,,,期初数据(M-O),,,,,,,当日收入(P-Y),,,,,,,,,,当日支出(Z-AI),,,,,当日账务结存(AJ-AK),,\n";
                $title .=",,,,,,,,,,,,,,,调拨收入,,暂存收入,,铺货收入,,送货收回,,其他收入,,调拨支出,,暂存支出,,铺货支出,,送货支出,,其他支出,,,\n";
                $title .="部门信息,制造部门,大类,非标,板厚,规格,表面要求,门框,花色,锁具,开向,其他,期初数量,本月单价,期初金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,数量,金额,\n";

            }
            $department='';$info='';
            $cx = M()->query("select * from xsrb_excel where dept_id =".$v1['id'].$type." and `date` =".$date." and `biao` ='spzmx' ");
            if (!count($cx))  //判断此循环下的部门是否已导入
            {
                $json = $this->search('',$date,$type,$v1['id']);
                foreach ($json as $k2 => $v2)
                {
                    if (count($v2)){  //判断部门没有录入期初数据的
                        foreach ($v2 as $k3 => $v3)
                        {
                            if ($type == 0 ){
                                unset($v3[24]);unset($v3[25]);unset($v3[26]);unset($v3[27]);
                                $arr =  array_merge($v3);
                            }else
                                $arr = $v3;
                            foreach($arr as $k4=>$v4){
                                $info .="$v4,";
                            }
                            if ($k3==0)
                                $dname = $k2;
                            else
                                $dname ='';
                            $department .="$dname,$info,\n";
                            unset($info);
                        }
                    }else
                        $department .="$k2,\n";
                }
                $strs = $title.$department;
                $strs = iconv('utf-8', 'gbk', $strs);
                //生成csv文件,保存在当前项目目录下
                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$v1['id'].'-spzmx-'.$type.'-'.$date.'.csv';
                if ($bumen_id !='')
                {
                    $fileName = $v1['id'].'-spzmx-'.$type.'-'.$date.'.csv';
                    header("Content-Type: text/csv");
                    header("Content-Disposition: attachment; filename=$fileName");
                    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
                    header('Expires:0');
                    header('Pragma:public');
                    echo $strs;
                    return;
                }
                $fp = fopen($keys,"a");     //生成csv文件
                fwrite($fp,$strs);
                fclose($fp);
                ob_clean();

                $keys = "http://xsrb.wsy.me:801/files/".$v1['id'].'-spzmx-'.$type.'-'.$date.'.csv';
                $cxo = M()->query("select * from xsrb_excel where dept_id =".$v1['id'].$type." and `date` =".$date." and `biao` ='spzmx' ");
                if(!count($cxo))
                {
                    $cxo =1;
                    if ($keys !='')    //上传成功返回url时,存入数据库
                    {                //当前部门的文件下载地址存入数据库
                        $sql = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$v1['id'].$type.",'spzmx','$date','$keys')";
                        M()->execute($sql);
                    }
                }
                $ret = -1;
            }
        }
        if($ret ==1)
            return '{"resultcode":1,"resultmsg":"其他收入明细表上传成功"}';
        else
            return '{"resultcode":-1,"resultmsg":"其他收入明细表上传失败"}';
    }

    //下载excel
    public function toexcel($token='',$date='',$type=1)
    {
        header("Access-Control-Allow-Origin: *");
        switch ($this->_method)
        {
            case 'get':
            {
                //token检测
                $userinfo = checktoken($token);
                if (! $userinfo) {
                    $this->response(retmsg(- 2), 'json');
                    return;
                }
                $dept_id = $userinfo['dept_id'];
                //日期选择
                if ($date == '') {
                    $date = date("Ymd",strtotime("-1 day"));
                } else {
                    if ($date >= date('Ymd'))
                        $date = date("Ymd",strtotime("-1 day"));
                    else
                        $date = date("Ymd", strtotime($date));
                }
				
                //把有效无效
                $sql = "select * from xsrb_excel where biao ='spzmx' and `dept_id` =".$dept_id.$type.' and `date` ='.$date.' limit 1';
                $result = M()->query($sql);
                if (count($result))
                {
                    if($_SERVER['SERVER_NAME'] =='172.16.10.252')
                    {
                        $excel_url = str_replace('xsrb.wsy.me:801','172.16.10.252',$result[0]['url']);
                    }else
                    {
                        $excel_url =$result[0]['url'];
                    }
                    $arr = array(
                        'excel_url'=>$excel_url
                    );
                }else
                {
                    $arr = array(
                        'excel_url'=>C('Controller_url')."/SPZMX/uploadExcel/type/$type/date/".$date."/bumen_id/".$dept_id
                    );
                }
                //将一维关联数组转换为json字符串
                $json = json_encode($arr);
                echo $json;
            }
        }
    }
}