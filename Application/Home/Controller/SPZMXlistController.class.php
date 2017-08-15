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
class SPZMXlistController extends RestController{
    /**
     * 经营部商品帐明细期初表导入
     */
    public function loadingExcelQc($token='',$types=1){
        header("Access-Control-Allow-Origin: *");
        $date = date('Y-m',strtotime(TODAY));
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
            $extension = substr(strrchr($_FILES["file"]["name"], '.'), 1);
            if ($extension != 'xls' && $extension != 'xlsx'){
                $this->response(array('resultcode'=>-1,'resultmsg'=>'文件格式错误!'),'json');
            }
            $n = 0; //成功插入记录条数
            $m = 0;
            $objPHPExcel = \PHPExcel_IOFactory::load($uploadfile);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 总行数
//            $highestColumn = $sheet->getHighestColumn(); // 总列数
//            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            if ($objPHPExcel->getActiveSheet()->getCell("A2")->getValue() !='制造部门')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是制造部门'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("B2")->getValue() !='大类')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是大类'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("C2")->getValue() !='非标')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是非标'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("D2")->getValue() !='板厚')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是板厚'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("E2")->getValue() !='规格')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是规格'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("F2")->getValue() !='表面要求')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是表面要求'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("G2")->getValue() !='门框')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是门框'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("H2")->getValue() !='花色')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是花色'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("I2")->getValue() !='锁具')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'I列不是锁具'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("J2")->getValue() !='开向')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'J列不是开向'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("K2")->getValue() !='其他')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'K列不是其他'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("L2")->getValue() !='期初数量')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'L列不是期初数量'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("M2")->getValue() !='本月单价')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是本月单价'),'json');
            if($objPHPExcel->getActiveSheet()->getCell("N2")->getValue() !='下月单价')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是下月单价'),'json');


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

            for ($j = 5; $j <= $highestRow; $j++) { //第5行开始循环数据行
                $m++;
                if (empty($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue()) && empty($objPHPExcel->getActiveSheet()->getCell("B$j")->getValue())) //以1,2列判断是否为空行
                {}
                else{
                    $zhizaobm = trim($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue());
                    $dalei = trim(empty($objPHPExcel->getActiveSheet()->getCell("B$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("B$j")->getValue());
                    $feibiao = trim(empty($objPHPExcel->getActiveSheet()->getCell("C$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("C$j")->getValue());
                    $banhou = trim(empty($objPHPExcel->getActiveSheet()->getCell("D$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("D$j")->getValue());
                    $guige = trim(empty($objPHPExcel->getActiveSheet()->getCell("E$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("E$j")->getValue());
                    $biaomianyq = trim(empty($objPHPExcel->getActiveSheet()->getCell("F$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("F$j")->getValue());
                    $menkuang = trim(empty($objPHPExcel->getActiveSheet()->getCell("G$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("G$j")->getValue());
                    $huase =  trim(empty($objPHPExcel->getActiveSheet()->getCell("H$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("H$j")->getValue());
                    $suoju = trim(empty($objPHPExcel->getActiveSheet()->getCell("I$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("I$j")->getValue());
                    $kaixiang =  trim(empty($objPHPExcel->getActiveSheet()->getCell("J$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("J$j")->getValue());
                    $qita =  trim(empty($objPHPExcel->getActiveSheet()->getCell("K$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("K$j")->getValue());
                    $sl =  empty($objPHPExcel->getActiveSheet()->getCell("L$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("L$j")->getValue();
                    $bydj =  empty($objPHPExcel->getActiveSheet()->getCell("M$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("M$j")->getValue();
                    $xydj =  empty($objPHPExcel->getActiveSheet()->getCell("N$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("N$j")->getValue();

                    $banhou = str_replace('×','*',$banhou);
                    $guige = str_replace('×','*',$guige);
					$qita = trim($qita);
                    $qita = trim($qita,"'");

                    $k1= in_array($zhizaobm,$temp['zhizaobm'])?1:0;
                    $k2= in_array($dalei,$temp['dalei'])?1:0;
                    $k3= in_array($feibiao,$temp['feibiao'])?1:0;
                    $k4= in_array($banhou,$temp['banhou'])?1:0;
                    $k5= in_array($biaomianyq,$temp['biaomianyq'])?1:0;
                    $k6= in_array($menkuang,$temp['menkuang'])?1:0;
                    $k7= in_array($huase,$temp['huase'])?1:0;
                    $k8= in_array($suoju,$temp['suoju'])?1:0;
                    $k9= in_array($kaixiang,$temp['kaixiang'])?1:0;
                    $k10= ctype_digit("$sl")?1:0;
                    $k11= is_numeric($bydj)?1:0;
                    $k12= is_numeric($xydj)?1:0;


                    //特殊制造部门
                    $teshuzhizaobm = array('舍零','返利','送货运费收入');
                    if (in_array($zhizaobm,$teshuzhizaobm)){
                        $kzhizaobm = 1;
                    }
                    //特殊门判断
                    $kmenkuang = in_array($menkuang,$temp['menkuang']);
                    $ksuoju = in_array($suoju,$temp['suoju']);
                    $teshumen = array('钢木门','强化门','卫浴门','单板门','木门');
                    if (in_array($dalei,$teshumen)){
                        $kmenkuang =1;
                        $ksuoju =1;
                    }

                    //录入选项判断
                    //其中,配件行C-J列必须为空,若有则会判断为产品信息行
                    if ($kzhizaobm
                        ||(ctype_digit("$sl") && is_numeric($bydj) && is_numeric($xydj) && in_array($zhizaobm,$temp['zhizaobm']) && in_array($dalei,$temp['dalei']) && empty($feibiao) && empty($banhou) && empty($guige) &&empty($biaomianyq) &&empty($menkuang) &&empty($huase) &&empty($suoju) &&empty($kaixiang) &&  !empty($qita))
                        || (!empty($guige) && ctype_digit("$sl") && is_numeric($bydj) && is_numeric($xydj) && in_array($zhizaobm,$temp['zhizaobm']) && in_array($dalei,$temp['dalei']) && in_array($feibiao,$temp['feibiao']) && in_array($banhou,$temp['banhou']) && in_array($biaomianyq,$temp['biaomianyq']) && $kmenkuang && in_array($huase,$temp['huase']) && $ksuoju && in_array($kaixiang,$temp['kaixiang']))){
                        $sql .= "($types,$dept,'$date','" . $zhizaobm . "','" . $dalei . "','" . $feibiao . "','" . $banhou . "','" . $guige . "','" . $biaomianyq . "','" . $menkuang . "','" . $huase . "','" . $suoju . "','" . $kaixiang . "','" . $qita . "', $sl , $bydj , $xydj ),";
                        $n++;
                        //检查重复合格数据
                        $str_repeat = $zhizaobm.$dalei.$feibiao.$banhou.$guige.$biaomianyq.$menkuang.$huase.$suoju.$kaixiang.$qita;
                        if (in_array($str_repeat,$repeat)){
                            $error[] = array(
                                array('type'=>1,'value'=>$j),
                                array('type'=>1,'value'=>$zhizaobm),
                                array('type'=>1,'value'=>$dalei),
                                array('type'=>1,'value'=>$feibiao),
                                array('type'=>1,'value'=>$banhou),
                                array('type'=>1,'value'=>$guige),
                                array('type'=>1,'value'=>$biaomianyq),
                                array('type'=>1,'value'=>$menkuang),
                                array('type'=>1,'value'=>$huase),
                                array('type'=>1,'value'=>$suoju),
                                array('type'=>1,'value'=>$kaixiang),
                                array('type'=>1,'value'=>$qita),
                                array('type'=>1,'value'=>$sl),
                                array('type'=>1,'value'=>$bydj),
                                array('type'=>1,'value'=>$xydj),
                                array('type'=>0,'value'=>'该行期初产品信息已存在,请检查仔细!')
                            );
                        }
                        $repeat[] = $str_repeat;
                    }else{
                        $code = '';
                        $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                        $code .= $k2?'':(empty($dalei)?'大类列不能为空；':"大类:[".$dalei."]不存在；");
                        $code .= $k3?'':(empty($feibiao)?'非标列不能为空；':"非标:[".$feibiao."]不存在；");
                        $code .= $k4?'':(empty($banhou)?'板厚列不能为空；':"板厚:[".$banhou."]不存在；");
                        $code .= empty($guige)?'规格列不能为空；':'';
                        $code .= $k5?'':(empty($biaomianyq)?'表面要求列不能为空；':"表面要求:[".$biaomianyq."]不存在；");
                        $code .= $k6?'':(empty($menkuang)?'门框列不能为空；':"门框:[".$menkuang."]不存在；");
                        $code .= $k7?'':(empty($huase)?'花色列不能为空；':"花色:[".$huase."]不存在；");
                        $code .= $k8?'':(empty($suoju)?'锁具列不能为空；':"锁具:[".$suoju."]不存在；");
                        $code .= $k9?'':(empty($kaixiang)?'开向列不能为空；':"开向:[".$kaixiang."]不存在；");
                        $code .= $k10?'':'期初数量录入不正确；';
                        $code .= $k11?'':'本月单价非数字；';
                        $code .= $k12?'':'下月单价非数字；';
                        $error[] = array(
                            array('type'=>1,'value'=>$j),
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
                            array('type'=>$k10,'value'=>$sl),
                            array('type'=>$k11,'value'=>$bydj),
                            array('type'=>$k12,'value'=>$xydj),
                            array('type'=>0,'value'=>$code)
                        );
                    }
                }
            }
            unlink($uploadfile);
            if ($sql !=''){
                M()->execute("delete from spzmxqc where `type`=$types and dept = $dept and `date`='$date'");
                $ins_sql = "replace into spzmxqc(type,dept,`date`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,qichusl,benyuedj,xiayuedj)values" . rtrim($sql, ',');
                M()->execute($ins_sql);
				M()->execute("update spzpd set `flag`=0,createtime =now() where dept =$dept and edate ='".TODAY."' and `type`=$types order by createtime desc limit 1");                
                $this->response(array('resultcode'=>-1,'resultmsg'=>"上传产品期初信息:$m,成功:$n;",'error'=>$error),'json');
            }else{
                $this->response(array('resultcode'=>-1,'resultmsg'=>"上传0条!",'error'=>$error),'json');
            }
        }
    }

    /**
     * 商品帐可选选项查询
     */
    public function search($token=''){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $date = date("Y-m",strtotime(TODAY));
        $sql = "select zhizaobm,dalei,feibiao,banhou,biaomianyq,menkuang,huase,suoju,kaixiang from spzmx_list";
        $attr = M()->query($sql);
        $arr = array(
            array('label'=>'zhizaobm','name'=>'制造部门','option'=>array()),
            array('label'=>'dalei','name'=>'大类','option'=>array()),//大类
            array('label'=>'feibiao','name'=>'非标','option'=>array()),//
            array('label'=>'banhou','name'=>'板厚','option'=>array()),//板厚
            array('label'=>'guige','name'=>'规格','option'=>array()),//规格
            array('label'=>'biaomianyq','name'=>'表面要求','option'=>array()),//表面要求
            array('label'=>'menkuang','name'=>'门框','option'=>array()),//门框
            array('label'=>'huase','name'=>'花色','option'=>array()),//花色
            array('label'=>'suoju','name'=>'锁具','option'=>array()),//锁具
            array('label'=>'kaixiang','name'=>'开向','option'=>array())//开向
        );
        foreach ($attr as $key=>$row){
            //属性列名
            $row_keys=array_keys($row);
            foreach ($row_keys as $key){
                //如果属性列值不为空则插入到对应的option数组中
                if(!empty($row[$key])){
                    //查询插入的option的位置
                    foreach ($arr as $offset=>$tempval){
                        if($tempval['label']==$key){
                            //如果不存在则添加到选项中
                            if(!in_array($row[$key], $arr[$offset]['option']))
                                array_push($arr[$offset]['option'],$row[$key]);
                        }
                    }
                }
            }
        }
        $data['list'] = $arr;
        $this->response(array('resultcode'=>0,'resultmsg'=>'查询成功','data'=>$data),'json');
    }

    /**
     * 商品帐列表保存
     * @param string $token
     */
    public function submit($token='',$type=1,$delete=0){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $date = date("Y-m",strtotime(TODAY));
        $json = file_get_contents("php://input");
        switch ($this->_method){
            case 'post':{
                break;
            }
            case 'get':{
                $this->response(array('resultcode'=>-1,'resultmsg'=>'数据传递方式错误!'),'json');
                break;
            }
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
        $data = json_decode($json,true);
        if($delete ==1){
            M()->execute("delete from spzmxqc where `type`=$type and dept = $dept and `date`='$date' ");
        }
        foreach($data['data'] as $v){
            if($v['create_time'] =='')
                $time = date("Y-m-d H:i:s");
            else
                $time = $v['create_time'];
            if ($v['zhizaobm'] =='必填列')
                continue;

            $zhizaobm =$v['zhizaobm'] ;
            $dalei = $v['dalei'] ;
            $feibiao = $v['feibiao'];
            $banhou = $v['banhou'];
            $guige = str_replace('×','*',$v['guige']);
            $biaomianyq = $v['biaomianyq'];
            $menkuang = $v['menkuang'];
            $huase =  $v['huase'];
            $suoju = $v['suoju'];
            $kaixiang =  $v['kaixiang'];
			$qita = trim($v['qita']);
            $qita = trim($v['qita'],"'");
            $sl =  $v['qichusl'];
            $bydj =  $v['benyuedj'];
            $xydj = $v['xiayuedj'];

            $k1= in_array($zhizaobm,$temp['zhizaobm'])?1:0;
            $k2= in_array($dalei,$temp['dalei'])?1:0;
            $k3= in_array($feibiao,$temp['feibiao'])?1:0;
            $k4= in_array($banhou,$temp['banhou'])?1:0;
            $k5= in_array($biaomianyq,$temp['biaomianyq'])?1:0;
            $k6= in_array($menkuang,$temp['menkuang'])?1:0;
            $k7= in_array($huase,$temp['huase'])?1:0;
            $k8= in_array($suoju,$temp['suoju'])?1:0;
            $k9= in_array($kaixiang,$temp['kaixiang'])?1:0;
            $k10= is_numeric($sl)?1:0;
            $k11= is_numeric($bydj)?1:0;
            $k12= is_numeric($xydj)?1:0;

            //特殊制造部门
            $teshuzhizaobm = array('舍零','返利','送货运费收入');
            if (in_array($zhizaobm,$teshuzhizaobm)){
                $kzhizaobm = 1;
            }
            //特殊门判断
            $kmenkuang = in_array($menkuang,$temp['menkuang']);
            $ksuoju = in_array($suoju,$temp['suoju']);
            $teshumen = array('钢木门','强化门','卫浴门','单板门','木门');
            if (in_array($dalei,$teshumen)){
                $kmenkuang =1;
                $ksuoju =1;
            }

            if ($kzhizaobm
                ||(is_numeric($v['qichusl']) && is_numeric($v['benyuedj']) && is_numeric($v['xiayuedj']) && in_array($v['zhizaobm'],$temp['zhizaobm']) && in_array($v['dalei'],$temp['dalei']) && empty($v['feibiao']) && empty($v['banhou']) && empty($v['guige']) &&empty($v['menkuang']) &&empty($v['biaomianyq']) &&empty($v['huase']) &&empty($v['suoju']) &&empty($v['kaixiang']) &&  !empty($v['qita']))
                || (!empty($guige) && is_numeric($v['qichusl']) && is_numeric($v['benyuedj']) && is_numeric($v['xiayuedj']) && in_array($v['zhizaobm'],$temp['zhizaobm']) && in_array($v['dalei'],$temp['dalei']) && in_array($v['feibiao'],$temp['feibiao']) && in_array($v['banhou'],$temp['banhou']) && in_array($v['biaomianyq'],$temp['biaomianyq']) && $kmenkuang && in_array($v['huase'],$temp['huase']) && $ksuoju && in_array($v['kaixiang'],$temp['kaixiang']))){
                $sql .= "($type,$dept,'$date','$time','".$v['zhizaobm']."','".$v['dalei']."','".$v['feibiao']."','".$v['banhou']."','".$v['guige']."','".$v['biaomianyq']."','".$v['menkuang']."','".$v['huase']."','".$v['suoju']."','".$v['kaixiang']."','".$v['qita']."','".$v['qichusl']."','".$v['benyuedj']."','".$v['xiayuedj']."','".$v['qichuje']."'),";
                //检查重复合格数据
                $str_repeat = $zhizaobm.$dalei.$feibiao.$banhou.$guige.$biaomianyq.$menkuang.$huase.$suoju.$kaixiang.$qita;
                if (in_array($str_repeat,$repeat)){
                    $error[] = array(
                        array('type'=>1,'value'=>''),
                        array('type'=>1,'value'=>$zhizaobm),
                        array('type'=>1,'value'=>$dalei),
                        array('type'=>1,'value'=>$feibiao),
                        array('type'=>1,'value'=>$banhou),
                        array('type'=>1,'value'=>$guige),
                        array('type'=>1,'value'=>$biaomianyq),
                        array('type'=>1,'value'=>$menkuang),
                        array('type'=>1,'value'=>$huase),
                        array('type'=>1,'value'=>$suoju),
                        array('type'=>1,'value'=>$kaixiang),
                        array('type'=>1,'value'=>$qita),
                        array('type'=>1,'value'=>$sl),
                        array('type'=>1,'value'=>$bydj),
                        array('type'=>1,'value'=>$xydj),
                        array('type'=>0,'value'=>'该行期初产品信息已存在,请检查仔细!')
                    );
                }
                $repeat[] = $str_repeat;
            }else{
                $code = '';
                $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                $code .= $k2?'':(empty($dalei)?'大类列不能为空；':"大类:[".$dalei."]不存在；");
                $code .= $k3?'':(empty($feibiao)?'非标列不能为空；':"非标:[".$feibiao."]不存在；");
                $code .= $k4?'':(empty($banhou)?'板厚列不能为空；':"板厚:[".$banhou."]不存在；");
                $code .= empty($guige)?'规格列不能为空；':'';
                $code .= $k5?'':(empty($biaomianyq)?'表面要求列不能为空；':"表面要求:[".$biaomianyq."]不存在；");
                $code .= $k6?'':(empty($menkuang)?'门框列不能为空；':"门框:[".$menkuang."]不存在；");
                $code .= $k7?'':(empty($huase)?'花色列不能为空；':"花色:[".$huase."]不存在；");
                $code .= $k8?'':(empty($suoju)?'锁具列不能为空；':"锁具:[".$suoju."]不存在；");
                $code .= $k9?'':(empty($kaixiang)?'开向列不能为空；':"开向:[".$kaixiang."]不存在；");
                $code .= $k10?'':'数量非数字；';
                $code .= $k11?'':'本月单价非数字；';
                $code .= $k12?'':'下月单价非数字；';
                $error[] = array(
                    array('type'=>1,'value'=>''),
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
                    array('type'=>$k10,'value'=>$sl),
                    array('type'=>$k11,'value'=>$bydj),
                    array('type'=>$k12,'value'=>$xydj),
                    array('type'=>0,'value'=>$code)
                );
            }
        }
        if ($sql !=''){
            M()->execute("delete from spzmxqc where `type`=$type and dept = $dept and `date`='$date' ");
            $sql_ins = "replace into spzmxqc(`type`,dept,`date`,`create_time`,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,qichusl,benyuedj,xiayuedj,qichuje)values" . rtrim($sql, ',');
            $result = M()->execute($sql_ins);
            M()->execute("update spzpd set `flag`=0,createtime =now() where dept =$dept and edate ='".TODAY."' and `type`=$type order by createtime desc limit 1");
        }
        $this->response(array('resultcode'=>-1,'resultmsg'=>'保存成功','error'=>$error),'json');
    }
}