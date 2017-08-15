<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/8
 * Time: 16:53
 */
namespace NewSPZ\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class SPZPDController extends RestController{
    //商品帐盘点查询
    public function search($token='',$sdate='',$edate='',$type=1,$excel=''){
        header("Access-Control-Allow-Origin:*");
        if (empty($excel)){
            //token验证
            $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept = $userinfo['dept_id'];
        }else{
            $dept  = $excel;
        }
        $yue = date("Y-m",strtotime(TODAY));

        if ($sdate == '' || $edate ==''){
            //获取本月最近一次盘点开始与结束时间
            $sql_json = M('new_spzpd')->query("select * from new_spzpd where dept=$dept and yuefen='$yue' and type=$type order by createtime desc limit 1");
            $sdate = $sql_json[0]['sdate'];
            $edate = $sql_json[0]['edate'];
        }else{
            $sql_json = M()->query("select json,flag from new_spzpd where dept=$dept and sdate='$sdate' and type=$type and edate ='$edate'");
        }
        //获取已盘点数据
        $swJson = $sql_json[0]['json'];
        $shiwu = json_decode($swJson,true);

        if ($sdate =='' && $edate ==''){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'盘点时间段未设置!'),'json');
        }
        if (date("Y-m",strtotime($sdate)) != date("Y-m",strtotime($edate))){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'盘点时间不在同一个月份!'),'json');
        }

        $yuechu = date("Ym01",strtotime($sdate));
        $benyue = date("Y-m",strtotime($edate));
        $date = $edate;
        if (date("d",strtotime($sdate)) !=01){      //日期不是1号时,计算期初时间往前一天
            $dateqc1 = date("Y-m-d",(strtotime($sdate) - 3600*24));
            $dateqc = "select sum(jiecun) as qcsl from new_spzmx as m where dept=$dept and 
                    `date` BETWEEN '$yuechu' and '$dateqc1' and`type`=$type and product_md5 = q.product_md5 ";
        }else
            $dateqc = 0;
        $sql_qichu = "select shangpinjc,zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,
                            menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,
                            fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita, 
                            ((select ifnull(($dateqc),0)as value )+qichusl) as qcsl,benyuedj,xiayuedj,qichuje,
                            (select ifnull((select sum(dbsr) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as dbsr,0 as je1,
                            (select ifnull((select sum(zcsr) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as zcsr,0 as je2,
                            (select ifnull((select sum(phsr) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as phsr,0 as je3,
                            (select ifnull((select sum(shsh) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as shsh,0 as je4,
                            (select ifnull((select sum(qtsr) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as qtsr,0 as je5,
                            (select ifnull((select sum(zfxszc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as zfxszc,0 as je6,
                            (select ifnull((select sum(kfxszc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as kfxszc,0 as je7,
                            (select ifnull((select sum(dbzc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as dbzc,0 as je8,
                            (select ifnull((select sum(zczc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as zczc,0 as je9,
                            (select ifnull((select sum(phzc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as phzc,0 as je10,
                            (select ifnull((select sum(shzc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as shzc,0 as je11,
                            (select ifnull((select sum(bfzc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as bfzc,0 as je12,
                            (select ifnull((select sum(qtzc) from new_spzmx where `type`=$type and  dept=$dept and `date` BETWEEN '$sdate' and '$date' and product_md5 = q.product_md5),0))as qtzc,0 as je13,
                            0 as jiecun ,0 as je14 
                            from new_spzmxqc as q where `type`=$type and dept=$dept and `month`='$benyue' order by dangci ";
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
            $data[$k]['je12'] = $v['bfzc']*$v['benyuedj'];
            $data[$k]['je13'] = $v['qtzc']*$v['benyuedj'];
            $data[$k]['jiecun']= $v['dbsr']+$v['zcsr']+$v['phsr']+$v['shsh']+$v['qtsr']-$v['zfxszc']-$v['kfxszc']-$v['dbzc']-$v['zczc']-$v['shzc']-$v['qtzc']-$v['phzc']-$v['bfzc']+$v['qcsl'];
            $data[$k]['je14'] = ($v['dbsr']+$v['zcsr']+$v['phsr']+$v['shsh']+$v['qtsr']-$v['zfxszc']-$v['kfxszc']-$v['dbzc']-$v['zczc']-$v['shzc']-$v['qtzc']-$v['phzc']-$v['bfzc']+$v['qcsl'])*$v['benyuedj'];
        }
        if ($type ==1)
            $dataType='有效';
        else
            $dataType = '无效';
        $temp_json = '{"data":[{"tr":[{"dataType":0,"colspan":26,"value":"'.$dataType.'的产品信息"},
        {"dataType":0,"colspan":4,"value":"期初数据"},{"dataType":0,"colspan":10,"value":"当日收入"},
        {"dataType":0,"colspan":16,"value":"当日支出"},{"dataType":0,"colspan":2,"value":"当日财务结存"},
        {"dataType":0,"colspan":2,"value":"当日实物结存"},{"dataType":0,"colspan":6,"value":"盈亏及升降值"}]},
        
        {"tr":[
        {"dataType":0,"colspan":1,"rowspan":2,"value":"商品简称"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"制造部门"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"订单类别"},

        {"dataType":0,"colspan":1,"rowspan":2,"value":"档次"},{"dataType":0,"colspan":1,"rowspan":2,"value":"门框"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"框厚"},{"dataType":0,"colspan":1,"rowspan":2,"value":"前板厚"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"后板厚"},{"dataType":0,"colspan":1,"rowspan":2,"value":"底框材料"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"门扇"},{"dataType":0,"colspan":1,"rowspan":2,"value":"规格"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"开向"},{"dataType":0,"colspan":1,"rowspan":2,"value":"铰链"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"花色"},{"dataType":0,"colspan":1,"rowspan":2,"value":"表面方式"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"表面要求"},{"dataType":0,"colspan":1,"rowspan":2,"value":"窗花"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"猫眼"},{"dataType":0,"colspan":1,"rowspan":2,"value":"标牌"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"主锁"},{"dataType":0,"colspan":1,"rowspan":2,"value":"副锁"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"锁把"},{"dataType":0,"colspan":1,"rowspan":2,"value":"标件"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"包装品牌"},{"dataType":0,"colspan":1,"rowspan":2,"value":"包装方式"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"其他"},
        
        {"dataType":0,"colspan":1,"rowspan":2,"value":"期初数量"},{"dataType":0,"colspan":1,"rowspan":2,"value":"本月单价"},
        {"dataType":0,"colspan":1,"rowspan":2,"value":"下月单价"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初金额"},
        {"dataType":0,"colspan":2,"value":"调拨收入"},{"dataType":0,"colspan":2,"value":"暂存收入"},
        {"dataType":0,"colspan":2,"value":"铺货收入"},{"dataType":0,"colspan":2,"value":"送货收回"},
        {"dataType":0,"colspan":2,"value":"其他收入"},{"dataType":-1,"colspan":2,"value":"直发销售支出"},
        {"dataType":-1,"colspan":2,"value":"库房销售支出"},{"dataType":0,"colspan":2,"value":"调拨支出"},
        {"dataType":0,"colspan":2,"value":"暂存支出"},{"dataType":0,"colspan":2,"value":"铺货支出"},
        {"dataType":0,"colspan":2,"value":"送货支出"},
        {"dataType":0,"colspan":2,"value":"报废支出"},
        {"dataType":0,"colspan":2,"value":"其他支出"},
        {"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":2,"value":"盘盈"},{"dataType":0,"colspan":1,"value":"调价升值"},{"dataType":0,"colspan":2,"value":"盘亏"},{"dataType":0,"colspan":1,"value":"调价降值"}]},
        {"tr":[{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":-1,"colspan":1,"value":"数量"},{"dataType":-1,"colspan":1,"value":"金额"},{"dataType":-1,"colspan":1,"value":"数量"},{"dataType":-1,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"金额"}]},
        {"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":-1,"colspan":1,"value":""},{"dataType":-1,"colspan":1,"value":"0"},{"dataType":-1,"colspan":1,"value":""},{"dataType":-1,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":"0"}]}]}';
        $dataJson = json_decode($temp_json,true);
        $x = 15;    //产品信息的更多属性
        foreach($data as $kk=>$vv){
            //计算金额合计
            $dataJson['data']['3']['tr'][14+$x]['value'] += $vv['qichuje'];
            $dataJson['data']['3']['tr'][16+$x]['value'] += $vv['je1'];
            $dataJson['data']['3']['tr'][18+$x]['value'] += $vv['je2'];
            $dataJson['data']['3']['tr'][20+$x]['value'] += $vv['je3'];
            $dataJson['data']['3']['tr'][22+$x]['value'] += $vv['je4'];
            $dataJson['data']['3']['tr'][24+$x]['value'] += $vv['je5'];
            $dataJson['data']['3']['tr'][26+$x]['value'] += $vv['je6'];
            $dataJson['data']['3']['tr'][28+$x]['value'] += $vv['je7'];
            $dataJson['data']['3']['tr'][30+$x]['value'] += $vv['je8'];
            $dataJson['data']['3']['tr'][32+$x]['value'] += $vv['je9'];
            $dataJson['data']['3']['tr'][34+$x]['value'] += $vv['je10'];
            $dataJson['data']['3']['tr'][36+$x]['value'] += $vv['je11'];
            $dataJson['data']['3']['tr'][38+$x]['value'] += $vv['je12'];
            $dataJson['data']['3']['tr'][40+$x]['value'] += $vv['je13'];
            $dataJson['data']['3']['tr'][42+$x]['value'] += $vv['je14'];

            $dataJson['data']['3']['tr'][11+$x]['value'] += $vv['qcsl'];
            $dataJson['data']['3']['tr'][15+$x]['value'] += $vv['dbsr'];
            $dataJson['data']['3']['tr'][17+$x]['value'] += $vv['zcsr'];
            $dataJson['data']['3']['tr'][19+$x]['value'] += $vv['phsr'];
            $dataJson['data']['3']['tr'][21+$x]['value'] += $vv['shsh'];
            $dataJson['data']['3']['tr'][23+$x]['value'] += $vv['qtsr'];
            $dataJson['data']['3']['tr'][25+$x]['value'] += $vv['zfxszc'];
            $dataJson['data']['3']['tr'][27+$x]['value'] += $vv['kfxszc'];
            $dataJson['data']['3']['tr'][29+$x]['value'] += $vv['dbzc'];
            $dataJson['data']['3']['tr'][31+$x]['value'] += $vv['zczc'];
            $dataJson['data']['3']['tr'][33+$x]['value'] += $vv['phzc'];
            $dataJson['data']['3']['tr'][35+$x]['value'] += $vv['shzc'];
            $dataJson['data']['3']['tr'][37+$x]['value'] += $vv['bfzc'];
            $dataJson['data']['3']['tr'][39+$x]['value'] += $vv['qtzc'];
            $dataJson['data']['3']['tr'][41+$x]['value'] += $vv['jiecun'];

            //通过product_md5获取对应实物结存数据
            $md5 = md5($vv['zhizaobm'].$vv['dingdanlb'].$vv['dangci'].$vv['menkuang'].floatval($vv['kuanghou']).
                floatval($vv['qianbanhou']).floatval($vv['houbanhou']).$vv['dikuangcl'].$vv['menshan'].$vv['guige'].
                $vv['kaixiang'].$vv['jiaolian'].$vv['huase'].$vv['biaomianfs'].$vv['biaomianyq'].$vv['chuanghua'].
                $vv['maoyan'].$vv['biaopai'].$vv['zhusuo'].$vv['fusuo'].$vv['suoba'].$vv['biaojian'].$vv['baozhuangpp'].
                $vv['baozhuangfs'].$vv['qita'].$dept.$benyue.$type);
            $swjc = empty($shiwu[$md5])?0:$shiwu[$md5];
            //实物结存合计
            $dataJson['data']['3']['tr'][43+$x]['value'] += $swjc;

            //数量差值 单价差值
            $chazhi = $swjc - $vv['jiecun'];
            $chazhiDj = $vv['xiayuedj']-$vv['benyuedj'];
            //盘盈盘亏合计
            $dataJson['data'][3]['tr'][44+$x]['value'] += $swjc*$vv['benyuedj'];    //实物金额,盘亏金额初始值
            $dataJson['data'][3]['tr'][45+$x]['value'] += ($chazhi>=0?$chazhi:0);
            $dataJson['data'][3]['tr'][46+$x]['value'] += ($chazhi>=0?($chazhi*$vv['benyuedj']):0);
            $dataJson['data'][3]['tr'][47+$x]['value'] += ($chazhiDj>=0?$swjc*$chazhiDj:0);
            $dataJson['data'][3]['tr'][48+$x]['value'] += ($chazhi<0?abs($chazhi):0);
            $dataJson['data'][3]['tr'][49+$x]['value'] += ($chazhi<0?abs(($chazhi*$vv['benyuedj'])):0);
            $dataJson['data'][3]['tr'][50+$x]['value'] += ($chazhiDj<0?$swjc*abs($chazhiDj):0);

            $json ='{"tr":[
                {"dataType":0,"colspan":1,"value":"'.$vv['shangpinjc'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['zhizaobm'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['dingdanlb'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['dangci'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['menkuang'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['kuanghou'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['qianbanhou'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['houbanhou'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['dikuangcl'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['menshan'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['guige'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['kaixiang'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['jiaolian'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['huase'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['biaomianfs'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['biaomianyq'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['chuanghua'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['maoyan'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['biaopai'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['zhusuo'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['fusuo'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['suoba'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['biaojian'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['baozhuangpp'].'"},
                {"dataType":0,"colspan":1,"value":"'.$vv['baozhuangfs'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qita'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['qcsl'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['benyuedj'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['xiayuedj'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['qichuje'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['dbsr'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['je1'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['zcsr'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['je2'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['phsr'].'"},
            {"dataType":0,"colspan":1,"value":"'.$vv['je3'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['shsh'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je4'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qtsr'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je5'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['zfxszc'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['je6'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['kfxszc'].'"},{"dataType":-1,"colspan":1,"value":"'.$vv['je7'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['dbzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je8'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['zczc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je9'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['phzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je10'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['shzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je11'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['bfzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je12'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['qtzc'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je13'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['jiecun'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['je14'].'"},
            {"dataType":1,"colspan":1,"value":"'.$swjc.'"},{"dataType":0,"colspan":1,"value":"'.($swjc*$vv['benyuedj']).'"},
            {"dataType":0,"colspan":1,"value":"'.($chazhi>=0?$chazhi:0).'"},{"dataType":0,"colspan":1,"value":"'.($chazhi>=0?($chazhi*$vv['benyuedj']):0).'"},
            {"dataType":0,"colspan":1,"value":"'.($chazhiDj>=0?$swjc*$chazhiDj:0).'"},
            {"dataType":0,"colspan":1,"value":"'.($chazhi<0?abs($chazhi):0).'"},{"dataType":0,"colspan":1,"value":"'.($chazhi<0?abs(($chazhi*$vv['benyuedj'])):0).'"},
            {"dataType":0,"colspan":1,"value":"'.($chazhiDj<0?$swjc*abs($chazhiDj):0).'"}]}';
            $dataJson['data'][]=json_decode($json,true);
        }
        if(empty($excel)){
            $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','sdate'=>$sdate,'edate'=>$edate,'data'=>$dataJson['data']),'json');
        }else
            return array('resultcode'=>0,'resultmsg'=>'查询成功','sdate'=>$sdate,'edate'=>$edate,'data'=>$dataJson['data']);
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
        $yue = date("Y-m",strtotime(TODAY));
        //处理盘点时间段数据
        $x = 15;    //产品信息的更多属性
        $y = 2;     //在其他支出前面新增 报废支出一列
        foreach($data['data'] as $key => $val){
            if ($key>3){
                $v = $val['tr'];
                foreach($v as $k => $v1){
//                    if ($k ==(42+$x + $y)){   //计算盘点表当日实物结存金额
//                        $data['data'][$key]['tr'][$k]['value'] = $data['data'][$key]['tr'][$k-1]['value'] * $data['data'][$key]['tr'][12+$x]['value'];
//                    }
                    if ($k==(43+$x + $y)){    //计算盘盈数量与金额
                        if (($data['data'][$key]['tr'][41+$x + $y]['value']-$data['data'][$key]['tr'][39+$x + $y]['value'])>0)
                            $pysl = $data['data'][$key]['tr'][41+$x + $y]['value']-$data['data'][$key]['tr'][39+$x + $y]['value'];
                        else
                            $pysl = 0;
                        $data['data'][$key]['tr'][$k]['value'] = $pysl;
//                        $data['data'][$key]['tr'][$k+1]['value'] = $pysl*$data['data'][$key]['tr'][12+$x]['value'];
                    }
//                    if ($k==(45+$x + $y)){    //计算调价升值
//                        if (($data['data'][$key]['tr'][13+$x]['value']-$data['data'][$key]['tr'][12+$x]['value'])>0)
//                            $djsz = bcsub($data['data'][$key]['tr'][13+$x]['value'],$data['data'][$key]['tr'][12+$x]['value'],2)*$data['data'][$key]['tr'][41+$x + $y]['value'];
//                        else
//                            $djsz = 0;
//                        $data['data'][$key]['tr'][$k]['value'] =$djsz;
//                    }
                    if ($k==(46+$x + $y)){    //计算盘亏数量与金额
                        if (($data['data'][$key]['tr'][41+$x + $y]['value']-$data['data'][$key]['tr'][39+$x + $y]['value'])<0)
                            $pksl = $data['data'][$key]['tr'][39+$x + $y]['value'] - $data['data'][$key]['tr'][41+$x + $y]['value'];
                        else
                            $pksl = 0;
                        $data['data'][$key]['tr'][$k]['value'] = $pksl;
//                        $data['data'][$key]['tr'][$k+1]['value'] = $pksl*$data['data'][$key]['tr'][12+$x]['value'];
                    }
//                    if ($k==(48+$x + $y)){       //计算调价降值
//                        if (($data['data'][$key]['tr'][13+$x]['value']-$data['data'][$key]['tr'][12+$x]['value'])<0)
//                            $djjz = bcsub($data['data'][$key]['tr'][12+$x]['value'] , $data['data'][$key]['tr'][13+$x]['value'],2)*$data['data'][$key]['tr'][41+$x + $y]['value'];
//                        else
//                            $djjz = 0;
//                        $data['data'][$key]['tr'][$k]['value'] =$djjz;
//                    }
                }
                //本月md5
                $product_md5 = md5($v[1]['value'].$v[2]['value'].$v[3]['value'].$v[4]['value'].floatval($v[5]['value']).floatval($v[6]['value']).
                    floatval($v[7]['value']).$v[8]['value'].$v[9]['value']. $v[10]['value'].$v[11]['value'].$v[12]['value'].
                    $v[13]['value'].$v[14]['value'].$v[15]['value'].$v[16]['value'].$v[17]['value'].$v[18]['value'].
                    $v[19]['value'].$v[20]['value'].$v[21]['value'].$v[22]['value'].$v[23]['value'].$v[24]['value'].
                    $v[25]['value'].$dept.$yue.$type);
                //下月md5
                $product_md51 = md5($v[1]['value'].$v[2]['value'].$v[3]['value'].$v[4]['value'].floatval($v[5]['value']).floatval($v[6]['value']).
                    floatval($v[7]['value']).$v[8]['value'].$v[9]['value']. $v[10]['value'].$v[11]['value'].$v[12]['value'].
                    $v[13]['value'].$v[14]['value'].$v[15]['value'].$v[16]['value'].$v[17]['value'].$v[18]['value'].
                    $v[19]['value'].$v[20]['value'].$v[21]['value'].$v[22]['value'].$v[23]['value'].$v[24]['value'].
                    $v[25]['value'].$dept.$nextyue.$type);
                //盘点表-盘盈盘亏数据合计
                $sql_yuemo .= "($type,$dept,'$nextyue','".$v[0]['value']."','".$v[1]['value']."','".$v[2]['value']."','".$v[3]['value']."','".$v[4]['value']."','".$v[5]['value']."','".$v[6]['value']."','".$v[7]['value']."','".$v[8]['value']."','".$v[9]['value']."','".$v[10]['value']."',
                                '".$v[11]['value']."','".$v[12]['value']."','".$v[13]['value']."','".$v[14]['value']."','".$v[15]['value']."','".$v[16]['value']."','".$v[17]['value']."','".$v[18]['value']."','".$v[19]['value']."','".$v[20]['value']."','".$v[21]['value']."','".$v[22]['value']."','".$v[23]['value']."',
                                '".$v[24]['value']."','".$v[25]['value']."',
                                '".$v[13 + $x]['value']."','0','".$v[41 + $x + $y]['value']."','".$v[41 + $x + $y]['value']*$v[13 + $x]['value']."','$product_md51'),";
                $jiecun = $data['data'][$key]['tr'][43+$x + $y]['value']-$data['data'][$key]['tr'][46+ $x + $y]['value'];
                $sql_yuezhong .= "($type,$dept,'$nextday','$product_md5','".$data['data'][$key]['tr'][43+$x + $y]['value']."','".$data['data'][$key]['tr'][46+$x + $y]['value']."',$jiecun),";
                if (!empty($data['data'][$key]['tr'][43+ $x + $y]['value']))
                    $sql_shouzhimx .= "($type,$dept,'$nextday','$product_md5','".$data['data'][$key]['tr'][43 + $x + $y]['value']."','其他收入','有效调无效','".$data['data'][$key]['tr'][12+ $x]['value']."'),";

                if (!empty($data['data'][$key]['tr'][46+ $x + $y]['value']))
                    $sql_shouzhimx .= "($type,$dept,'$nextday','$product_md5','".$data['data'][$key]['tr'][46 + $x + $y]['value']."','其他支出','报废支出','".$data['data'][$key]['tr'][12+ $x]['value']."'),";

                //产品信息md5对应实物结存
                $shiwujc[$product_md5] = $data['data'][$key]['tr'][41+$x + $y]['value'];
            }
        }
        if ($lastday == $edate){        //月末盘点,实物结存为下月期初数据
            //处理月末盘点数据
            $sql_ins = "replace into new_spzmxqc(`type`,dept,`month`,shangpinjc,zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita,benyuedj,xiayuedj,qichusl,qichuje,product_md5)values" . rtrim($sql_yuemo, ',');
        }else {       //月中盘点,盘盈亏数据录入次日其他收入,其他支出里面
            M()->execute("delete from new_spzmx where dept =$dept and `date`='$nextday' and `type` =$type" );//清理
            $sql_ins = "replace into new_spzmx (`type`,dept,`date`,`product_md5`,qtsr,qtzc,jiecun)values" . rtrim($sql_yuezhong, ',');
            M()->execute("delete from new_shouzhimx where dept =$dept and `create_date`='$nextday' and `type` =$type" );//清理
            if ($sql_shouzhimx !=''){       //收支明细添加
                $sql_mx = "replace into new_shouzhimx(`type`,dept,`create_date`,`product_md5`,shuliang,shouzhilb,shouzhimx,danjia)values" . rtrim($sql_shouzhimx, ',');
                M()->execute($sql_mx);
            }
        }
        M()->execute($sql_ins);
        M()->execute("replace into new_spzpd (dept,createtime,flag,`type`,yuefen,sdate,edate,json)values
                    ($dept,now(),1,$type,'".date('Y-m',strtotime(TODAY))."','$sdate','$edate','".json_encode($shiwujc,JSON_UNESCAPED_UNICODE)."')");
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
        $yue = date("Y-m",strtotime(TODAY));
//        $redis_con = new \Redis();
//        $redis_con->connect(C('REDIS_URL'),"6379");
//        $redis_con->auth(C('REDIS_PWD'));
//        $json_pd = $redis_con->get($redis); //获取本月的对账单数据
        $sdate = substr($redis,-23,10);
        $edate = substr($redis,-12,10);

        $data = $this->search('',$sdate,$edate,$type,$dept);
        if (empty($data)){
            $this->response(array('resultcode'=>-1,'resultmsg'=>'没有盘点数据,请录入!'),'json');
        }
        $x = 15;$str = '';

        foreach ($data['data'] as $key => $val ){
            if ($type == 0){
                if ($key == 0){
                    $val['tr'][3]['colspan'] = 12;
                }
                if ($key ==1){
                    unset($val['tr'][35]);
                    unset($val['tr'][36]);
                }
                if ($key ==2){
                    unset($val['tr'][10]);
                    unset($val['tr'][11]);
                    unset($val['tr'][12]);
                    unset($val['tr'][13]);
                }
            }
            if ($key >2) {
                if ($type == 0) {
                    unset($val['tr'][25 + $x]);
                    unset($val['tr'][26 + $x]);
                    unset($val['tr'][27 + $x]);
                    unset($val['tr'][28 + $x]);
                    $val = array_merge($val);
                }
            }
            if ($key == 2){
                $str .=',,,,,,,,,,,,,,,,,,,,,,,,,,,,,,';
            }
            foreach($val['tr'] as $ktr =>$vtr){
                if ($key == 2){}
                $str .=$vtr['value'];
                if ($vtr['colspan'] != 0){
                    for ($i=0;$i<$vtr['colspan'];$i++){
                        $str .=',';
                    }
                }
            }
            $str .="\n";

        }
        $dname = M()->query("select * from xsrb_department where id =$dept");
        if ($type ==1)
            $typename = '有效';
        else
            $typename = '无效';
        $name = $dname[0]['dname'].'盘点表:'.$data['sdate'].'至'.$data['edate'].'-'.$typename;
        $fileName = $name.'.csv';
//        echo $str;return;
        $str = iconv('utf-8','gbk',$str);
        $this->export_csv($fileName,$str);
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
        $sql_list = "select sdate,edate from new_spzpd where dept = $dept and `type`=$type  and edate >='2017-06-01' order by createtime desc limit 10";
        $list = M()->query($sql_list);
        $controller = substr(__CONTROLLER__,0,-6);
        foreach($list as $klist=>$vlist){
            $redis = "spzpd-$dept-".$vlist['sdate'].'-'.$vlist['edate']."-$type";
            $data[] = array(
                'yuefen'=>$vlist['sdate'].'至'.$vlist['edate'],
                'excel_url'=>XSRB_IP.$controller."/$biao/toexcel/token/".$token."/redis/$redis/type/$type"
            );
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功!','data'=>$data),'json');
    }
    private function export_csv($filename,$data){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header("Content-Encoding: binary");
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
    }
}