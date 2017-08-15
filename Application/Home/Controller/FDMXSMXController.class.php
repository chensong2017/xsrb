<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 17:42
 */
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class FDMXSMXController extends RestController{
    //防盗门销售明细导入
    public function loadingExcelXsmx($token='',$types=1){
        header("Access-Control-Allow-Origin: *");
        $date = date('Y-m-d',strtotime(TODAY));
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
            if ($filename[1] != 'xls' && $filename[1] != 'xlsx'){
                $this->response(array('resultcode'=>-1,'resultmsg'=>'文件格式错误!'),'json');
            }
            $objPHPExcel = \PHPExcel_IOFactory::load($uploadfile);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 总行数
            $highestColumn = $sheet->getHighestColumn(); // 总列数
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            if ($objPHPExcel->getActiveSheet()->getCell("A2")->getValue() !='日期')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是日期'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("B2")->getValue() !='客户姓名')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是客户姓名'),'json');

            if ($objPHPExcel->getActiveSheet()->getCell("C2")->getValue() !='制造部门')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是制造部门'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("D2")->getValue() !='大类')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是大类'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("E2")->getValue() !='非标')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是非标'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("F2")->getValue() !='板厚')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是板厚'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("G2")->getValue() !='规格')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是规格'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("H2")->getValue() !='表面要求')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是表面要求'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("I2")->getValue() !='门框')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'I列不是门框'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("J2")->getValue() !='花色')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'J列不是花色'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("K2")->getValue() !='锁具')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'K列不是锁具'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("L2")->getValue() !='开向')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'L列不是开向'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("M2")->getValue() !='其他')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是其他'),'json');

            if($objPHPExcel->getActiveSheet()->getCell("N1")->getValue() !='销售模式')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'N列不是销售模式'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("O2")->getValue() !='销量')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'O列不是销量'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("P2")->getValue() !='销售单价')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'P列不是销售单价'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("V2")->getValue() !='调拨运费')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'V列不是调拨运费'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("W2")->getValue() !='欠款额')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'W列不是欠款额'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("X2")->getValue() !='提货方式')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'X列不是提货方式'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("Y2")->getValue() !='经手人')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'Y列不是经手人'),'json');

            $m = 0;$n =0;
            $qc_temp = M()->query("select concat(zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita)as temp from spzmxqc where `date` = '$yue' and dept =$dept and `type` =$types");
            foreach ($qc_temp as $vtemp){
                $temp1[] = $vtemp['temp'];
            }

            //产品信息-基础数据
            $sql_check = "select zhizaobm,dalei,feibiao,banhou,biaomianyq,menkuang,huase,suoju,kaixiang from spzmx_list ";
            $re = M()->query($sql_check);
            $temp = array();
            foreach($re as $key=>$val){
                foreach($val as $k1=>$v1){
                    if (empty($v1))
                        continue;
                    if ( !in_array($temp[$k1],$v1))
                        $temp[$k1][] = $v1;
                }
            }
            $temp['xsms'] = array('直发销售','库房销售');
            $temp['thfs'] = array('自提','公司直发','客户直发','托运','送货');
            for ($j = 5; $j <= $highestRow; $j++) {     //行数循环
                $m ++;
                if ($date == date("Y-m-d", \PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue())) && !empty($objPHPExcel->getActiveSheet()->getCell("C$j")->getValue())){
                    $khxm = trim(empty($objPHPExcel->getActiveSheet()->getCell("B$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("B$j")->getValue());
                    $zhizaobm = trim(empty($objPHPExcel->getActiveSheet()->getCell("C$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("C$j")->getValue());
                    $dalei = trim(empty($objPHPExcel->getActiveSheet()->getCell("D$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("D$j")->getValue());
                    $feibiao = trim(empty($objPHPExcel->getActiveSheet()->getCell("E$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("E$j")->getValue());
                    $banhou = trim(empty($objPHPExcel->getActiveSheet()->getCell("F$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("F$j")->getValue());
                    $guige = trim(empty($objPHPExcel->getActiveSheet()->getCell("G$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("G$j")->getValue());

                    $banhou = str_replace('×','*',$banhou);
                    $guige = str_replace('×','*',$guige);

                    $biaomianyq = trim(empty($objPHPExcel->getActiveSheet()->getCell("H$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("H$j")->getValue());
                    $menkuang = trim(empty($objPHPExcel->getActiveSheet()->getCell("I$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("I$j")->getValue());
                    $huase =  trim(empty($objPHPExcel->getActiveSheet()->getCell("J$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("J$j")->getValue());
                    $suoju = trim(empty($objPHPExcel->getActiveSheet()->getCell("K$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("K$j")->getValue());
                    $kaixiang =  trim(empty($objPHPExcel->getActiveSheet()->getCell("L$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("L$j")->getValue());
                    $qita =  trim(empty($objPHPExcel->getActiveSheet()->getCell("M$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("M$j")->getValue());
                    $xsms = trim(empty($objPHPExcel->getActiveSheet()->getCell("N$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("N$j")->getValue());
                    $xl =  empty($objPHPExcel->getActiveSheet()->getCell("O$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("O$j")->getValue();
                    $xsdj =  empty($objPHPExcel->getActiveSheet()->getCell("P$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("P$j")->getValue();
                    $cbdj =  empty($objPHPExcel->getActiveSheet()->getCell("Q$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("Q$j")->getValue();
                    $dbfy =  empty($objPHPExcel->getActiveSheet()->getCell("V$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("V$j")->getValue();
                    $qke =  empty($objPHPExcel->getActiveSheet()->getCell("W$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("W$j")->getValue();
                    $thfs =  trim(empty($objPHPExcel->getActiveSheet()->getCell("X$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("X$j")->getValue());
                    $jsr =  trim(empty($objPHPExcel->getActiveSheet()->getCell("Y$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("Y$j")->getValue());
                    $cpxx = $zhizaobm.$dalei.$feibiao.$banhou.$guige.$biaomianyq.$menkuang.$huase.$suoju.$kaixiang.$qita;

                    $k1= in_array($zhizaobm,$temp['zhizaobm'])?1:0;
                    $k2= in_array($dalei,$temp['dalei'])?1:0;
                    $k3= in_array($feibiao,$temp['feibiao'])?1:0;
                    $k4= in_array($banhou,$temp['banhou'])?1:0;
                    $k5= in_array($biaomianyq,$temp['biaomianyq'])?1:0;
                    $k6= in_array($menkuang,$temp['menkuang'])?1:0;
                    $k7= in_array($huase,$temp['huase'])?1:0;
                    $k8= in_array($suoju,$temp['suoju'])?1:0;
                    $k9= in_array($kaixiang,$temp['kaixiang'])?1:0;
                    $k10= is_numeric($xl)?1:0;
                    $k11= is_numeric($xsdj)?1:0;
                    $k12= is_numeric($cbdj)?1:0;
                    $k13= in_array($xsms,$temp['xsms'])?1:0;
                    $k14 = in_array($thfs,$temp['thfs'])?1:0;

                    if (in_array($cpxx,$temp1) && is_numeric($xl) && is_numeric($xsdj) && is_numeric($dbfy) && is_numeric($qke) && $k13 && $k14){
                        $n ++;
                        $sql .= '{"kehuxm":"'.$khxm.'","zhizaobm":"'.$zhizaobm.'","dalei":"'.$dalei.'","feibiao":"'.$feibiao.'","banhou":"'.$banhou.'","guige":"'.$guige.'","biaomianyq":"'.$biaomianyq.'","menkuang":"'.$menkuang.'","huase":"'.$huase.'","suoju":"'.$suoju.'","kaixiang":"'.$kaixiang.'","qita":"'.$qita.'","xiaoshoums":"'.$xsms.'","xiaoliang":'.$xl.',"xiaoshoudj":'.$xsdj.',"chengbendj":'.$cbdj.',"xiaoshousr":0,"xiaoshoucb":0,"maoli":0,"maolil":0,"diaobofy":'.$dbfy.',"qiankuane":'.$qke.',"tihuofs":"'.$thfs.'","jingshou":"'.$jsr.'"},';
                        unset($strs);
                    }else{
                        $code = '';
                        $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                        $code .= $k2?'':(empty($dalei)?'大类列不能为空；':"大类:[".$dalei."]不存在；");
                        $code .= $k3?'':(empty($feibiao)?'非标列不能为空；':"非标:[".$feibiao."]不存在；");
                        $code .= $k4?'':(empty($banhou)?'板厚列不能为空；':"板厚:[".$banhou."]不存在；");
                        $code .= empty($guige)?'规格列不能为空；':'';
                        $code .= $k5?'':(empty($biaomianyq)?'表面要求列不能为空；':"表面要求:[".$biaomianyq."]不存在；");
                        $teshumen = array('钢木门','强化门','卫浴门','单板门','木门');
                        if (!in_array($dalei,$teshumen)){  //特殊大类处理
                            $code .= $k6?'':(empty($menkuang)?'门框列不能为空；':"门框:[".$menkuang."]不存在；");
                            $code .= $k8?'':(empty($suoju)?'锁具列不能为空；':"锁具:[".$suoju."]不存在；");
                        }
                        $code .= $k7?'':(empty($huase)?'花色列不能为空；':"花色:[".$huase."]不存在；");
                        $code .= $k9?'':(empty($kaixiang)?'开向列不能为空；':"开向:[".$kaixiang."]不存在；");
                        $code .= $k13?'':(empty($xsms)?'销售模式列不能为空；':"销售模式:[".$xsms."]不存在；");
                        $code .= $k10?'':'数量非数字；';
                        $code .= $k11?'':'本月单价非数字；';
                        $code .= $k12?'':'下月单价非数字；';
                        $code .= $k14?'':(empty($thfs)?'提货方式列不能为空；':"提货方式:[".$thfs."]不存在；");
                        $code .= is_numeric($xsdj)?'':'销售单价非数字';
                        $code .= is_numeric($dbfy)?'':'调拨费用非数字';
                        $code .= is_numeric($qke)?'':'欠款额非数字';						
                        if (empty($code)){
                            $code = '该产品信息行在期初中未录入!';
                        }
						$teshuzhizaobm = array('舍零','返利','送货运费收入');
						if (in_array($zhizaobm,$teshuzhizaobm)){
                            $code = '该门套产品信息行在期初中未录入!';
                            $k1=1;$k2=1;$k3=1;$k4=1;$k5=1;$k6=1;$k7=1;$k8=1;$k9=1;$k10=1;$k11=1;$k12=1;$k13=1;$k14=1;
                        }
                        $error[] = array(
                            array('type'=>1,'value'=>$j),
                            array('type'=>1,'value'=>$date),
                            array('type'=>1,'value'=>$khxm),
                            array('type'=>$k1,'value'=>$zhizaobm),
                            array('type'=>$k2,'value'=>$dalei),
                            array('type'=>$k3,'value'=>$feibiao),
                            array('type'=>$k4,'value'=>$banhou),
                            array('type'=>empty($guige)?0:1,'value'=>$guige),
                            array('type'=>$k5,'value'=>$biaomianyq),
                            array('type'=>$k6,'value'=>$menkuang),
                            array('type'=>$k7,'value'=>$huase),
                            array('type'=>$k8,'value'=>$suoju),
                            array('type'=>$k9,'value'=>$kaixiang),
                            array('type'=>1,'value'=>$qita),
                            array('type'=>$k13,'value'=>$xsms),
                            array('type'=>$k10,'value'=>$xl),
                            array('type'=>$k11,'value'=>$xsdj),
                            array('type'=>$k12,'value'=>$cbdj),
                            array('type'=>1,'value'=>$dbfy),
                            array('type'=>1,'value'=>$qke),
                            array('type'=>$k14,'value'=>$thfs),
                            array('type'=>1,'value'=>$jsr),
                            array('type'=>0,'value'=>$code)
                        );
                    }
                }
            }
            unlink($uploadfile);
            if ($sql !=''){
                $json = '{"info": ['.trim($sql,',').']}';
                $this->submit('',0,$dept,$json);
                $this->response(array('resultcode'=>-1,'resultmsg'=>"本日上传产品期初信息:$m,成功:$n;",'error'=>$error),'json');
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
        $type=1;
        $arr = array(
            array('label'=>'zhizaobm','name'=>'制造部门','option'=>array()),
            array('label'=>'dalei','name'=>'大类','option'=>array()),//大类
            array('label'=>'feibiao','name'=>'非标','option'=>array()),//非标
            array('label'=>'banhou','name'=>'板厚','option'=>array()),//板厚
            array('label'=>'guige','name'=>'规格','option'=>array()),//规格
            array('label'=>'biaomianyq','name'=>'表面要求','option'=>array()),//表面要求
            array('label'=>'menkuang','name'=>'门框','option'=>array()),//门框
            array('label'=>'huase','name'=>'花色','option'=>array()),//花色
            array('label'=>'suoju','name'=>'锁具','option'=>array()),//锁具
            array('label'=>'kaixiang','name'=>'开向','option'=>array()),//开向
            array('label'=>'qita','name'=>'其他','option'=>array()),//其他
            array('label'=>'xiaoshoums','name'=>'销售模式','option'=>array('库房销售','直发销售')),//其他
            array('label'=>'tihuofs','name'=>'提货方式','option'=>array('公司直发','客户直发','托运','自提','送货'))//其他
        );
        $sql_qc = "select concat(zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita)as temp from spzmxqc where `date` = '$yue' and dept =$dept and `type`=$type";
        $qc = M()->query($sql_qc);
        foreach($qc as $vqc){
            $temp[] = $vqc['temp'];
        }
        $sql_info = "select `date`,kehuxm,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,xiaoshoums,xiaoliang,xiaoshoudj,chengbendj,0 as xiaoshousr,0 as xiaoshoucb,0 as maoli,0 as maolil,diaobofy,qiankuane,tihuofs,jingshou from xsmx as q where  `date` BETWEEN '$sdate' and '$edate' and dept = $dept and `type` =$type ";
        $info = M()->query($sql_info);

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

            $cpxx = $v['zhizaobm'].$v['dalei'].$v['feibiao'].$v['banhou'].$v['guige'].$v['biaomianyq'].$v['menkuang'].$v['huase'].$v['suoju'].$v['kaixiang'].$v['qita'];
            if (!in_array($cpxx,$temp)){
                $data['error'][] = array(
                    array('type'=>1,'value'=>$k+1),
                    array('type'=>0,'value'=>$v['date']),
                    array('type'=>0,'value'=>$v['zhizaobm']),
                    array('type'=>0,'value'=>$v['dalei']),
                    array('type'=>0,'value'=>$v['feibiao']),
                    array('type'=>0,'value'=>$v['banhou']),
                    array('type'=>0,'value'=>$v['guige']),
                    array('type'=>0,'value'=>$v['biaomianyq']),
                    array('type'=>0,'value'=>$v['menkuang']),
                    array('type'=>0,'value'=>$v['huase']),
                    array('type'=>0,'value'=>$v['suoju']),
                    array('type'=>0,'value'=>$v['kaixiang']),
                    array('type'=>0,'value'=>$v['qita']),
                    array('type'=>0,'value'=>'该明细行所对应的期初产品信息不存在!')
                );
            }
        }
        $title = json_decode('{"title":[{"tr":[{"dataType":0,"colspan":1,"rowspan":2,"value":"日期"},{"dataType":0,"colspan":1,"value":"客户信息"},{"dataType":0,"colspan":11,"value":"产品信息"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":8,"value":"销售盈亏"},{"dataType":0,"colspan":3,"value":"责任信息"}]},{"tr":[{"dataType":0,"colspan":1,"value":"客户姓名"},{"dataType":0,"colspan":1,"value":"制造部门"},{"dataType":0,"colspan":1,"value":"大类"},{"dataType":0,"colspan":1,"value":"非标"},{"dataType":0,"colspan":1,"value":"板厚"},{"dataType":0,"colspan":1,"value":"规格"},{"dataType":0,"colspan":1,"value":"表面要求"},{"dataType":0,"colspan":1,"value":"门框"},{"dataType":0,"colspan":1,"value":"花色"},{"dataType":0,"colspan":1,"value":"锁具"},{"dataType":0,"colspan":1,"value":"开向"},{"dataType":0,"colspan":1,"value":"其他"},{"dataType":0,"colspan":1,"value":"销售模式"},{"dataType":0,"colspan":1,"value":"销量"},{"dataType":0,"colspan":1,"value":"销售单价"},{"dataType":0,"colspan":1,"value":"成本单价"},{"dataType":0,"colspan":1,"value":"销售收入"},{"dataType":0,"colspan":1,"value":"销售成本"},{"dataType":0,"colspan":1,"value":"毛利"},{"dataType":0,"colspan":1,"value":"毛利率"},{"dataType":0,"colspan":1,"value":"调拨费用"},{"dataType":0,"colspan":1,"value":"欠费额"},{"dataType":0,"colspan":1,"value":"提货方式"},{"dataType":0,"colspan":1,"value":"经手人"}]},{"tr":[{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":"'.$heji['xiaoliang'].'"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""},
        {"dataType":0,"colspan":1,"value":"'.$heji['xiaoshousr'].'"},{"dataType":0,"colspan":1,"value":"'.$heji['xiaoshoucb'].'"},{"dataType":0,"colspan":1,"value":"'.$heji['maoli'].'"},{"dataType":0,"colspan":1,"value":"'.$heji['maolil'].'"},{"dataType":0,"colspan":1,"value":"'.$heji['diaobofy'].'"},{"dataType":0,"colspan":1,"value":"'.$heji['qiankuane'].'"},{"dataType":0,"colspan":1,"value":""},{"dataType":0,"colspan":1,"value":""}]}]}',true);
//        if (count($info)){
//            array_unshift($info,$heji);
//        }
        $data['list'] = $arr;
        $data['info'] = $info;
        //$data['option'] = $option;
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
        //获取期初产品信息
        $qc_temp = M()->query("select concat(zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita)as temp from spzmxqc where `date` = '$yue' and dept =$dept and `type`=$type ");
        foreach ($qc_temp as $vtemp){
            $temp[] = $vtemp['temp'];
        }
        if ($delete ==1){
            M()->execute("delete from xsmx where `type`=$type and dept = $dept and `date`='$date'");
        }
        M()->execute("update spzmx set jiecun = jiecun +kfxszc+zfxszc,kfxszc=0,zfxszc=0 where dept =$dept and `type`=$type and `date`='$date'");
        $data = json_decode($json,true);
        foreach($data['info'] as $k => $v ){
            if($k == 0 && $v['zhizaobm'] =='必填列')
            {}
            else{
//                $cpxx = $v['zhizaobm'].$v['dalei'].$v['feibiao'].$v['banhou'].$v['guige'].$v['biaomianyq'].$v['menkuang'].$v['huase'].$v['suoju'].$v['kaixiang'].$v['qita'];
//                if (in_array($cpxx,$temp)){
                $dj_arr = array(
                    'dept'=>$dept,
                    'type'=>$type,
                    'date'=>date('Y-m',strtotime($date)),
                    'zhizaobm'=>$v['zhizaobm'],
                    'dalei'=>$v['dalei'],
                    'feibiao'=>$v['feibiao'],
                    'banhou'=>$v['banhou'],
                    'guige'=>$v['guige'],
                    'biaomianyq'=>$v['biaomianyq'],
                    'menkuang'=>$v['menkuang'],
                    'huase'=>$v['huase'],
                    'suoju'=>$v['suoju'],
                    'kaixiang'=>$v['kaixiang'],
                    'qita'=>$v['qita'],);
                $dj = M('spzmxqc')->where($dj_arr)->select();
                $arrAll[] = array(
                    'dept'=>$dept,
                    'type'=>$type,
                    'date'=>$date,
                    'zhizaobm'=>$v['zhizaobm'],
                    'dalei'=>$v['dalei'],
                    'feibiao'=>$v['feibiao'],
                    'banhou'=>$v['banhou'],
                    'guige'=>$v['guige'],
                    'biaomianyq'=>$v['biaomianyq'],
                    'menkuang'=>$v['menkuang'],
                    'huase'=>$v['huase'],
                    'suoju'=>$v['suoju'],
                    'kaixiang'=>$v['kaixiang'],
                    'qita'=>$v['qita'],
                    'xiaoshoums'=>$v['xiaoshoums'],
                    'kehuxm'=>$v['kehuxm'],
                    'xiaoliang'=>$v['xiaoliang'],
                    'xiaoshoudj'=>$v['xiaoshoudj'],
                    'chengbendj'=>$dj[0]['benyuedj'],
                    'diaobofy'=>$v['diaobofy'],
                    'qiankuane'=>$v['qiankuane'],
                    'tihuofs'=>$v['tihuofs'],
                    'jingshou'=>$v['jingshou']
                );
                //$sql .= "($dept,$type,'$date','".$v['zhizaobm']."','".$v['dalei']."','".$v['feibiao']."','".$v['banhou']."','".$v['guige']."','".$v['biaomianyq']."','".$v['menkuang']."','".$v['huase']."','".$v['suoju']."','".$v['kaixiang']."','".$v['qita']."','".$v['xiaoshoums']."','".$v['kehuxm']."',".$v['xiaoliang'].",".$v['xiaoshoudj'].",".$v['diaobofy'].",".$v['qiankuane'].",'".$v['tihuofs']."','".$v['jingshou']."'),";
                //库房销售,直发销售 数据处理
                if ($v['xiaoshoums'] =='库房销售')
                    $info[$v['zhizaobm'].'#@'.$v['dalei'].'#@'.$v['feibiao'].'#@'.$v['banhou'].'#@'.$v['guige'].'#@'.$v['biaomianyq'].'#@'.$v['menkuang'].'#@'.$v['huase'].'#@'.$v['suoju'].'#@'.$v['kaixiang'].'#@'.$v['qita']]['kfxs'] += $v['xiaoliang'];
                else
                    $info[$v['zhizaobm'].'#@'.$v['dalei'].'#@'.$v['feibiao'].'#@'.$v['banhou'].'#@'.$v['guige'].'#@'.$v['biaomianyq'].'#@'.$v['menkuang'].'#@'.$v['huase'].'#@'.$v['suoju'].'#@'.$v['kaixiang'].'#@'.$v['qita']]['zfxs'] += $v['xiaoliang'];
//                }else{
//                    $this->response(array('resultcode'=>-1,'resultmsg'=>$cpxx.'--产品信息有误,请检查!'),'json');
//                }
            }
        }
        foreach($info as $kinfo =>$vinfo){
            $arr = explode('#@',$kinfo);
            //查询商品帐明细表
            $result = M()->query("select dept from spzmx where zhizaobm='".$arr[0]."' and dalei='".$arr[1]."' and feibiao='".$arr[2]."' and banhou ='".$arr[3]."' and guige='".$arr[4]."' and biaomianyq='".$arr[5]."' and menkuang='".$arr[6]."' and huase='".$arr[7]."' and suoju='".$arr[8]."' and kaixiang='".$arr[9]."' and qita='".$arr[10]."' and dept =$dept and `type` =$type and `date` ='$date' ");
            $vinfo['zfxs'] = empty($vinfo['zfxs'])?0:$vinfo['zfxs'];
            $vinfo['kfxs'] = empty($vinfo['kfxs'])?0:$vinfo['kfxs'];
            if (count($result)){
                //更新商品帐明细表
                $ssss = "update spzmx set  kfxszc =".$vinfo['kfxs']." ,zfxszc = ".$vinfo['zfxs'].",jiecun =dbsr+zcsr+phsr+shsh+qtsr-dbzc-phzc-shzc-qtzc-zczc-".$vinfo['zfxs']."-".$vinfo['kfxs']." where zhizaobm='".$arr[0]."' and dalei='".$arr[1]."' and feibiao='".$arr[2]."' and banhou ='".$arr[3]."' and guige='".$arr[4]."' and biaomianyq='".$arr[5]."' and menkuang='".$arr[6]."' and huase='".$arr[7]."' and suoju='".$arr[8]."' and kaixiang='".$arr[9]."' and qita='".$arr[10]."' and dept =$dept and `type` =$type and `date` ='$date'";
                M()->execute($ssss);
            }else{
                M()->execute("insert INTO spzmx(`type`,`dept`,`date`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,zfxszc,kfxszc,jiecun) Values($type,$dept,'$date','".$arr[0]."','".$arr[1]."','".$arr[2]."','".$arr[3]."','".$arr[4]."','".$arr[5]."','".$arr[6]."','".$arr[7]."','".$arr[8]."','".$arr[9]."','".$arr[10]."',".$vinfo['zfxs'].",".$vinfo['kfxs'].",".(-$vinfo['zfxs']-$vinfo['kfxs']).")");
            }
        }
        if (!empty($arrAll)){
            M()->execute("delete from xsmx where `type`=$type and dept = $dept and `date`='$date'");
            //$sql = 'replace INTO xsmx(`dept`,`type`,`date`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,xiaoshoums,kehuxm,xiaoliang,xiaoshoudj,diaobofy,qiankuane,tihuofs,jingshou) Values' . rtrim($sql, ',');
            //M()->execute($sql);
            M('xsmx')->addAll($arrAll);
            $check_pd = M()->query("select flag from spzpd where dept =$dept and `type`=$type and yuefen ='$yue' and edate ='$date' ");
            if (count($check_pd)){  //当天盘点过后,再次录入防盗门销售明细数据.flag设置为0
                M()->execute("update spzpd set flag =0,createtime=now() where dept =$dept and `type`=$type and edate ='$date' order by createtime desc limit 1");
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
//            $date1 = date('Y-m-d',strtotime($date));
//            if($date1 == $yuechu){//如果所选日期为当月1号，则导出上个月的所有数据
//                $start = date('Y-m-01',strtotime("-1 month"));//2017-02-01
//                $end = date('Y-m-t',strtotime("-1 month"));//2017-02-28
//            }else{
//                $start = $yuechu;
//                $end = date("Y-m-d",strtotime("$date -1 day"));//获取所选日期的前一天
//            }

            $sql_one = $sql_data . " limit " . ($i - 1) * $pageSize . ",$pageSize";
            $result = $Model->query($sql_one);
            foreach ($result as $key => $value){
                $dname = $value['dname'];//当前片区下的所有部门的部门名称
                $pname = $value['pname'];
                $tr  = ",,,,,,,,,,,,,,,,,,,,,,,,\n";
                $tr .= ",,,,,,,,,,,,,,,,,,,,,,,,\n";
                $tr .= ",,,,,,,,,,,,,,,,,,,,,,,,\n";
                $tr .= ",".$dname."---防盗门销售明细表,,,,,,,,,,,,,,,,\n";
                $tr .= ",日期,客户信息,,,,,,产品信息,,,,,,,,,,销售盈亏,,,,,,责任信息,\n";
                $tr .= ",日期,客户姓名,制造部门,大类,非标,板厚,规格,表面要求,门框,花色,锁具,开向,其他,销售模式,销量,销售单价,成本单价,销售收入,销售成本,毛利,毛利率,调拨费用,欠款额,提货方式,经手人\n";

                $depts = $value['id'];//当前片区下的所有部门的部门id
                //联表查询成本单价----2017-03-20：星期一：go on
//                $sql = "select * from xsmx where xsmx.date between '$start' and '$end' and dept='$depts'";//查询该片区的所有数据
//                $sql = "select a.date,a.kehuxm,a.zhizaobm,a.dalei,a.feibiao,a.banhou,a.guige,a.biaomianyq,
//a.menkuang,a.huase,a.suoju,a.kaixiang,a.qita,a.xiaoshoums,a.xiaoliang,a.xiaoshoudj,b.benyuedj,a.diaobofy,
//a.qiankuane,a.tihuofs,a.jingshou from xsmx a,spzmxqc b where a.date between '$start' and '$end' and a.dept='$depts'
//and a.zhizaobm=b.zhizaobm and a.dalei=b.dalei and a.feibiao=b.feibiao and a.banhou=b.banhou and a.guige=b.guige
//and a.biaomianyq=b.biaomianyq and a.menkuang=b.menkuang and a.huase=b.huase and a.suoju=b.suoju
//and a.kaixiang=b.kaixiang and a.qita=b.qita order by a.dalei ;";//查询该片区的所有数据
//               echo $sql;return;
                $sql = "SELECT a.date,a.kehuxm,a.zhizaobm,a.dalei,a.feibiao,a.banhou,a.guige,a.biaomianyq,
a.menkuang,a.huase,a.suoju,a.kaixiang,a.qita,a.xiaoshoums,a.xiaoliang,a.xiaoshoudj,a.chengbendj,a.diaobofy,
a.qiankuane,a.tihuofs,a.jingshou
FROM xsmx a
WHERE a.date BETWEEN '$start' AND '$end' AND a.dept='$depts' 
;";
//                echo $sql;return;
                $tempData = $Model->query($sql);
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
                    $jingshou = $temp_value["jingshou"];//经手人

                    $tr .=",".$riqi.",";
                    $temp_value = array_values($temp_value);
                    foreach($temp_value as $arr_key=>$arr_val){
                        if($arr_key > 0 && $arr_key < count($temp_value)-4){
                            $tr.=$arr_val.",";//到成本单价结束
                        }
                    }
                    $tr .= $xiaoshousr.",".$xiaoshoucb.",".$maoli.",".$maolilv.",".$diaobofy.",".$qiankuane.",".$tihuofs.",".$jingshou."\n";
                }
//                echo $tr.'<br/>';
//                return;
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