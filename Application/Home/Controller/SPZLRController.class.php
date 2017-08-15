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
class SPZLRController extends RestController{
    //商品帐明细表导入
    public function loadingExcelMx($token='',$type='1'){
        $date = TODAY;
        header("Access-Control-Allow-Origin: *");
        //token检测
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        if($_POST ['import']=="导入数据") {
            //获取上传的文件名
            $file = $_FILES['inputExcel'] ['name'];
            $filetempname = $_FILES ['inputExcel']['tmp_name'];
            $filePath = dirname(dirname(dirname(dirname(__FILE__)))) . '\\excel\\';
            $filename = explode(".", $file);//把上传的文件名以“.”做一个数组。
            $time = date("YmdHis");
            $filename [0] = $time;//取文件名t替换
            $name = implode(".", $filename); //上传后的文件名
            $uploadfile = $filePath . $name;
            $sql = '';
            $result = move_uploaded_file($filetempname, $uploadfile);
            if ($result){       //文件上传ok
                $objPHPExcel = \PHPExcel_IOFactory::load($uploadfile);
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow(); // 总行数
                $highestColumn = $sheet->getHighestColumn(); // 总列数
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
                for ($j = 5; $j <= $highestRow; $j++) {
                    $currentColumn = 'A';
                    for ($i = 1; $i <= $highestColumnIndex; $i++) {
                        $k = $currentColumn++;
                        $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . ',';//读取单元格value数据,
                    }
                    $strs = explode(",", trim($str, ','));
                    $str = '';
                    //对当日没有收入与支出的产品数据不插入数据库,已减小数据库存储量
//                        if ($strs[13] == 0 && $strs[15] == 0 && $strs[17] == 0 && $strs[19] == 0 && $strs[21] == 0 && $strs[23] == 0 && $strs[25] == 0 && $strs[27] == 0 && $strs[29] == 0 && $strs[31] == 0 && $strs[33] == 0 && $strs[35] == 0)
//                        {}
//                        else
                    $sql .= "($type,$dept,'$date','" . $strs[0] . "','" . $strs[1] . "','" . $strs[2] . "','" . $strs[3] . "','" . $strs[4] . "','" . $strs[5] . "','" . $strs[6] . "','" . $strs[7] . "','" . $strs[8] . "','" . $strs[9] . "','".$strs[13]."','" . $strs[15] . "','" . $strs[17] . "','" . $strs[19] . "','" . $strs[21] . "','" . $strs[23] . "','" . $strs[25] . "','" . $strs[27] . "','" . $strs[29] . "','" . $strs[31] . "','" . $strs[33] . "','" . $strs[35] . "','".($strs[13]['value']+$strs[15]['value']+$strs[17]['value']+$strs[19]['value']+$strs[21]['value']-$strs[23]['value']-$strs[25]['value']-$strs[27]['value']-$strs[29]['value']-$strs[31]['value']-$strs[33]['value']-$strs[35]['value'])."'),";
                }
                if ($sql != ''){
                    $sql = 'replace INTO spzmx(`type`,`dept`,`date`,zhizaobm,dalei,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,dbsr,zcsr,phsr,shsh,qtsr,zfxszc,kfxszc,dbzc,zczc,phzc,shzc,qtzc,jiecun) Values' . rtrim($sql, ',');
                    M()->execute($sql);
                    $this->response(array('resultcode'=>0,'resultmsg'=>'上传成功'),'json');
                }
            }
        }
    }

    /**
     * 商品帐明细录入
     */
    public function submit($token='',$type ='1'){
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
        $date = TODAY;
        $json = file_get_contents("php://input");   //获取保存提交的数据
        $data = json_decode($json,true);
        foreach($data['data'] as $key=> $val){
            if ($key>=4){
                $v = $val['tr'];
                //对录入数据行全为0的做不插入处理
//                if ($v[12]['value'] ==0 && $v[14]['value'] ==0 && $v[16]['value'] ==0 && $v[18]['value'] ==0 && $v[20]['value'] ==0 && $v[22]['value'] ==0 && $v[24]['value'] ==0 && $v[26]['value'] ==0 && $v[28]['value'] ==0 && $v[30]['value'] ==0 && $v[32]['value'] ==0 && $v[34]['value'] ==0 )
//                {}
//                else
                $sql .= "($type,$dept,'$date','" .$v[0]['value'] . "','" . $v[1]['value'] . "','" . $v[2]['value'] . "','" . $v[3]['value'] . "','" .$v[4]['value'] . "','" . $v[5]['value'] . "','" . $v[6]['value'] . "','" . $v[7]['value'] . "','" . $v[8]['value'] . "','".$v[9]['value']."','" . $v[13]['value'] . "','" .$v[15]['value'] . "','" . $v[17]['value'] . "','" . $v[19]['value'] . "','" . $v[21]['value'] . "','" . $v[23]['value'] . "','" . $v[25]['value'] . "','" . $v[27]['value'] . "','" . $v[29]['value'] . "','" . $v[31]['value'] . "','" . $v[33]['value'] . "','" . $v[35]['value'] . "','".($v[13]['value']+$v[15]['value']+$v[17]['value']+$v[19]['value']+$v[21]['value']-$v[23]['value']-$v[25]['value']-$v[27]['value']-$v[29]['value']-$v[31]['value']-$v[33]['value']-$v[35]['value'])."'),";
            }
        }
        if ($sql != ''){
            $sql = 'replace INTO spzmx(`type`,`dept`,`date`,zhizaobm,dalei,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,dbsr,zcsr,phsr,shsh,qtsr,zfxszc,kfxszc,dbzc,zczc,phzc,shzc,qtzc,jiecun) Values' . rtrim($sql, ',');
            M()->execute($sql);
            $this->response(array('resultcode'=>0,'resultmsg'=>'保存成功!'),'json');
        }
    }

    /**
     * 查询当日录入数据的
     * @param string $token
     * @param string $type
     */
    public function search($token='',$type='1'){
        header("Access-Control-Allow-Origin:*");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $date = TODAY;
        $yue = date("Y-m",strtotime($date));
        $yuechu = date("Ym01",strtotime($date));
        $dateqc = $date;  //期初计算时间
        //当天期初及为昨天结存
        if (date("d",strtotime($date)) !=01){
            $dateqc1 = date("Ymd",(strtotime($date) - 3600*24)); //计算1号至现在每日的支出与收入差之和
            $dateqc = "select sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-phzc-shzc-qtzc) as meirijc from spzmx as m where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` BETWEEN '$yuechu' and '$dateqc1'";
        }else
            $dateqc = 0;
        $sql_qichu = "select zhizaobm,dalei,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita, ((select ifnull(($dateqc),0)as value )+qichusl) as qcsl,benyuedj,qichuje,(select ifnull((select dbsr from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as dbsr,0 as je1,(select ifnull((select zcsr from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as zcsr,0 as je2,(select ifnull((select phsr from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as phsr,0 as je3,(select ifnull((select shsh from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as shsh,0 as je4,(select ifnull((select qtsr from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as qtsr,0 as je5,(select ifnull((select zfxszc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as zfxszc,0 as je6,(select ifnull((select kfxszc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as kfxszc,0 as je7,(select ifnull((select dbzc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as dbzc,0 as je8,(select ifnull((select zczc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as zczc,0 as je9,(select ifnull((select phzc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as phzc,0 as je10,(select ifnull((select shzc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as shzc,0 as je11,(select ifnull((select qtzc from spzmx where `type`='$type' and dalei=q.dalei and banhou=q.banhou and guige=q.guige and biaomianyq=q.biaomianyq and menkuang=q.menkuang and huase=q.huase and suoju=q.suoju and kaixiang=q.kaixiang and qita=q.qita and dept='$dept' and `date` ='$date'),0))as qtzc,0 as je12,0 as jiecun ,0 as je13 from spzmxqc as q where `type`='$type' and dept='$dept' and `date`='$yue' order by dalei ";
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
        $dname = M()->query("select dname from xsrb_department where id =$dept");
        if ($type ==1)
            $dataType='有效';
        else
            $dataType = '无效';
        $temp_json = '{"data":[{"tr":[{"dataType":0,"colspan":10,"value":"'.$dname[0]['dname'].$dataType.'的商品帐产品信息"},{"dataType":0,"colspan":3,"value":"期初数据"},{"dataType":0,"colspan":10,"value":"当日收入"},{"dataType":0,"colspan":14,"value":"当日支出"},{"dataType":0,"colspan":2,"value":"当日财务结存"}]},{"tr":[{"dataType":0,"colspan":1,"rowspan":2,"value":"制造部门"},{"dataType":0,"colspan":1,"rowspan":2,"value":"大类"},{"dataType":0,"colspan":1,"rowspan":2,"value":"板厚"},{"dataType":0,"colspan":1,"rowspan":2,"value":"规格"},{"dataType":0,"colspan":1,"rowspan":2,"value":"表面要求"},{"dataType":0,"colspan":1,"rowspan":2,"value":"门框"},{"dataType":0,"colspan":1,"rowspan":2,"value":"花色"},{"dataType":0,"colspan":1,"rowspan":2,"value":"锁具"},{"dataType":0,"colspan":1,"rowspan":2,"value":"开向"},{"dataType":0,"colspan":1,"rowspan":2,"value":"其他"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初数量"},{"dataType":0,"colspan":1,"rowspan":2,"value":"本月单价"},{"dataType":0,"colspan":1,"rowspan":2,"value":"期初金额"},{"dataType":0,"colspan":2,"value":"调拨收入"},{"dataType":0,"colspan":2,"value":"暂存收入"},{"dataType":0,"colspan":2,"value":"铺货收入"},{"dataType":0,"colspan":2,"value":"送货收回"},{"dataType":0,"colspan":2,"value":"其他收入"},{"dataType":0,"colspan":2,"value":"直发销售支出"},{"dataType":0,"colspan":2,"value":"库房销售支出"},{"dataType":0,"colspan":2,"value":"调拨支出"},{"dataType":0,"colspan":2,"value":"暂存支出"},{"dataType":0,"colspan":2,"value":"铺货支出"},{"dataType":0,"colspan":2,"value":"送货支出"},{"dataType":0,"colspan":2,"value":"其他支出"},{"dataType":0,"rowspan":2,"value":"数量"},{"dataType":0,"rowspan":2,"value":"金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"},{"dataType":0,"colspan":1,"value":"数量"},{"dataType":0,"colspan":1,"value":"金额"}]},{"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"0"}]}]}';
        $dataJson = json_decode($temp_json,true);
        foreach($data as $kk=>$vv){
            $dataJson['data']['3']['tr'][12]['value'] += $vv['qichuje'];
            $dataJson['data']['3']['tr'][14]['value'] += $vv['je1'];
            $dataJson['data']['3']['tr'][16]['value'] += $vv['je2'];
            $dataJson['data']['3']['tr'][18]['value'] += $vv['je3'];
            $dataJson['data']['3']['tr'][20]['value'] += $vv['je4'];
            $dataJson['data']['3']['tr'][22]['value'] += $vv['je5'];
            $dataJson['data']['3']['tr'][24]['value'] += $vv['je6'];
            $dataJson['data']['3']['tr'][26]['value'] += $vv['je7'];
            $dataJson['data']['3']['tr'][28]['value'] += $vv['je8'];
            $dataJson['data']['3']['tr'][30]['value'] += $vv['je9'];
            $dataJson['data']['3']['tr'][32]['value'] += $vv['je10'];
            $dataJson['data']['3']['tr'][34]['value'] += $vv['je11'];
            $dataJson['data']['3']['tr'][36]['value'] += $vv['je12'];
            $dataJson['data']['3']['tr'][38]['value'] += $vv['je13'];
            $json ='{"tr":[
                {"dataType":0,"colspan":1,"value":"'.$vv['zhizaobm'].'"},{"dataType":0,"colspan":1,"value":"'.$vv['dalei'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['banhou'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['guige'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['biaomianyq'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['menkuang'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['huase'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['suoju'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['kaixiang'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qita'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qcsl'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['benyuedj'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['qichuje'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['dbsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je1'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['zcsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je2'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['phsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je3'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['shsh'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je4'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['qtsr'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je5'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['zfxszc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je6'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['kfxszc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je7'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['dbzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je8'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['zczc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je9'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['phzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je10'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['shzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je11'].'"},                {"dataType":1,"colspan":1,"value":"'.$vv['qtzc'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je12'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['jiecun'].'"},                {"dataType":0,"colspan":1,"value":"'.$vv['je13'].'"}                ]}';
            $dataJson['data'][]=json_decode($json,true);
        }
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$dataJson['data']),'json');
    }
}