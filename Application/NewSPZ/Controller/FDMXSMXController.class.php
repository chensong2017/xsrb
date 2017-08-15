<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 17:42
 */
namespace NewSPZ\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class FDMXSMXController extends RestController{
    //导入csv文件
    function load_csv($handle) {
        $out = array ();
        $n = 0;
        while ($data = fgetcsv($handle, 10000)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i++) {
                $out[$n][$i] = $data[$i];
            }
            $n++;
        }
        return $out;
    }
    //csv编码
    function array_iconv($arr, $in_charset="gbk", $out_charset="utf-8"){
        $ret = eval('return '.iconv($in_charset,$out_charset,var_export($arr,true).';'));
        return $ret;
    }
    //防盗门销售明细导入
    public function loadingExcelXsmx($token='',$types=1){
        header("Access-Control-Allow-Origin: *");
        $yue = date("Y-m",strtotime(TODAY));
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $file = $_FILES['file'] ['name'];
        $filetempname = $_FILES ['file']['tmp_name'];
        $filePath = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/excel/';
        $filename = explode(".", $file);//把上传的文件名以“.”做一个数组。
        $time = date("YmdHis");
        $filename [0] = $time.$dept;//取文件名t替换
        $name = implode(".", $filename); //上传后的文件名
        $uploadfile = $filePath . $name;
        $sql ='';
        $result=move_uploaded_file($filetempname,$uploadfile);
        if($result){
            //目前程序只处理 csv,xls,xlsx的文件数据
            if ($filename[1] == 'xls' || $filename[1] == 'xlsx'){
                $objPHPExcel = \PHPExcel_IOFactory::load($uploadfile);
                $sheet = $objPHPExcel->getSheet(0);
                $Row = $sheet->getHighestRow(); // 总行数
                $today = date('Y-m-d',strtotime(TODAY));
                $start = 4;
            }elseif($filename[1] == 'csv'){
                $handle = fopen($uploadfile, 'r');
                $arr = $this->load_csv($handle); //解析csv
                $objPHPCsv = $this->array_iconv($arr);
                $today = date('n月j日',strtotime(TODAY));
                $start = 3;
                $Row = count($objPHPCsv);
            }else{
                $this->response(array('resultcode'=>-1,'resultmsg'=>'文件格式错误!'),'json');
            }

            //需要处理的字段名称
            $title = array(
                '日期','电话','商品简称','制造部门','订单类别','档次','门框','框厚','前板厚','后板厚','底框材料',
                '门扇','规格','开向','铰链','花色','表面方式','表面要求','窗花','猫眼','标牌',
                '主锁','副锁','锁把','标件','包装品牌','包装方式','其他','销售模式','销量',
                '销售单价','成本单价','调拨运费','欠款额','提货方式','经手人'
            );
            //数据库字段模版
            $title_y = array(
                'date','phone','shangpinjc','zhizaobm','dingdanlb','dangci','menkuang','kuanghou','qianbanhou','houbanhou','dikuangcl',
                'menshan','guige','kaixiang','jiaolian','huase','biaomianfs','biaomianyq','chuanghua','maoyan','biaopai',
                'zhusuo','fusuo','suoba','biaojian','baozhuangpp','baozhuangfs','qita','xiaoshoums','xiaoliang',
                'xiaoshoudj','chengbendj','diaobofy','qiankuane','tihuofs','jingshour'
            );
            //excel数据模版
            $title_x = array(
                'A','H','I','J','K','L','M','N','O','P','Q',
                'R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD',
                'AE','AF','AG','AH','AI','AJ',
                'AK','AL','AQ','AR','AS','AT'
            );
            //csv数据模版
            $title_w = array(
                0,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,42,43,44,45
            );
            foreach($title_x as $k=>$v){
                if ($filename[1] != 'csv'){
                    if (excel_trim2($objPHPExcel->getActiveSheet()->getCell($v.'2')->getValue()) !=$title[$k])
                        $this->response(array('resultcode'=>-1,'resultmsg'=>$v.'列不是'.$title[$k]),'json');
                }else{
                    if (excel_trim2($objPHPCsv[1][$title_w[$k]]) !=$title[$k])
                        $this->response(array('resultcode'=>-1,'resultmsg'=>$v.'列不是'.$title[$k]),'json');
                }
            }

            $m = 0;$n =0;
            $sql_concat = "select  product_md5 from new_spzmxqc 
                            where `month` = '$yue' and dept =$dept and `type` =$types";
            $qc_temp = M()->query($sql_concat);
            foreach ($qc_temp as $vtemp){
                $temp1[] = $vtemp['product_md5'];
            }

            //产品信息-基础数据
            $sql_check = "select zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,
                            menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,
                            zhusuo,fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita from new_spzmx_list ";
            $re = M()->query($sql_check);
            $temp = array();
            foreach($re as $key=>$val){
                foreach($val as $k1=>$v1){
                    if (empty($v1))
                        continue;
                    if ( !in_array($temp[$k1],$v1))
                        $temp[$k1][] =excel_trim2($v1);
                }
            }
            $temp['xsms'] = array('直发销售','库房销售');
            $temp['thfs'] = array('自提','公司直发','客户直发','托运','送货');
            for ($j = $start; $j <= $Row; $j++) {     //行数循环
                $m ++;
                if($filename[1] != 'csv')
                    $input_date = date("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue()));
                else{
                    $input_date = $objPHPCsv[$j][0];
                    if($today != $input_date)
                        $input_date = date('n月j日',strtotime($input_date));
                }
                if ($today == $input_date){
                    foreach ($title_y as $ky=>$vy){
                        if ($filename[1] != 'csv')
                            $$vy =excel_trim2($objPHPExcel->getActiveSheet()->getCell($title_x[$ky].$j)->getValue());
                        else
                            $$vy =excel_trim2($objPHPCsv[$j][$title_w[$ky]]);
                        $$vy = empty($$vy)?'':$$vy;
                    }
                    if ($filename[1] != 'csv'){
                        $user = array(
                            'phone'=>$phone,
                            'name'=>$objPHPExcel->getActiveSheet()->getCell('B'.$j)->getValue(),
                            'level'=>$objPHPExcel->getActiveSheet()->getCell('C'.$j)->getValue(),
                            'addr_province'=>excel_trim2($objPHPExcel->getActiveSheet()->getCell('D'.$j)->getValue()),
                            'addr_city'=>excel_trim2($objPHPExcel->getActiveSheet()->getCell('E'.$j)->getValue()),
                            'addr_country'=>excel_trim2($objPHPExcel->getActiveSheet()->getCell('F'.$j)->getValue()),
                            'addr_detail'=>$objPHPExcel->getActiveSheet()->getCell('G'.$j)->getValue(),
                            'dept_id'=>$dept,
                            'created_by'=>$userinfo['user_name'],
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                    }else{
                        $user = array(
                            'phone'=>$phone,
                            'name'=>$objPHPCsv[$j][1],
                            'level'=>$objPHPCsv[$j][2],
                            'addr_province'=>excel_trim2($objPHPCsv[$j][3]),
                            'addr_city'=>excel_trim2($objPHPCsv[$j][4]),
                            'addr_country'=>excel_trim2($objPHPCsv[$j][5]),
                            'addr_detail'=>$objPHPCsv[$j][6],
                            'dept_id'=>$dept,
                            'created_by'=>$userinfo['user_name'],
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                    }
                    $xiaoliang  = empty($xiaoliang)?0:$xiaoliang;
                    $xiaoshoudj = empty($xiaoshoudj)?0:$xiaoshoudj;
                    $chengbendj = empty($chengbendj)?0:$chengbendj;
                    $diaobofy   = empty($diaobofy)?0:$diaobofy;
                    $qiankuane  = empty($qiankuane)?0:$qiankuane;
                    $guige = str_replace('×','*',$guige);

                    $cpxx = $zhizaobm.$dingdanlb.$dangci.$menkuang.floatval($kuanghou).floatval($qianbanhou).floatval($houbanhou).$dikuangcl.
                        $menshan.$guige.$kaixiang.$jiaolian.$huase.$biaomianfs.$biaomianyq.$chuanghua.
                        $maoyan.$biaopai.$zhusuo.$fusuo.$suoba.$biaojian.$baozhuangpp.$baozhuangfs.excel_trim2($qita);
                    //$k30= in_array($shangpinjc,$temp['shangpinjc'])?1:0;
                    $k1= in_array($zhizaobm,$temp['zhizaobm'])?1:0;
                    $k2= in_array($dingdanlb,$temp['dingdanlb'])?1:0;
                    $k3= in_array($dangci,$temp['dangci'])?1:0;
                    $k4= in_array($menkuang,$temp['menkuang'])?1:0;
                    $k27= in_array($kuanghou,$temp['kuanghou'])?1:0;
                    $k5= in_array($qianbanhou,$temp['qianbanhou'])?1:0;
                    $k6= in_array($houbanhou,$temp['houbanhou'])?1:0;
                    $k7= in_array($dikuangcl,$temp['dikuangcl'])?1:0;
                    $k8= in_array($menshan,$temp['menshan'])?1:0;
                    $k9= in_array($guige,$temp['guige'])?1:0;

                    $k10= in_array($kaixiang,$temp['kaixiang'])?1:0;
                    $k11= in_array($jiaolian,$temp['jiaolian'])?1:0;
                    $k12= in_array($huase,$temp['huase'])?1:0;
                    $k13= in_array($biaomianfs,$temp['biaomianfs'])?1:0;
                    $k14= in_array($biaomianyq,$temp['biaomianyq'])?1:0;
                    $k28= in_array($chuanghua,$temp['chuanghua'])?1:0;
                    $k15= in_array($maoyan,$temp['maoyan'])?1:0;
                    $k16= in_array($biaopai,$temp['biaopai'])?1:0;
                    $k17= in_array($zhusuo,$temp['zhusuo'])?1:0;
                    $k18= in_array($fusuo,$temp['fusuo'])?1:0;
                    $k19= in_array($suoba,$temp['suoba'])?1:0;
                    $k20= in_array($biaojian,$temp['biaojian'])?1:0;
                    $k21= in_array($baozhuangpp,$temp['baozhuangpp'])?1:0;
                    $k22= in_array($baozhuangfs,$temp['baozhuangfs'])?1:0;
                    $k23= in_array($qita,$temp['qita'])?1:0;

                    $k23= is_numeric($xiaoliang)?1:0;
                    $k24= is_numeric($xiaoshoudj)?1:0;

                    $k25= in_array($xiaoshoums,$temp['xsms'])?1:0;
                    $k26 = in_array($tihuofs,$temp['thfs'])?1:0;

                    $sql_shengshixian = "select * from xsrb_kehugrade WHERE province = '".$user['addr_province']."' and 
                                city = '".$user['addr_city']."' and country = '".$user['addr_country']."'";
                    $result_grade = M()->query($sql_shengshixian);
                    $k30 = empty(count($result_grade))?0:1;
                    if (in_array(md5($cpxx.$dept.$yue.$types),$temp1) && is_numeric($xiaoliang) && is_numeric($xiaoshoudj) && is_numeric($diaobofy) && is_numeric($qiankuane) && $k25 && $k26 && $k30){
                        $n ++;
                        $re_phone = M('xsrb_customer_info')->where("phone ='$phone' and dept_id =$dept")->find();
                        if (!count($re_phone)){
                            $user['level'] = $result_grade[0]['grade'];
                            M('xsrb_customer_info')->add($user);
                        }
                        $sql .= '{"phone":"'.$phone.'","product_md5":"'.md5($cpxx.$dept.$yue.$types).'",
                                "xiaoshoums":"'.$xiaoshoums.'","xiaoliang":"'.$xiaoliang.'",
                                "xiaoshoudj":"'.$xiaoshoudj.'","chengbendj":'.$chengbendj.',"xiaoshousr":0,
                                "xiaoshoucb":0,"maoli":0,"maolil":0,"diaobofy":'.$diaobofy.',
                                "qiankuane":'.$qiankuane.',"tihuofs":"'.$tihuofs.'","jingshour":"'.$jingshour.'"},';
                        unset($strs);
                    }else{
                        $code = '';
                        // $code .= $k30?'':(empty($shangpinjc)?'商品简称列不能为空；':"商品简称:[".$shangpinjc."]不存在；");
                        $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                        $code .= $k2?'':(empty($dingdanlb)?'':"订单类别:[".$dingdanlb."]不存在；");
                        $code .= $k3?'':(empty($dangci)?'':"档次:[".$dangci."]不存在；");
                        $code .= $k4?'':(empty($menkuang)?'':"门框:[".$menkuang."]不存在；");
                        $code .= $k5?'':(empty($qianbanhou)?'':"前板厚:[".$qianbanhou."]不存在；");
                        $code .= $k6?'':(empty($houbanhou)?'':"后板厚:[".$houbanhou."]不存在；");
                        $code .= $k7?'':(empty($dikuangcl)?'':"底框材料:[".$dikuangcl."]不存在；");
                        $code .= $k8?'':(empty($menshan)?'':"门扇:[".$menshan."]不存在；");

                        $code .= empty($guige)?'':'';

                        $code .= $k10?'':(empty($kaixiang)?'':"开向:[".$kaixiang."]不存在；");
                        $code .= $k11?'':(empty($jiaolian)?'':"铰链:[".$jiaolian."]不存在；");
                        $code .= $k12?'':(empty($huase)?'':"花色:[".$huase."]不存在；");
                        $code .= $k13?'':(empty($biaomianfs)?'':"表面方式:[".$biaomianfs."]不存在；");
                        $code .= $k14?'':(empty($biaomianyq)?'':"表面要求:[".$biaomianyq."]不存在；");
                        $code .= $k15?'':(empty($maoyan)?'':"猫眼:[".$maoyan."]不存在；");
                        $code .= $k16?'':(empty($biaopai)?'':"标牌:[".$biaopai."]不存在；");
                        $code .= $k17?'':(empty($zhusuo)?'':"主锁:[".$zhusuo."]不存在；");
                        $code .= $k18?'':(empty($fusuo)?'':"副锁:[".$fusuo."]不存在；");
                        $code .= $k19?'':(empty($suoba)?'':"锁把:[".$suoba."]不存在；");
                        $code .= $k20?'':(empty($biaojian)?'':"标件:[".$biaojian."]不存在；");
                        $code .= $k21?'':(empty($baozhuangpp)?'':"包装品牌:[".$baozhuangpp."]不存在；");
                        $code .= $k22?'':(empty($baozhuangfs)?'':"包装方式:[".$baozhuangfs."]不存在；");
                        $code .= $k23?'':(empty($qita)?'':"其他:[".$qita."]不存在；");
                        $code .= $k27?'':(empty($kuanghou)?'':"框厚:[".$kuanghou."]不存在；");
                        $code .= $k28?'':(empty($chuanghua)?'':"窗花:[".$chuanghua."]不存在；");


                        $teshumen = array('钢木门','强化门','卫浴门','单板门','木门');
                        if (!in_array($dangci,$teshumen)){  //特殊大类处理
                            $code .= $k4?'':(empty($menkuang)?'':"门框:[".$menkuang."]不存在；");
                            $code .= $k17?'':(empty($zhusuo)?'':"主锁:[".$zhusuo."]不存在；");
                        }

                        $code .= $k25?'':(empty($xiaoshoums)?'销售模式列不能为空；':"销售模式:[".$xiaoshoums."]不存在；");
                        $code .= $k26?'':(empty($tihuofs)?'提货方式列不能为空；':"提货方式:[".$tihuofs."]不存在；");
                        $code .= is_numeric($xiaoshoudj)?'':'销售单价非数字';
                        $code .= is_numeric($diaobofy)?'':'调拨费用非数字';
                        $code .= is_numeric($qiankuane)?'':'欠款额非数字';
                        $code .= $k30?'':'省市县数据不正确；';
                        if (empty($code)){
                            $code = '该产品信息行在期初中未录入!';
                        }
                        $teshuzhizaobm = array('舍零','返利','送货运费收入');
                        if (in_array($zhizaobm,$teshuzhizaobm)){
                            $code = '该门套产品信息行在期初中未录入!';
                            $k1=1;$k2=1;$k3=1;$k4=1;$k5=1;$k6=1;$k7=1;$k8=1;$k9=1;$k10=1;$k11=1;$k12=1;$k13=1;$k14=1;
                            $k15=1;$k16=1;$k17=1;$k18=1;$k19=1;$k20=1;$k21=1;$k22=1;$k23=1;$k24=1;$k25=1;
                        }
                        if ($filename[1] != 'csv'){
                            $hang = $j;
                        }else
                            $hang = $j+1;
                        $error[] = array(
                            array('type'=>1,'value'=>$hang),
                            array('type'=>1,'value'=>$today),
                            array('type'=>1,'value'=>$phone),
                            //array('type'=>$k30,'value'=>$shangpinjc),
                            array('type'=>$k1,'value'=>$zhizaobm),
                            array('type'=>$k2,'value'=>$dingdanlb),
                            array('type'=>$k3,'value'=>$dangci),
                            array('type'=>$k4,'value'=>$menkuang),
                            array('type'=>$k24,'value'=>$kuanghou),
                            array('type'=>$k5,'value'=>$qianbanhou),
                            array('type'=>$k6,'value'=>$houbanhou),
                            array('type'=>$k7,'value'=>$dikuangcl),
                            array('type'=>$k8,'value'=>$menshan),
                            array('type'=>empty($guige)?0:1,'value'=>$guige),
                            array('type'=>$k10,'value'=>$kaixiang),
                            array('type'=>$k11,'value'=>$jiaolian),
                            array('type'=>$k12,'value'=>$huase),
                            array('type'=>$k13,'value'=>$biaomianfs),
                            array('type'=>$k14,'value'=>$biaomianyq),
                            array('type'=>$k14,'value'=>$chuanghua),
                            array('type'=>$k15,'value'=>$maoyan),
                            array('type'=>$k16,'value'=>$biaopai),
                            array('type'=>$k17,'value'=>$zhusuo),
                            array('type'=>$k18,'value'=>$fusuo),
                            array('type'=>$k19,'value'=>$suoba),
                            array('type'=>$k20,'value'=>$biaojian),
                            array('type'=>$k21,'value'=>$baozhuangpp),
                            array('type'=>$k22,'value'=>$baozhuangfs),
                            array('type'=>1,'value'=>$qita),
                            array('type'=>$k25,'value'=>$xiaoshoums),
                            array('type'=>1,'value'=>$xiaoliang),
                            array('type'=>1,'value'=>$xiaoshoudj),
                            array('type'=>1,'value'=>$chengbendj),
                            array('type'=>1,'value'=>$diaobofy),
                            array('type'=>1,'value'=>$qiankuane),
                            array('type'=>$k26,'value'=>$tihuofs),
                            array('type'=>1,'value'=>$jingshour),
                            array('type'=>0,'value'=>$code)
                        );
                    }
                }
            }
            unlink($uploadfile);
            if ($sql !=''){
                $json = '{"info": ['.trim($sql,',').']}';
                $this->submit('',0,$dept,$json);
                $this->response(array('resultcode'=>-1,'resultmsg'=>"本次上传总数据:$m,成功:$n;",'error'=>$error),'json');
            }else{
                $this->response(array('resultcode'=>-1,'resultmsg'=>"没有本日数据;",'error'=>$error),'json');
            }
        }
    }
    //防盗门商品销售明细查询
    public function search($token='',$sdate='',$edate =''){
        header("Access-Control-Allow-Origin: *");
        $yue = date('Y-m',strtotime(TODAY));
        if (empty($sdate))
            $sdate = date('Ym01',strtotime(TODAY));
        if (empty($edate))
            $edate = TODAY;

        if (date('Ym',strtotime($sdate)) != date('Ym',strtotime($edate))){
            $this->response(array('resultcode'=>-1,'resultmsg'=>"不能跨月查询!"),'json');
        }
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        //获取期初,匹配是否期初被清理过
        $sql_qc = "select concat(shangpinjc,
                            zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,
                            menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,
                            fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita)as temp from new_spzmxqc 
                            where `month` = '$yue' and dept =$dept and type =1";
        $qc = M()->query($sql_qc);
        //组合匹配的数组
        foreach($qc as $vqc){
            $temp[] = $vqc['temp'];
        }
        //按条件查询部门的销售明细数据
        $sql_info = " select * ,0 as xiaoshousr,0 as xiaoshoucb,0 as maoli,0 as maolil from 
                      (new_xsmx a left join xsrb_customer_info b on a.phone=b.phone and b.dept_id=a.dept)
                      left join new_spzmxqc c on a.product_md5=c.product_md5  
                      where a.date BETWEEN '$sdate' and '$edate' and a.dept = $dept and a.type =1 ";
        $info = M()->query($sql_info);
        //计算毛利
        $heji = array('xiaoliang'=>0,'xiaoshousr'=>0,'xiaoshoucb'=>0,'maoli'=>0,'maolil'=>0,'diaobofy'=>0,'qiankuane'=>0);
        foreach($info as $k => $v){
            $info[$k]['xiaoshousr'] = $v['xiaoliang']*$v['xiaoshoudj'];
            $info[$k]['xiaoshoucb'] = $v['xiaoliang']*$v['chengbendj'];
            $info[$k]['maoli'] = bcsub($v['xiaoshoudj'],$v['chengbendj'],2)*$v['xiaoliang'];
            if ($v['xiaoliang']*$v['xiaoshoudj'] ==0)       //计算当行毛利率
                $info[$k]['maolil'] = 0;
            else
                $info[$k]['maolil'] = round($info[$k]['maoli']/($v['xiaoliang']*$v['xiaoshoudj']),7);

            $heji['xiaoliang'] +=$v['xiaoliang'] ;
            $heji['xiaoshousr'] += $v['xiaoliang']*$v['xiaoshoudj'];
            $heji['xiaoshoucb'] += $v['xiaoliang']*$v['chengbendj'];
            $heji['maoli'] += $info[$k]['maoli'];
            if ($heji['xiaoshousr'] ==0)        //计算合计毛利率
                $heji['maolil'] = 0;
            else
                $heji['maolil'] = round($heji['maoli']/($heji['xiaoshousr']),7);
            $heji['diaobofy'] += $v['diaobofy'];
            $heji['qiankuane'] += $v['qiankuane'];

            $cpxx = $v['shangpinjc'].$v['zhizaobm'].$v['dingdanlb'].$v['dangci'].$v['menkuang'].$v['kuanghou'].$v['qianbanhou'].
                $v['houbanhou'].$v['dikuangcl'].$v['menshan'].$v['guige'].$v['kaixiang'].$v['jiaolian'].
                $v['huase'].$v['biaomianfs'].$v['biaomianyq'].$v['chuanghua'].$v['maoyan'].$v['biaopai'].
                $v['zhusuo'].$v['fusuo'].$v['suoba'].$v['biaojian'].$v['baozhuangpp'].$v['baozhuangfs'].$v['qita'];
            //对期初被清理过的销售明细进行提示
            if (!in_array($cpxx,$temp)){
                $data['error'][] = array(
                    array('type'=>1,'value'=>$k+1),
                    array('type'=>0,'value'=>$v['date']),
                    array('type'=>0,'value'=>$v['shangpinjc']),
                    array('type'=>0,'value'=>$v['zhizaobm']),
                    array('type'=>0,'value'=>$v['dingdanlb']),
                    array('type'=>0,'value'=>$v['dangci']),
                    array('type'=>0,'value'=>$v['menkuang']),
                    array('type'=>0,'value'=>$v['kuanghou']),
                    array('type'=>0,'value'=>$v['qianbanhou']),
                    array('type'=>0,'value'=>$v['houbanhou']),
                    array('type'=>0,'value'=>$v['dikuangcl']),
                    array('type'=>0,'value'=>$v['menshan']),
                    array('type'=>0,'value'=>$v['guige']),
                    array('type'=>0,'value'=>$v['kaixiang']),
                    array('type'=>0,'value'=>$v['jiaolian']),
                    array('type'=>0,'value'=>$v['huase']),
                    array('type'=>0,'value'=>$v['biaomianfs']),
                    array('type'=>0,'value'=>$v['biaomianyq']),
                    array('type'=>0,'value'=>$v['chuanghua']),
                    array('type'=>0,'value'=>$v['maoyan']),
                    array('type'=>0,'value'=>$v['biaopai']),
                    array('type'=>0,'value'=>$v['zhusuo']),
                    array('type'=>0,'value'=>$v['fusuo']),
                    array('type'=>0,'value'=>$v['suoba']),
                    array('type'=>0,'value'=>$v['biaojian']),
                    array('type'=>0,'value'=>$v['baozhuangpp']),
                    array('type'=>0,'value'=>$v['baozhuangfs']),
                    array('type'=>0,'value'=>$v['qita']),
                    array('type'=>0,'value'=>'该明细行所对应的期初产品信息不存在!')
                );
            }
        }
        //显示title
        $title = json_decode('{"title":[{"tr":[
        {"dataType":0,"colspan":8,"value":"客户信息"},{"dataType":0,"colspan":26,"value":"产品信息"},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":8,"value":"销售盈亏"},
        {"dataType":0,"colspan":3,"value":"责任信息"}]},
        {"tr":[{"dataType":0,"colspan":1,"value":"日期"},{"dataType":0,"colspan":1,"value":"客户姓名"},
        {"dataType":0,"colspan":1,"value":"客户级别"},{"dataType":0,"colspan":1,"value":"省(直辖市)"},
        {"dataType":0,"colspan":1,"value":"地(市)"},{"dataType":0,"colspan":1,"value":"县(市)"},
        {"dataType":0,"colspan":1,"value":"乡镇"},{"dataType":0,"colspan":1,"value":"电话"},
        {"dataType":0,"colspan":1,"value":"商品简称"},{"dataType":0,"colspan":1,"value":"制造部门"},
        {"dataType":0,"colspan":1,"value":"订单类别"},{"dataType":0,"colspan":1,"value":"档次"},
        {"dataType":0,"colspan":1,"value":"门框"},{"dataType":0,"colspan":1,"value":"框厚"},
        {"dataType":0,"colspan":1,"value":"前板厚"},{"dataType":0,"colspan":1,"value":"后板厚"},
        {"dataType":0,"colspan":1,"value":"底框材料"},{"dataType":0,"colspan":1,"value":"门扇"},
        {"dataType":0,"colspan":1,"value":"规格"},{"dataType":0,"colspan":1,"value":"开向"},
        {"dataType":0,"colspan":1,"value":"铰链"},{"dataType":0,"colspan":1,"value":"花色"},
        {"dataType":0,"colspan":1,"value":"表面方式"},{"dataType":0,"colspan":1,"value":"表面要求"},
        {"dataType":0,"colspan":1,"value":"窗花"},{"dataType":0,"colspan":1,"value":"猫眼"},
        {"dataType":0,"colspan":1,"value":"标牌"},{"dataType":0,"colspan":1,"value":"主锁"},
        {"dataType":0,"colspan":1,"value":"副锁"},{"dataType":0,"colspan":1,"value":"锁把"},
        {"dataType":0,"colspan":1,"value":"标件"},{"dataType":0,"colspan":1,"value":"包装品牌"},
        {"dataType":0,"colspan":1,"value":"包装方式"},{"dataType":0,"colspan":1,"value":"其他"},
        {"dataType":0,"colspan":1,"value":"销售模式"},        
        {"dataType":0,"colspan":1,"value":"销量"},{"dataType":0,"colspan":1,"value":"销售单价"},
        {"dataType":0,"colspan":1,"value":"成本单价"},{"dataType":0,"colspan":1,"value":"销售收入"},
        {"dataType":0,"colspan":1,"value":"销售成本"},{"dataType":0,"colspan":1,"value":"毛利"},
        {"dataType":0,"colspan":1,"value":"毛利率"},{"dataType":0,"colspan":1,"value":"调拨费用"},
        {"dataType":0,"colspan":1,"value":"欠费额"},{"dataType":0,"colspan":1,"value":"提货方式"},
        {"dataType":0,"colspan":1,"value":"经手人"}]},
        {"tr":[{"dataType":0,"colspan":1,"value":""},
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
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":"'.$heji['xiaoliang'].'"},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":"'.$heji['xiaoshousr'].'"},
        {"dataType":0,"colspan":1,"value":"'.$heji['xiaoshoucb'].'"},
        {"dataType":0,"colspan":1,"value":"'.$heji['maoli'].'"},
        {"dataType":0,"colspan":1,"value":"'.$heji['maolil'].'"},
        {"dataType":0,"colspan":1,"value":"'.$heji['diaobofy'].'"},
        {"dataType":0,"colspan":1,"value":"'.$heji['qiankuane'].'"},
        {"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""}]}]}',true);

        //筛选销售模式,提货方式
        $data['list'] = array(
            //array('label'=>'dingdanlb','name'=>'订单类别','option'=>array('直接工程订单','经销商工程订单','经销商招商订单','常规订单','工程样品订单')),//其他
            array('label'=>'xiaoshoums','name'=>'销售模式','option'=>array('库房销售','直发销售')),//其他
            array('label'=>'tihuofs','name'=>'提货方式','option'=>array('公司直发','客户直发','托运','自提','送货'))//其他
        );
        $data['info'] = $info;
        $data['title'] = $title['title'];
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$data),'json');
    }

    //防盗门商品销售录入
    public function submit($token='',$delete =0,$dept_id='',$data='')
    {
        header("Access-Control-Allow-Origin: *");
        if (empty($dept_id)){
            $userinfo = checktoken($token);
            if (!$userinfo) {
                $this->response(retmsg(-2), 'json');
                return;
            }
            $dept = $userinfo['dept_id'];
            $json = file_get_contents("php://input");   //获取保存提交的数据
        }else{
            $dept = $dept_id;
            $json = $data;
        }
        $date = TODAY;
        $type=1;
        $yue = date("Y-m",strtotime(TODAY));
        if ($delete ==1){
            M()->execute("delete from new_xsmx where  dept = $dept and `date`='$date'");
        }
        M()->execute("update new_spzmx set jiecun = jiecun +kfxszc+zfxszc,kfxszc=0,zfxszc=0 where dept =$dept and `type`=1 and `date`='$date'");
        $data = json_decode($json,true);
        foreach($data['info'] as $k => $v ){
            if($k == 0 && $v['zhizaobm'] =='必填列')
            {}
            else{
                $dj_arr = array(
                    'date'=>date('Y-m',strtotime($date)),
                    'product_md5'=>$v['product_md5'],
                    'dept'=>$dept
                );
                $dj = M('new_spzmxqc')->where($dj_arr)->select();
                $arrAll[] = array(
                    'dept'=>$dept,
                    'type'=>$type,
                    'date'=>$date,
                    'phone'=>$v['phone'],
                    'product_md5'=>$v['product_md5'],
                    'xiaoshoums'=>$v['xiaoshoums'],
                    'xiaoliang'=>$v['xiaoliang'],
                    'xiaoshoudj'=>$v['xiaoshoudj'],
                    'chengbendj'=>$dj[0]['benyuedj'],
                    'diaobofy'=>$v['diaobofy'],
                    'qiankuane'=>$v['qiankuane'],
                    'tihuofs'=>$v['tihuofs'],
                    'jingshour'=>$v['jingshour']
                );
                //库房销售,直发销售 数据处理
                if ($v['xiaoshoums'] =='库房销售')
                    $info[$v['product_md5']]['kfxs'] += $v['xiaoliang'];
                else
                    $info[$v['product_md5']]['zfxs'] += $v['xiaoliang'];
            }
        }
        foreach($info as $kinfo =>$vinfo){
            //查询商品帐明细表
            $vinfo['zfxs'] = empty($vinfo['zfxs'])?0:$vinfo['zfxs'];
            $vinfo['kfxs'] = empty($vinfo['kfxs'])?0:$vinfo['kfxs'];
            $where = "where  dept =$dept and `type` =1 and `date` ='$date' and  product_md5 = '$kinfo'";
            $result = M()->query("select dept from new_spzmx $where ");
            if (count($result)){
                //更新商品帐明细表
                $ssss = "update new_spzmx set  kfxszc =".$vinfo['kfxs']." ,zfxszc = ".$vinfo['zfxs'].",
                         jiecun =dbsr+zcsr+phsr+shsh+qtsr-dbzc-phzc-shzc-qtzc-zczc-".$vinfo['zfxs']."-".$vinfo['kfxs']." $where ";
                M()->execute($ssss);
            }else{
                M()->execute("insert INTO new_spzmx(`type`,`dept`,`date`,product_md5,zfxszc,kfxszc,jiecun) 
                Values($type,$dept,'$date','$kinfo',".$vinfo['zfxs'].",".$vinfo['kfxs'].",".(-$vinfo['zfxs']-$vinfo['kfxs']).")");
            }
        }
        if (!empty($arrAll)){
            M()->execute("delete from new_xsmx where `type`=$type and dept = $dept and `date`='$date'");
            M('new_xsmx')->addAll($arrAll);
            $check_pd = M()->query("select flag from new_spzpd where dept =$dept and `type`=$type and yuefen ='$yue' and edate ='$date' ");
            if (count($check_pd)){  //当天盘点过后,再次录入防盗门销售明细数据.flag设置为0
                M()->execute("update new_spzpd set flag =0,createtime=now() where dept =$dept and `type`=$type and edate ='$date' order by createtime desc limit 1");
            }
        }
        if (empty($dept_id))
            $this->response(array('resultcode'=>0,'resultmsg'=>'保存成功!'),'json');
        else
            return true;
    }
    //防盗门商品销售查询--csv格式
    public function printExcel($token='',$date=TODAY,$pageSize=40){
        set_time_limit(90);
        header("Access-Control-Allow-Origin: *");
        $Model = M();
//        验证token:根据token获取用户的dept_id、pid
        $userinfo = checktoken($token);
        if(!$userinfo){
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        $pid = $userinfo['pid'];

        if ($date == '')
        {
            $date = date("Y-m-d");
        }
        $page = 1;
        // 响应数据
        $data = array();
        $data['page'] = $page;

        // 分权限查询部门数据
        $sql_total = "select count(*) as total from xsrb_department where ";
        $sql_data = "select t1.dname as dname,t1.id as id,t2.dname as pname from xsrb_department t1,xsrb_department t2 where t1.pid=t2.id ";
        // 如果为总部查询所有部门
        if ($pid == 0)
        {
            $sql_total .= " id!=1 and pid!=1";//所有部门的总数：247
            $sql_data .= " and t1.id!=1 and t1.pid!=1 ORDER BY t1.pid DESC,t1.qt1 ";//247个部门
        }        // 如果为片区则查出该片区下的所有部门
        elseif ($pid == 1)
        {
            $sql_total .= " pid='$dept_id' ";//四川片区为例：总数40
            $sql_data .= " and t1.pid='$dept_id' ORDER BY t1.pid DESC,t1.qt1 ";//四川片区为例：40个部门
        } // 具体某个部门的数据
        else
        {
            $sql_total .= " id='$dept_id' ";//总数1
            $sql_data .= " and t1.id='$dept_id'  ";//一个部门
        }
        $total = $Model->query($sql_total);
        $total = $total[0]['total'];
        $total_page = ceil($total / $pageSize);
        for($i = 1;$i <= $total_page;$i++){//遍历页
            if ($total < $pageSize)
                $total = 1;
            elseif ($total % $pageSize == 0)
                $total = $total / $pageSize;
            else
                $total = $total / $pageSize + 1;
            $data['total'] = (int) $total;
            $data['page'] = $i;

            //正式数据部分
            $yuechu = date("Y-m-01",strtotime("$date -1 day"));
            $start = $yuechu;
            if ($yuechu == date("Y-m-01"))
                $end = date("Y-m-d",strtotime("$date -1 day"));
            else
                $end = date("Y-m-t",strtotime("$date"));

            $sql_one = $sql_data . " limit " . ($i - 1) * $pageSize . ",$pageSize";
            $result = $Model->query($sql_one);
            foreach ($result as $key => $value){
                $dname = $value['dname'];//当前片区下的所有部门的部门名称
                $pname = $value['pname'];
                $tr  = ",,,,,,,,,,,,,,,,,,,,,,,,\n";
                $tr .= ",,,,,,,,,,,,,,,,,,,,,,,,\n";
                $tr .= ",,,,,,,,,,,,,,,,,,,,,,,,\n";
                $tr .= ",".$dname."---防盗门销售明细表,,,,,,,,,,,,,,,,\n";
                $tr .= ",日期,,,,,客户信息,,,,,,,,,,,,,,产品信息,,,,,,,,,,,,,,,,,,销售盈亏,,,,,,责任信息,\n";
                $tr .= ",日期,客户姓名,客户级别,省(直辖市),地(市),县(市),乡镇,电话,商品简称,制造部门,订单类别,档次,门框,框厚,前板厚,后板厚,底框材料,门扇,规格,开向,铰链,花色,表面方式,表面要求,窗花,猫眼,标牌,主锁,副锁,锁把,标件,包装品牌,包装方式,其他,销售模式,销量,销售单价,成本单价,销售收入,销售成本,毛利,毛利率,调拨费用,欠款额,提货方式,经手人\n";

                $depts = $value['id'];//当前片区下的所有部门的部门id
                //联表查询成本单价----2017-03-20：星期一：go on

                $sql = "select * ,0 as xiaoshousr,0 as xiaoshoucb,0 as maoli,0 as maolil from 
                      (new_xsmx a left join xsrb_customer_info b on a.phone=b.phone and a.dept=b.dept_id)
                      left join new_spzmxqc c on a.product_md5=c.product_md5
                       WHERE a.date BETWEEN '$start' AND '$end' AND a.dept='$depts' ";
                $tempDatas = $Model->query($sql);
                $tempData = array();
                foreach($tempDatas as $v){
                    $tempData[] = array(
                        'date'=>$v['date'],
                        'kehuxm'=>$v['name'],
                        'level'=>$v['level'],
                        'province'=>$v['addr_province'],
                        'city'=>$v['addr_city'],
                        'county'=>$v['addr_country'],
                        'detail'=>$v['addr_detail'],
                        'phone'=>$v['phone'],
                        'shangpinjc'=>$v['shangpinjc'],
                        'zhizaobm'=>$v['zhizaobm'],
                        'dingdanlb'=>$v['dingdanlb'],
                        'dangci'=>$v['dangci'],
                        'menkuang'=>$v['menkuang'],
                        'kuanghou'=>$v['kuanghou'],
                        'qianbanhou'=>$v['qianbanhou'],
                        'houbanhou'=>$v['houbanhou'],
                        'dikuangcl'=>$v['dikuangcl'],
                        'menshan'=>$v['menshan'],
                        'guige'=>$v['guige'],
                        'kaixiang'=>$v['kaixiang'],
                        'jiaolian'=>$v['jiaolian'],
                        'huase'=>$v['huase'],
                        'biaomianfs'=>$v['biaomianfs'],
                        'biaomianyq'=>$v['biaomianyq'],
                        'chuanghua'=>$v['chuanghua'],
                        'maoyan'=>$v['maoyan'],
                        'biaopai'=>$v['biaopai'],
                        'zhusuo'=>$v['zhusuo'],
                        'fusuo'=>$v['fusuo'],
                        'suoba'=>$v['suoba'],
                        'biaojian'=>$v['biaojian'],
                        'baozhuangpp'=>$v['baozhuangpp'],
                        'baozhuangfs'=>$v['baozhuangfs'],
                        'qita'=>$v['qita'],
                        'xiaoshoums'=>$v['xiaoshoums'],
                        'xiaoliang'=>$v['xiaoliang'],
                        'xiaoshoudj'=>$v['xiaoshoudj'],
                        'chengbendj'=>$v['chengbendj'],
                        'diaobofy'=>$v['diaobofy'],
                        'qiankuane'=>$v['qiankuane'],
                        'tihuofs'=>$v['tihuofs'],
                        'jingshour'=>$v['jingshour'],
                    );
                }
                foreach($tempData as $temp_key=>$temp_value){
                    $riqi = $temp_value["date"];
                    $xiaoliang = $temp_value["xiaoliang"];//销量
                    $xiaoshoudj = $temp_value["xiaoshoudj"];//销售单价
                    $chengbendj = $temp_value["chengbendj"];//成本单价

                    $xiaoshousr = $xiaoliang*$xiaoshoudj;//销售收入
                    $xiaoshoucb = $xiaoliang*$chengbendj;//销售成本
                    $maoli = $xiaoshousr-$xiaoshoucb;//毛利
                    $maolilv = $maoli/$xiaoshousr;//毛利率

                    $diaobofy = $temp_value["diaobofy"];//调拨费用
                    $qiankuane = $temp_value["qiankuane"];//欠款额
                    $tihuofs = $temp_value["tihuofs"];//提货方式
                    $jingshou = $temp_value["jingshour"];//经手人

                    $tr .=",".$riqi.",";
                    $temp_value = array_values($temp_value);
                    foreach($temp_value as $arr_key=>$arr_val){
                        if($arr_key > 0 && $arr_key < count($temp_value)-4){
                            $tr.=$arr_val.",";//到成本单价结束
                        }
                    }
                    $tr .= $xiaoshousr.",".$xiaoshoucb.",".$maoli.",".$maolilv.",".$diaobofy.",".$qiankuane.",".$tihuofs.",".$jingshou."\n";
                }
                $tr = iconv("UTF-8","GBK",$tr);
                $time = substr($end,0,7);
                if($pid == 0){
                    $filename = "总部".$time."防盗门商品销售明细.csv";
                }elseif($pid == 1){
                    $filename = $pname.$time."防盗门商品销售明细.csv";
                }else{
                    $filename = $dname.$time."防盗门商品销售明细.csv";
                }
                $this->export_csv($filename,$tr);
            }
        }
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
    //防盗门商品销售查询
}