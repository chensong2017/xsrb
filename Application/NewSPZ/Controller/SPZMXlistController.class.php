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
class SPZMXlistController extends RestController{
    /**
     * 经营部商品帐明细期初表导入
     */
    public function loadingExcelQc($token="",$types=1){
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
            if($extension == 'csv'){
                $handle = fopen($uploadfile,'r');
                //解析CSV
                $result = $this->input_csv($handle);
                $n = 0; //成功插入记录条数
                $m = 0;
                //总行数
                $len = count($result);
                $sql_check = "select * from new_spzmx_list ";
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
                for($j = 2;$j < $len;$j++){//第3行（下标为2）开始循环数据行
                    $m++;
                    $shangpinjc = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][0]));
                    $zhizaobm = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][1]));
                    $dingdanlb = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][2]));
                    $dangci = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][3]));
                    $menkuang = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][4]));
                    $kuanghou = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][5]));
                    $qianbanhou = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][6]));
                    $houbanhou = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][7]));
                    $dikuangcl = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][8]));
                    $menshan = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][9]));
                    $guige = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][10]));
                    $kaixiang = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][11]));
                    $jiaolian = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][12]));
                    $huase = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][13]));
                    $biaomianfs = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][14]));
                    $biaomianyq = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][15]));
                    $chuanghua = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][16]));
                    $maoyan = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][17]));
                    $biaopai = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][18]));
                    $zhusuo = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][19]));
                    $fusuo = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][20]));
                    $suoba = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][21]));
                    $biaojian = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][22]));
                    $baozhuangpp = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][23]));
                    $baozhuangfs = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][24]));
                    $qita = excel_trim2(iconv('gb2312', 'utf-8',$result[$j][25]));
                    $sl = excel_trim(iconv('gb2312', 'utf-8',$result[$j][26]));
                    $bydj = excel_trim(iconv('gb2312', 'utf-8',$result[$j][27]));
                    $xydj = excel_trim(iconv('gb2312', 'utf-8',$result[$j][28]));
                    $qichuje = $sl*$bydj;
                    $k1 = in_array($zhizaobm,$temp['zhizaobm'])?1:0;//制造部门
                    $k2= in_array($dingdanlb,$temp['dingdanlb'])?1:0;//订单类别
                    $k3= in_array($dangci,$temp['dangci'])?1:0;//档次
                    $k4= in_array($menkuang,$temp['menkuang'])?1:0;//门框
                    $k5= in_array($kuanghou,$temp['kuanghou'])?1:0;//框厚
                    $k6= in_array($qianbanhou,$temp['qianbanhou'])?1:0;//前板厚
                    $k7= in_array($houbanhou,$temp['houbanhou'])?1:0;//后板厚
                    $k8= in_array($dikuangcl,$temp['dikuangcl'])?1:0;//底框材料
                    $k9= in_array($menshan,$temp['menshan'])?1:0;//门扇
//                    $k10= in_array($guige,$temp['guige'])?1:0;//规格
                    $k11= in_array($kaixiang,$temp['kaixiang'])?1:0;//开向
                    $k12= in_array($jiaolian,$temp['jiaolian'])?1:0;//铰链
                    $k13= in_array($huase,$temp['huase'])?1:0;//花色
                    $k14= in_array($biaomianfs,$temp['biaomianfs'])?1:0;//表面方式
                    $k15= in_array($biaomianyq,$temp['biaomianyq'])?1:0;//表面要求
                    $k16= in_array($chuanghua,$temp['chuanghua'])?1:0;//窗花
                    $k17= in_array($maoyan,$temp['maoyan'])?1:0;//猫眼
                    $k18= in_array($biaopai,$temp['biaopai'])?1:0;//标牌
                    $k19= in_array($zhusuo,$temp['zhusuo'])?1:0;//主锁
                    $k20= in_array($fusuo,$temp['fusuo'])?1:0;//副锁
                    $k21= in_array($suoba,$temp['suoba'])?1:0;//锁把
                    $k22= in_array($biaojian,$temp['biaojian'])?1:0;//标件
                    $k23= in_array($baozhuangpp,$temp['baozhuangpp'])?1:0;//包装品牌
                    $k24= in_array($baozhuangfs,$temp['baozhuangfs'])?1:0;//包装方式

                    $sl = empty($sl)?'0':$sl;
                    $k26= ctype_digit($sl)?1:0;//期初数量
                    $bydj = empty($bydj)?0:$bydj;
                    $k27= is_numeric($bydj)?1:0;//本月单价
                    $xydj = empty($xydj)?'0':$xydj;
                    $k28= is_numeric($xydj)?1:0;//下月单价

                    //特殊制造部门
                    $teshuzhizaobm = array('舍零','返利','送货运费收入','外购');
                    $teshudangci = array('门套','锁把','锁体','锁芯','配件','把手');
                    //特殊门判断
                    $teshumen1 = array('防火门');
                    $teshumen2 = array('钢木门','强化门','卫浴门','木门');
                    //零碎门套、锁把等处理
                    //1---不填(可填)
                    if (in_array($zhizaobm,$teshuzhizaobm) || in_array($dangci,$teshudangci)){
                        $k3 = $k2 = $k4 = $k5 = $k6 = $k7 = $k8 = $k9 = $k11 = $k12 = $k13 = $k14 = $k15 = $k16
                            = $k17 = $k18 = $k19 = $k20 = $k21 = $k22 = $k23 = $k24 = 1;
                    }
                    if (in_array($dangci,$teshumen1)){//如果是防火门---表面要求、窗花、标牌不填
                        $k15 = $k16 = $k18 = 1;
                    }
                    if(in_array($dangci,$teshumen2)){//木门（除开档次、规格、花色，其余均不填）
                        $k2 = $k4 = $k5 = $k6 = $k7 = $k8 = $k9 = $k11 = $k12 = $k14 = $k15 = $k16 = $k17 = $k18 = $k19 = $k20 = $k21 =
                        $k22 = $k23 = $k24 = 1;
                    }
                    $flag = empty($k1 && $k2 && $k3 && $k4 && $k5 && $k6 && $k7 && $k8 && $k9 && $k11 && $k12 && $k13
                        && $k14 && $k15 && $k16 && $k17 && $k18 && $k19 && $k20 && $k21 && $k22 && $k23 && $k24  && $k26
                        && $k27  && $k28)?0:1;

                    if ($flag){
                        $create_time = date("Y-m-d H:i:s");
                        $add_key = $zhizaobm.$dingdanlb.$dangci.$menkuang.floatval($kuanghou).floatval($qianbanhou).floatval($houbanhou).$dikuangcl.$menshan.$guige.$kaixiang.$jiaolian.$huase.$biaomianfs.$biaomianyq.$chuanghua.$maoyan.$biaopai.$zhusuo.$fusuo.$suoba.$biaojian.$baozhuangpp.$baozhuangfs.$qita.$dept.$date.$types;
                        $product_md5 = md5($add_key);
                        $qichuje = $sl*$bydj;
                        $sql .= "($types,$dept,'$date','" . $shangpinjc . "','" . $zhizaobm . "','" . $dingdanlb . "','" . $dangci . "','" . $menkuang . "','" . $kuanghou . "','" . $qianbanhou . "','" . $houbanhou . "','" . $dikuangcl . "','" . $menshan . "','" . $guige . "','".$kaixiang."','".$jiaolian."','".$huase."','".$biaomianfs."','".$biaomianyq."','".$chuanghua."','".$maoyan."','".$biaopai."','".$zhusuo."','".$fusuo."','".$suoba."','".$biaojian."','".$baozhuangpp."','".$baozhuangfs."','".$qita."', $bydj , $xydj , $sl ,'$qichuje','$create_time','$product_md5'),";
                        $n++;
                    }else{//错误提示机制
                        $code = '';
                        $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                        $code .= $k2?'':(empty($dingdanlb)?'订单类别列不能为空；':"订单类别:[".$dingdanlb."]不存在；");
                        $code .= $k3?'':(empty($dangci)?'档次列不能为空；':"档次:[".$dangci."]不存在；");
                        $code .= $k4?'':(empty($menkuang)?'门框列不能为空；':"门框:[".$menkuang."]不存在；");
                        $code .= $k5?'':(empty($kuanghou)?'框厚列不能为空；':"框厚:[".$kuanghou."]不存在；");
                        $code .= $k6?'':(empty($qianbanhou)?'前板厚列不能为空；':"前板厚:[".$qianbanhou."]不存在；");
                        $code .= $k7?'':(empty($houbanhou)?'后板厚列不能为空；':"后板厚:[".$houbanhou."]不存在；");
                        $code .= $k8?'':(empty($dikuangcl)?'底框材料列不能为空；':"底框材料:[".$dikuangcl."]不存在；");
                        $code .= $k9?'':(empty($menshan)?'门扇列不能为空；':"门扇:[".$menshan."]不存在；");
                        $guige_sm = "";
                        $guige_judge = explode('*',strval($guige));
                        foreach($guige_judge as $val){
                            if(strlen($val) == 3 || strlen($val) == 4){
                                continue;
                            }elseif(in_array($zhizaobm,$teshuzhizaobm) || in_array($dangci,$teshudangci)){
                                continue;
                            }else{
                                $guige_sm = '规格需为3或4位数*3或4位数,error_line : '.$j."； ";
                            }
                        }
                        $code .= empty($guige)?'规格列不能为空；':$guige_sm;
                        $code .= $k11?'':(empty($kaixiang)?'开向列不能为空；':"开向:[".$kaixiang."]不存在；");
                        $code .= $k12?'':(empty($jiaolian)?'铰链列不能为空；':"铰链:[".$jiaolian."]不存在；");
                        $code .= $k13?'':(empty($huase)?'花色列不能为空；':"花色:[".$huase."]不存在；");
                        $code .= $k14?'':(empty($biaomianfs)?'表面方式列不能为空；':"表面方式:[".$biaomianfs."]不存在；");
                        $code .= $k15?'':(empty($biaomianyq)?'表面要求列不能为空；':"表面要求:[".$biaomianyq."]不存在；");

                        $code .= $k16?'':(empty($chuanghua)?'窗花列不能为空；':"窗花:[".$chuanghua."]不存在；");
                        $code .= $k17?'':(empty($maoyan)?'猫眼列不能为空；':"猫眼:[".$maoyan."]不存在；");
                        $code .= $k18?'':(empty($biaopai)?'标牌列不能为空；':"标牌:[".$biaopai."]不存在；");
                        $code .= $k19?'':(empty($zhusuo)?'主锁列不能为空；':"主锁:[".$zhusuo."]不存在；");
                        $code .= $k20?'':(empty($fusuo)?'副锁列不能为空；':"副锁:[".$fusuo."]不存在；");
                        $code .= $k21?'':(empty($suoba)?'锁把列不能为空；':"锁把:[".$suoba."]不存在；");
                        $code .= $k22?'':(empty($biaojian)?'标件列不能为空；':"标件:[".$biaojian."]不存在；");
                        $code .= $k23?'':(empty($baozhuangpp)?'包装品牌列不能为空；':"包装品牌:[".$baozhuangpp."]不存在；");
                        $code .= $k24?'':(empty($baozhuangfs)?'包装方式列不能为空；':"装方式:[".$baozhuangfs."]不存在；");
                        $code .= $k26?'':'期初数量录入不正确；';
                        $code .= $k27?'':'本月单价非数字；';
                        $code .= $k28?'':'下月单价非数字；';
                        $error[] = array(
                            array('type'=>1,'value'=>$j+1),
                            array('type'=>1,'value'=>$shangpinjc),
                            array('type'=>$k1,'value'=>$zhizaobm),
                            array('type'=>$k2,'value'=>$dingdanlb),
                            array('type'=>$k3,'value'=>$dangci),
                            array('type'=>$k4,'value'=>$menkuang),
                            array('type'=>$k5,'value'=>$kuanghou),
                            array('type'=>$k6,'value'=>$qianbanhou),
                            array('type'=>$k7,'value'=>$houbanhou),
                            array('type'=>$k8,'value'=>$dikuangcl),
                            array('type'=>$k9,'value'=>$menshan),
                            array('type'=>empty($guige)?0:1,'value'=>$guige),
                            array('type'=>$k11,'value'=>$kaixiang),
                            array('type'=>$k12,'value'=>$jiaolian),
                            array('type'=>$k13,'value'=>$huase),
                            array('type'=>$k14,'value'=>$biaomianfs),
                            array('type'=>$k15,'value'=>$biaomianyq),
                            array('type'=>$k16,'value'=>$chuanghua),
                            array('type'=>$k17,'value'=>$maoyan),
                            array('type'=>$k18,'value'=>$biaopai),
                            array('type'=>$k19,'value'=>$zhusuo),
                            array('type'=>$k20,'value'=>$fusuo),
                            array('type'=>$k21,'value'=>$suoba),
                            array('type'=>$k22,'value'=>$biaojian),
                            array('type'=>$k23,'value'=>$baozhuangpp),
                            array('type'=>$k24,'value'=>$baozhuangfs),

                            array('type'=>1,'value'=>$qita),
                            array('type'=>$k26,'value'=>$sl),
                            array('type'=>$k27,'value'=>$bydj),
                            array('type'=>$k28,'value'=>$xydj),
                            array('type'=>0,'value'=>$code)
                        );
                    }
                }
                if ($sql !=''){
                    $firstDay=date('Y-m-01');
                    $del = "delete from new_spzmxqc where `type`=$types and dept = $dept and `month`='$date' and
                product_md5 not in (select product_md5 from new_shouzhimx where dept=$dept and type=$types and create_date>'$firstDay' union select product_md5 from new_xsmx where dept=$dept and type=$types and date>'$firstDay' )
                ";
                    M()->execute("delete from new_spzmxqc where `type`=$types and dept = $dept and `month`='$date' and
                product_md5 not in (select product_md5 from new_shouzhimx where dept=$dept and type=$types and create_date>'$firstDay' union select product_md5 from new_xsmx where dept=$dept and type=$types and date>'$firstDay' )
                ");
                    $ins_sql = "replace into new_spzmxqc(type,dept,month,shangpinjc,zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita,benyuedj,xiayuedj,qichusl,qichuje,create_time,product_md5)values" . rtrim($sql, ',');
                    M()->execute($ins_sql);
                    M()->execute("update new_spzpd set `flag`=0,createtime =now() where dept =$dept and edate ='".TODAY."' and `type`=$types order by createtime desc limit 1");
                    $this->response(array('resultcode'=>0,'resultmsg'=>"上传产品期初信息:$m,成功:$n;",'error'=>$error),'json');
                }else{
                    $this->response(array('resultcode'=>-1,'resultmsg'=>"上传0条!",'error'=>$error),'json');
                }
                exit();
            }elseif ($extension != 'xls' && $extension != 'xlsx'){
                $this->response(array('resultcode'=>-1,'resultmsg'=>'文件格式错误!'),'json');
            }
            $n = 0; //成功插入记录条数
            $m = 0;
            $objPHPExcel = \PHPExcel_IOFactory::load($uploadfile);
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow(); // 总行数
            if (excel_trim2($objPHPExcel->getActiveSheet()->getCell("A2")->getValue()) !='产品简称')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是产品简称'),'json');
            if (excel_trim2($objPHPExcel->getActiveSheet()->getCell("B2")->getValue()) !='制造部门')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是制造部门'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("C2")->getValue()) !='订单类别')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是订单类别'));
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("D2")->getValue()) !='档次')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是档次'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("E2")->getValue()) !='门框')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是门框'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("F2")->getValue()) !='框厚')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是框厚'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("G2")->getValue()) !='前板厚')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是前板厚'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("H2")->getValue()) !='后板厚')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是后板厚'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("I2")->getValue()) !='底框材料')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'I列不是底框材料'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("J2")->getValue()) !='门扇')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'J列不是门扇'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("K2")->getValue()) !='规格')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'K列不是规格'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("L2")->getValue()) !='开向')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'L列不是开向'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("M2")->getValue()) !='铰链')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是铰链'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("N2")->getValue()) !='花色')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'N列不是花色'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("O2")->getValue()) !='表面方式')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'O列不是表面方式'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("P2")->getValue()) !='表面要求')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'P列不是表面要求'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("Q2")->getValue()) !='窗花')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'Q列不是窗花'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("R2")->getValue()) !='猫眼')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'R列不是猫眼'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("S2")->getValue()) !='标牌')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'S列不是标牌'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("T2")->getValue()) !='主锁')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'T列不是主锁'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("U2")->getValue()) !='副锁')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'U列不是副锁'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("V2")->getValue()) !='锁把')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'V列不是锁把'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("W2")->getValue()) !='标件')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'W列不是标件'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("X2")->getValue()) !='包装品牌')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'X列不是包装品牌'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("Y2")->getValue()) !='包装方式')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'Y列不是包装方式'),'json');
            if(excel_trim2($objPHPExcel->getActiveSheet()->getCell("Z2")->getValue()) !='其他')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'Z列不是其他'),'json');

            if(excel_trim($objPHPExcel->getActiveSheet()->getCell("AA2")->getValue()) !='期初数量')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'AA列不是期初数量'),'json');
            if(excel_trim($objPHPExcel->getActiveSheet()->getCell("AB2")->getValue()) !='本月单价')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'AB列不是本月单价'),'json');
            if(excel_trim($objPHPExcel->getActiveSheet()->getCell("AC2")->getValue()) !='下月单价')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'AC列不是下月单价'),'json');
            if(excel_trim($objPHPExcel->getActiveSheet()->getCell("AD2")->getValue()) !='期初金额')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'AD列不是期初金额'),'json');


            //产品信息-基础数据
            $sql_check = "select * from new_spzmx_list ";
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
            for ($j = 3; $j <= $highestRow; $j++) { //第3行开始循环数据行
                $m++;
                if (empty($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue()) && empty($objPHPExcel->getActiveSheet()->getCell("B$j")->getValue())) //以1,2列判断是否为空行
                {}
                else{
                    $shangpinjc = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("A$j")->getValue());
                    $zhizaobm = excel_trim2($objPHPExcel->getActiveSheet()->getCell("B$j")->getValue());
                    $dingdanlb = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("C$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("C$j")->getValue());
                    $dangci = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("D$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("D$j")->getValue());
                    $menkuang = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("E$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("E$j")->getValue());
                    $kuanghou = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("F$j")->getValue())?'':number_format($objPHPExcel->getActiveSheet()->getCell("F$j")->getValue(),2,'.',''));
                    $qianbanhou = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("G$j")->getValue())?'':number_format($objPHPExcel->getActiveSheet()->getCell("G$j")->getValue(),2,'.',''));
                    $houbanhou = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("H$j")->getValue())?'':number_format($objPHPExcel->getActiveSheet()->getCell("H$j")->getValue(),2,'.',''));
                    $dikuangcl = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("I$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("I$j")->getValue());
                    $menshan = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("J$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("J$j")->getValue());
                    $guige = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("K$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("K$j")->getValue());
                    $kaixiang = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("L$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("L$j")->getValue());
                    $jiaolian = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("M$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("M$j")->getValue());
                    $huase = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("N$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("N$j")->getValue());
                    $biaomianfs = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("O$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("O$j")->getValue());
                    $biaomianyq = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("P$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("P$j")->getValue());
                    $chuanghua = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("Q$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("Q$j")->getValue());
                    $maoyan =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("R$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("R$j")->getValue());
                    $biaopai = excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("S$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("S$j")->getValue());
                    $zhusuo =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("T$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("T$j")->getValue());
                    $fusuo =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("U$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("U$j")->getValue());
                    $suoba =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("V$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("V$j")->getValue());
                    $biaojian =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("W$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("W$j")->getValue());
                    $baozhuangpp =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("X$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("X$j")->getValue());
                    $baozhuangfs =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("Y$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("Y$j")->getValue());
                    $qita =  excel_trim2(empty($objPHPExcel->getActiveSheet()->getCell("Z$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("Z$j")->getValue());
                    $guige = str_replace('×','*',$guige);

                    $sl = excel_trim(empty($objPHPExcel->getActiveSheet()->getCell("AA$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("AA$j")->getValue());
                    $bydj = excel_trim(empty($objPHPExcel->getActiveSheet()->getCell("AB$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("AB$j")->getValue());
                    $xydj = excel_trim(empty($objPHPExcel->getActiveSheet()->getCell("AC$j")->getValue())?0:$objPHPExcel->getActiveSheet()->getCell("AC$j")->getValue());

                    $k1= in_array($zhizaobm,$temp['zhizaobm'])?1:0;//制造部门
                    $k2= in_array($dingdanlb,$temp['dingdanlb'])?1:0;//订单类别
                    $k3= in_array($dangci,$temp['dangci'])?1:0;//档次
                    $k4= in_array($menkuang,$temp['menkuang'])?1:0;//门框
                    $k5= in_array($kuanghou,$temp['kuanghou'])?1:0;//框厚
                    $k6= in_array($qianbanhou,$temp['qianbanhou'])?1:0;//前板厚
                    $k7= in_array($houbanhou,$temp['houbanhou'])?1:0;//后板厚
                    $k8= in_array($dikuangcl,$temp['dikuangcl'])?1:0;//底框材料
                    $k9= in_array($menshan,$temp['menshan'])?1:0;//门扇
//                    $k10= in_array($guige,$temp['guige'])?1:0;//规格
                    $k11= in_array($kaixiang,$temp['kaixiang'])?1:0;//开向
                    $k12= in_array($jiaolian,$temp['jiaolian'])?1:0;//铰链
                    $k13= in_array($huase,$temp['huase'])?1:0;//花色
                    $k14= in_array($biaomianfs,$temp['biaomianfs'])?1:0;//表面方式
                    $k15= in_array($biaomianyq,$temp['biaomianyq'])?1:0;//表面要求
                    $k16= in_array($chuanghua,$temp['chuanghua'])?1:0;//窗花
                    $k17= in_array($maoyan,$temp['maoyan'])?1:0;//猫眼
                    $k18= in_array($biaopai,$temp['biaopai'])?1:0;//标牌
                    $k19= in_array($zhusuo,$temp['zhusuo'])?1:0;//主锁
                    $k20= in_array($fusuo,$temp['fusuo'])?1:0;//副锁
                    $k21= in_array($suoba,$temp['suoba'])?1:0;//锁把
                    $k22= in_array($biaojian,$temp['biaojian'])?1:0;//标件
                    $k23= in_array($baozhuangpp,$temp['baozhuangpp'])?1:0;//包装品牌
                    $k24= in_array($baozhuangfs,$temp['baozhuangfs'])?1:0;//包装方式

                    $k26= ctype_digit($sl)?1:0;//期初数量
                    $bydj = empty($bydj)?0:$bydj;
                    $k27= is_numeric($bydj)?1:0;//本月单价
                    $xydj = empty($xydj)?0:$xydj;
                    $k28= is_numeric($xydj)?1:0;//下月单价

                    //特殊制造部门
                    $teshuzhizaobm = array('舍零','返利','送货运费收入','外购');
                    $teshudangci = array('门套','锁把','锁体','锁芯','配件','把手');
                    //特殊门判断
                    $teshumen1 = array('防火门');
                    $teshumen2 = array('钢木门','强化门','卫浴门','木门');
                    //零碎门套、锁把等处理
                    //1---不填(可填)
                    if (in_array($zhizaobm,$teshuzhizaobm) || in_array($dangci,$teshudangci)){
                        $k3 = $k2 = $k4 = $k5 = $k6 = $k7 = $k8 = $k9 = $k11 = $k12 = $k13 = $k14 = $k15 = $k16
                            = $k17 = $k18 = $k19 = $k20 = $k21 = $k22 = $k23 = $k24 = 1;
                    }
                    if (in_array($dangci,$teshumen1)){//如果是防火门---表面要求、窗花、标牌不填
                        $k15 = $k16 = $k18 = 1;
                    }
                    if(in_array($dangci,$teshumen2)){
                        $k2 = $k4 = $k5 = $k6 = $k7 = $k8 = $k9 = $k11 = $k12 = $k14 = $k15 = $k16 = $k17 = $k18 = $k19 = $k20 = $k21 =
                        $k22 = $k23 = $k24 = 1;
                    }

                    //录入选项判断
                    //其中,配件行C-J列必须为空,若有则会判断为产品信息行
                    $flag = empty($k1 && $k2 && $k3 && $k4 && $k5 && $k6 && $k7 && $k8 && $k9 && $k11 && $k12 && $k13
                        && $k14 && $k15 && $k16 && $k17 && $k18 && $k19 && $k20 && $k21 && $k22 && $k23 && $k24  && $k26
                        && $k27  && $k28)?0:1;
                    if ($flag){
                        $create_time = date("Y-m-d H:i:s");
                        $add_key = $zhizaobm.$dingdanlb.$dangci.$menkuang.floatval($kuanghou).floatval($qianbanhou).floatval($houbanhou).$dikuangcl.$menshan.$guige.$kaixiang.$jiaolian.$huase.$biaomianfs.$biaomianyq.$chuanghua.$maoyan.$biaopai.$zhusuo.$fusuo.$suoba.$biaojian.$baozhuangpp.$baozhuangfs.$qita.$dept.$date.$types;
                        $product_md5 = md5($add_key);
                        $qichuje = $sl*$bydj;
                        $sql .= "($types,$dept,'$date','" . $shangpinjc . "','" . $zhizaobm . "','" . $dingdanlb . "','" . $dangci . "','" . $menkuang . "','" . $kuanghou . "','" . $qianbanhou . "','" . $houbanhou . "','" . $dikuangcl . "','" . $menshan . "','" . $guige . "','".$kaixiang."','".$jiaolian."','".$huase."','".$biaomianfs."','".$biaomianyq."','".$chuanghua."','".$maoyan."','".$biaopai."','".$zhusuo."','".$fusuo."','".$suoba."','".$biaojian."','".$baozhuangpp."','".$baozhuangfs."','".$qita."', $bydj , $xydj , $sl ,'$qichuje','$create_time','$product_md5'),";
                        $n++;
                    }else{
                        $code = '';
                        $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                        $code .= $k2?'':(empty($dingdanlb)?'订单类别列不能为空；':"订单类别:[".$dingdanlb."]不存在；");
                        $code .= $k3?'':(empty($dangci)?'档次列不能为空；':"档次:[".$dangci."]不存在；");
                        $code .= $k4?'':(empty($menkuang)?'门框列不能为空；':"门框:[".$menkuang."]不存在；");
                        $code .= $k5?'':(empty($kuanghou)?'框厚列不能为空；':"框厚:[".$kuanghou."]不存在；");
                        $code .= $k6?'':(empty($qianbanhou)?'前板厚列不能为空；':"前板厚:[".$qianbanhou."]不存在；");
                        $code .= $k7?'':(empty($houbanhou)?'后板厚列不能为空；':"后板厚:[".$houbanhou."]不存在；");
                        $code .= $k8?'':(empty($dikuangcl)?'底框材料列不能为空；':"底框材料:[".$dikuangcl."]不存在；");
                        $code .= $k9?'':(empty($menshan)?'门扇列不能为空；':"门扇:[".$menshan."]不存在；");
                        $guige_sm = "";
                        $guige_judge = explode('*',strval($guige));
                        foreach($guige_judge as $val){
                            if(strlen($val) == 3 || strlen($val) == 4){
                                continue;
                            }elseif(in_array($zhizaobm,$teshuzhizaobm) || in_array($dangci,$teshudangci)){
                                continue;
                            }else{
                                $guige_sm = '规格需为3或4位数*3或4位数,error_line : '.$j."； ";
                            }
                        }
                        $code .= empty($guige)?'规格列不能为空；':$guige_sm;
                        $code .= $k11?'':(empty($kaixiang)?'开向列不能为空；':"开向:[".$kaixiang."]不存在；");
                        $code .= $k12?'':(empty($jiaolian)?'铰链列不能为空；':"铰链:[".$jiaolian."]不存在；");
                        $code .= $k13?'':(empty($huase)?'花色列不能为空；':"花色:[".$huase."]不存在；");
                        $code .= $k14?'':(empty($biaomianfs)?'表面方式列不能为空；':"表面方式:[".$biaomianfs."]不存在；");
                        $code .= $k15?'':(empty($biaomianyq)?'表面要求列不能为空；':"表面要求:[".$biaomianyq."]不存在；");

                        $code .= $k16?'':(empty($chuanghua)?'窗花列不能为空；':"窗花:[".$chuanghua."]不存在；");
                        $code .= $k17?'':(empty($maoyan)?'猫眼列不能为空；':"猫眼:[".$maoyan."]不存在；");
                        $code .= $k18?'':(empty($biaopai)?'标牌列不能为空；':"标牌:[".$biaopai."]不存在；");
                        $code .= $k19?'':(empty($zhusuo)?'主锁列不能为空；':"主锁:[".$zhusuo."]不存在；");
                        $code .= $k20?'':(empty($fusuo)?'副锁列不能为空；':"副锁:[".$fusuo."]不存在；");
                        $code .= $k21?'':(empty($suoba)?'锁把列不能为空；':"锁把:[".$suoba."]不存在；");
                        $code .= $k22?'':(empty($biaojian)?'标件列不能为空；':"标件:[".$biaojian."]不存在；");
                        $code .= $k23?'':(empty($baozhuangpp)?'包装品牌列不能为空；':"包装品牌:[".$baozhuangpp."]不存在；");
                        $code .= $k24?'':(empty($baozhuangfs)?'包装方式列不能为空；':"包装方式:[".$baozhuangfs."]不存在；");
                        $code .= $k26?'':'期初数量录入不正确；';
                        $code .= $k27?'':'本月单价非数字；';
                        $code .= $k28?'':'下月单价非数字；';
                        $error[] = array(
                            array('type'=>1,'value'=>$j),
                            array('type'=>1,'value'=>$shangpinjc),
                            array('type'=>$k1,'value'=>$zhizaobm),
                            array('type'=>$k2,'value'=>$dingdanlb),
                            array('type'=>$k3,'value'=>$dangci),
                            array('type'=>$k4,'value'=>$menkuang),
                            array('type'=>$k5,'value'=>$kuanghou),
                            array('type'=>$k6,'value'=>$qianbanhou),
                            array('type'=>$k7,'value'=>$houbanhou),
                            array('type'=>$k8,'value'=>$dikuangcl),
                            array('type'=>$k9,'value'=>$menshan),
                            array('type'=>empty($guige)?0:1,'value'=>$guige),
                            array('type'=>$k11,'value'=>$kaixiang),
                            array('type'=>$k12,'value'=>$jiaolian),
                            array('type'=>$k13,'value'=>$huase),
                            array('type'=>$k14,'value'=>$biaomianfs),
                            array('type'=>$k15,'value'=>$biaomianyq),
                            array('type'=>$k16,'value'=>$chuanghua),
                            array('type'=>$k17,'value'=>$maoyan),
                            array('type'=>$k18,'value'=>$biaopai),
                            array('type'=>$k19,'value'=>$zhusuo),
                            array('type'=>$k20,'value'=>$fusuo),
                            array('type'=>$k21,'value'=>$suoba),
                            array('type'=>$k22,'value'=>$biaojian),
                            array('type'=>$k23,'value'=>$baozhuangpp),
                            array('type'=>$k24,'value'=>$baozhuangfs),

                            array('type'=>1,'value'=>$qita),
                            array('type'=>$k26,'value'=>$sl),
                            array('type'=>$k27,'value'=>$bydj),
                            array('type'=>$k28,'value'=>$xydj),
                            array('type'=>0,'value'=>$code)
                        );
                    }
                }
            }
            unlink($uploadfile);
            if ($sql !=''){
                $firstDay=date('Y-m-01');
                $del = "delete from new_spzmxqc where `type`=$types and dept = $dept and `month`='$date' and
                product_md5 not in (select product_md5 from new_shouzhimx where dept=$dept and type=$types and create_date>'$firstDay' union select product_md5 from new_xsmx where dept=$dept and type=$types and date>'$firstDay' )
                ";
                M()->execute("delete from new_spzmxqc where `type`=$types and dept = $dept and `month`='$date' and
                product_md5 not in (select product_md5 from new_shouzhimx where dept=$dept and type=$types and create_date>'$firstDay' union select product_md5 from new_xsmx where dept=$dept and type=$types and date>'$firstDay' )
                ");
                $ins_sql = "replace into new_spzmxqc(type,dept,month,shangpinjc,zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita,benyuedj,xiayuedj,qichusl,qichuje,create_time,product_md5)values" . rtrim($sql, ',');
                M()->execute($ins_sql);
                M()->execute("update new_spzpd set `flag`=0,createtime =now() where dept =$dept and edate ='".TODAY."' and `type`=$types order by createtime desc limit 1");
                $this->response(array('resultcode'=>0,'resultmsg'=>"上传产品期初信息:$m,成功:$n;",'error'=>$error),'json');
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
        $sql = "select * from new_spzmx_list";
        $attr = M()->query($sql);
        $arr = array(
            array('label'=>'zhizaobm','name'=>'制造部门','option'=>array()),
            array('label'=>'dingdanlb','name'=>'订单类别 ','option'=>array()),//大类
            array('label'=>'dangci','name'=>'档次','option'=>array()),//
            array('label'=>'menkuang','name'=>'门框','option'=>array()),//
            array('label'=>'kuanghou','name'=>'框厚','option'=>array()),//板厚
            array('label'=>'qianbanhou','name'=>'前板厚','option'=>array()),//板厚
            array('label'=>'houbanhou','name'=>'后板厚','option'=>array()),//板厚
            array('label'=>'dikuangcl','name'=>'底框材料','option'=>array()),//板厚
            array('label'=>'menshan','name'=>'门扇','option'=>array()),//板厚
            array('label'=>'guige','name'=>'规格','option'=>array()),//规格
            array('label'=>'kaixiang','name'=>'开向','option'=>array()),//开向
            array('label'=>'jiaolian','name'=>'铰链','option'=>array()),//开向
            array('label'=>'huase','name'=>'花色','option'=>array()),//花色
            array('label'=>'biaomianfs','name'=>'表面方式','option'=>array()),//表面要求
            array('label'=>'biaomianyq','name'=>'表面要求','option'=>array()),//表面要求
            array('label'=>'chuanghua','name'=>'窗花','option'=>array()),//门框
            array('label'=>'maoyan','name'=>'猫眼','option'=>array()),//锁具
            array('label'=>'biaopai','name'=>'标牌','option'=>array()),//锁具
            array('label'=>'zhusuo','name'=>'主锁','option'=>array()),//锁具
            array('label'=>'fusuo','name'=>'副锁','option'=>array()),//锁具
            array('label'=>'suoba','name'=>'锁把','option'=>array()),//锁具
            array('label'=>'biaojian','name'=>'标件','option'=>array()),//锁具
            array('label'=>'baozhuangpp','name'=>'包装品牌','option'=>array()),//锁具
            array('label'=>'baozhuangfs','name'=>'包装方式','option'=>array()),//锁具
            array('label'=>'qita','name'=>'其他','option'=>array()),//锁具
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
     * @param string $type {1：有效商品帐，0：无效商品帐}
     * @param string $delete {1：删除当天的数据，0：不删除当天的数据}
     */
    public function submit($token="",$type=1,$delete=0){
        header("Access-Control-Allow-Origin: *");
        $userinfo = checktoken($token);
        if (! $userinfo) {
            $this->response(retmsg(- 2), 'json');
            return;
        }
        $dept = $userinfo['dept_id'];
        $sql = '';
        $date = date("Y-m",strtotime(TODAY));
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
        $sql_check = "select * from new_spzmx_list ";
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
        $data = json_decode(file_get_contents("php://input"),true);
        $firstDay=date('Y-m-01');
        if($delete ==1){
            M()->execute("delete from new_spzmxqc where `type`=$type and dept = $dept and `month`='$date' and 
                product_md5 not in (select product_md5 from new_shouzhimx where dept=$dept and type=$type and create_date>'$firstDay' union select product_md5 from new_xsmx where dept=$dept and type=$type and date>'$firstDay' )
                ");
        }
        foreach($data['data'] as $key=>$v){
            if(excel_trim($v['create_time']) =='')
                $time = date("Y-m-d H:i:s");
            else
                $time = $v['create_time'];
            if (excel_trim($v['zhizaobm']) =='必填列')
                continue;
            $shangpinjc =excel_trim($v['shangpinjc']) ;
            $zhizaobm =excel_trim($v['zhizaobm']) ;
            $dingdanlb =excel_trim($v['dingdanlb']) ;
            $dangci = excel_trim($v['dangci']);
            $menkuang = excel_trim($v['menkuang']);
            $kuanghou = excel_trim($v['kuanghou']);//框厚
            $kuanghou = number_format($kuanghou,2,'.','');

            $qianbanhou = excel_trim($v['qianbanhou']);//前板厚
            $qianbanhou = number_format($qianbanhou,2,'.','');

            $houbanhou = excel_trim($v['houbanhou']);//后板厚
            $houbanhou = number_format($houbanhou,2,'.','');

            $dikuangcl = excel_trim($v['dikuangcl']);
            $menshan = excel_trim($v['menshan']);
            $guige = excel_trim(str_replace('×','*',$v['guige']));
            $kaixiang = excel_trim($v['kaixiang']);
            $jiaolian = excel_trim($v['jiaolian']);
            $huase = excel_trim($v['huase']);
            $biaomianfs = excel_trim($v['biaomianfs']);
            $biaomianyq = excel_trim($v['biaomianyq']);
            $chuanghua = excel_trim($v['chuanghua']);
            $maoyan =  excel_trim($v['maoyan']);
            $biaopai = excel_trim($v['biaopai']);
            $zhusuo =  excel_trim($v['zhusuo']);
            $fusuo =  excel_trim($v['fusuo']);
            $suoba =  excel_trim($v['suoba']);
            $biaojian =  excel_trim($v['biaojian']);
            $baozhuangpp =  excel_trim($v['baozhuangpp']);
            $baozhuangfs =  excel_trim($v['baozhuangfs']);
            $qita = excel_trim($v['qita']);
            $sl =  excel_trim($v['qichusl']);
            $bydj =  excel_trim($v['benyuedj']);
            $xydj = excel_trim($v['xiayuedj']);

            $k1= in_array($zhizaobm,$temp['zhizaobm'])?1:0;
            $k0= in_array($dingdanlb,$temp['dingdanlb'])?1:0;
            $k2= in_array($dangci,$temp['dangci'])?1:0;
            $k3= in_array($menkuang,$temp['menkuang'])?1:0;
            $k4= in_array($kuanghou,$temp['kuanghou'])?1:0;
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
            $k15= in_array($chuanghua,$temp['chuanghua'])?1:0;
            $k16= in_array($maoyan,$temp['maoyan'])?1:0;
            $k17= in_array($biaopai,$temp['biaopai'])?1:0;
            $k18= in_array($zhusuo,$temp['zhusuo'])?1:0;
            $k19= in_array($fusuo,$temp['fusuo'])?1:0;
            $k20= in_array($suoba,$temp['suoba'])?1:0;
            $k21= in_array($biaojian,$temp['biaojian'])?1:0;
            $k22= in_array($baozhuangpp,$temp['baozhuangpp'])?1:0;
            $k23= in_array($baozhuangfs,$temp['baozhuangfs'])?1:0;

            $k24= is_numeric($sl)?1:0;
            $k25= is_numeric($bydj)?1:0;
            $k26= is_numeric($xydj)?1:0;

            //特殊制造部门
            $teshuzhizaobm = array('舍零','返利','送货运费收入','外购');
            $teshudangci = array('门套','锁把','锁体','锁芯','配件');
            //特殊门判断
            $teshumen1 = array('防火门');
            $teshumen2 = array('钢木门','强化门','卫浴门','木门');
            //零碎门套、锁把等处理 1---不填(可填)
            if (in_array($zhizaobm,$teshuzhizaobm) || in_array($dangci,$teshudangci)){
                $k0 = $k2 = $k3 = $k4 = $k5 = $k6 = $k7 = $k8 = $k10 = $k11 = $k12 = $k13 = $k14 = $k15 = $k16
                    = $k17 = $k18 = $k19 = $k20 = $k21 = $k22 = $k23  = 1;
            }
            if (in_array($dangci,$teshumen1)){//如果是防火门---表面要求、窗花、标牌不填
                $k14 = $k15 = $k17 = 1;
            }
            if(in_array($dangci,$teshumen2)){
                $k0 = $k3 = $k4 = $k5 = $k6 = $k7 = $k8 = $k10 = $k11 = $k13 = $k14 = $k15 = $k16 = $k17 = $k18 = $k19 = $k20 = $k21 =
                $k22 = $k23  = 1;
            }

            $flag = $k0 && $k1 && $k2 && $k3 && $k4 && $k5 && $k6 && $k7 && $k8 && $k10 && $k11 && $k12 && $k13
                && $k14 && $k15 && $k16 && $k17 && $k18 && $k19 && $k20 && $k21 && $k22 && $k23 && $k24 && $k25
                && $k26;
            if ($flag){
                $add_key = $zhizaobm.$dingdanlb.$dangci.$menkuang.floatval($kuanghou).floatval($qianbanhou).floatval($houbanhou).$dikuangcl.$menshan.$guige.$kaixiang.$jiaolian.$huase.$biaomianfs.$biaomianyq.$chuanghua.$maoyan.$biaopai.$zhusuo.$fusuo.$suoba.$biaojian.$baozhuangpp.$baozhuangfs.$qita.$dept.$date.$type;
                $qichuje = $sl*$bydj;
                $pro_md5 = md5($add_key);
                $sql .= "($type,$dept,'$date','$time','".$shangpinjc."','".$zhizaobm."','".$dingdanlb."','".$dangci."','".$menkuang."','".$kuanghou."','".$qianbanhou."','".$houbanhou."','".$dikuangcl."','".$menshan."','".$guige."','".$kaixiang."','".$jiaolian."','".$huase."','".$biaomianfs."','".$biaomianyq."','".$chuanghua."','".$maoyan."','".$biaopai."','".$zhusuo."','".$fusuo."','".$suoba."','".$biaojian."','".$baozhuangpp."','".$baozhuangfs."','".$qita."',".$bydj.",".$xydj.",".$sl.",".$qichuje.",'".$pro_md5."'),";
            }else{
                $code = '';
                $code .= $k1?'':(empty($zhizaobm)?'制造部门列不能为空; ':"制造部门:[".$zhizaobm."]不存在； ");
                $code .= $k0?'':(empty($dingdanlb)?'订单类别列不能为空; ':"订单类别:[".$dingdanlb."]不存在； ");
                $code .= $k2?'':(empty($dangci)?'档次列不能为空; ':"档次:[".$dangci."]不存在； ");
                $code .= $k3?'':(empty($menkuang)?'门框列不能为空; ':"门框:[".$menkuang."]不存在； ");
                $code .= $k4?'':(empty($kuanghou)?'框厚列不能为空；':"框厚:[".$kuanghou."]不存在； ");
                $code .= $k5?'':(empty($qianbanhou)?'前板厚列不能为空；':"前板厚:[".$qianbanhou."]不存在； ");
                $code .= $k6?'':(empty($houbanhou)?'后板厚列不能为空; ':"后板厚:[".$houbanhou."]不存在； ");
                $code .= $k7?'':(empty($dikuangcl)?'底框材料列不能为空； ':"底框材料:[".$dikuangcl."]不存在；");
                $code .= $k8?'':(empty($menshan)?'门扇列不能为空；':"门扇:[".$menshan."]不存在； ");
                $guige_judge = explode('*',strval($guige));
                foreach($guige_judge as $val){
                    if(strlen($val) == 3 || strlen($val) == 4){
                        continue;
                    }elseif(in_array($zhizaobm,$teshuzhizaobm) || in_array($dangci,$teshudangci)){
                        continue;
                    }else{
                        $guige_sm = '规格需为3或4位数*3或4位数,error_line : '.$key."； ";
                    }
                }
                $code .= empty($guige)?'规格列不能为空；':$guige_sm;
                $code .= $k10?'':(empty($kaixiang)?'开向列不能为空； ':"开向:[".$kaixiang."]不存在； ");
                $code .= $k11?'':(empty($jiaolian)?'铰链列不能为空； ':"铰链:[".$jiaolian."]不存在； ");
                $code .= $k12?'':(empty($huase)?'花色列不能为空； ':"花色:[".$huase."]不存在；");
                $code .= $k13?'':(empty($biaomianfs)?'表面方式列不能为空；':"表面方式:[".$biaomianfs."]不存在； ");
                $code .= $k14?'':(empty($biaomianyq)?'表面要求列不能为空；':"表面要求:[".$biaomianyq."]不存在； ");

                $code .= $k15?'':(empty($chuanghua)?'窗花列不能为空；':"窗花:[".$chuanghua."]不存在； ");
                $code .= $k16?'':(empty($maoyan)?'猫眼列不能为空； ':"猫眼:[".$maoyan."]不存在； ");
                $code .= $k17?'':(empty($biaopai)?'标牌列不能为空；':"标牌:[".$biaopai."]不存在； ");
                $code .= $k18?'':(empty($zhusuo)?'主锁列不能为空；':"主锁:[".$zhusuo."]不存在；");
                $code .= $k19?'':(empty($fusuo)?'副锁列不能为空； ':"副锁:[".$fusuo."]不存在； ");
                $code .= $k20?'':(empty($suoba)?'锁把列不能为空； ':"锁把:[".$suoba."]不存在； ");
                $code .= $k21?'':(empty($biaojian)?'标件列不能为空； ':"标件:[".$biaojian."]不存在； ");
                $code .= $k22?'':(empty($baozhuangpp)?'包装品牌列不能为空； ':"包装品牌:[".$baozhuangpp."]不存在； ");
                $code .= $k23?'':(empty($baozhuangfs)?'包装方式列不能为空；':"装方式:[".$baozhuangfs."]不存在； ");
                $code .= $k24?'':'期初数量录入不正确； ';
                $code .= $k25?'':'本月单价非数字；';
                $code .= $k26?'':'下月单价非数字；';
                $error[] = array(
                    array('type'=>1,'value'=>$key),//错误的行数
                    array('type'=>1,'value'=>$shangpinjc),
                    array('type'=>$k1,'value'=>$zhizaobm),
                    array('type'=>$k0,'value'=>$dingdanlb),
                    array('type'=>$k2,'value'=>$dangci),
                    array('type'=>$k3,'value'=>$menkuang),
                    array('type'=>$k4,'value'=>$kuanghou),
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
                    array('type'=>$k15,'value'=>$chuanghua),
                    array('type'=>$k16,'value'=>$maoyan),
                    array('type'=>$k17,'value'=>$biaopai),
                    array('type'=>$k18,'value'=>$zhusuo),
                    array('type'=>$k19,'value'=>$fusuo),
                    array('type'=>$k20,'value'=>$suoba),
                    array('type'=>$k21,'value'=>$biaojian),
                    array('type'=>$k22,'value'=>$baozhuangpp),
                    array('type'=>$k23,'value'=>$baozhuangfs),
                    array('type'=>1,'value'=>$qita),
                    array('type'=>$k24,'value'=>$sl),
                    array('type'=>$k25,'value'=>$bydj),
                    array('type'=>$k26,'value'=>$xydj),
                    array('type'=>0,'value'=>$code)
                );
            }
        }
        if ($sql !=''){

            M()->execute("delete from new_spzmxqc where `type`=$type and dept = $dept and `month`='$date' and 
                product_md5 not in (select product_md5 from new_shouzhimx where dept=$dept and type=$type and create_date>'$firstDay' union select product_md5 from new_xsmx where dept=$dept and type=$type and date>'$firstDay' )
                ");
            $sql_ins = "replace into new_spzmxqc(`type`,dept,`month`,`create_time`,shangpinjc,zhizaobm,dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita,benyuedj,xiayuedj,qichusl,qichuje,product_md5)values" . rtrim($sql, ',');
            $result = M()->execute($sql_ins);
            M()->execute("update new_spzpd set `flag`=0,createtime =now() where dept =$dept and edate ='".TODAY."' and `type`=$type order by createtime desc limit 1");
            $this->response(array('resultcode'=>0,'resultmsg'=>"保存成功",'error'=>$error),'json');
        }else{
            $this->response(array('resultcode'=>-1,'resultmsg'=>'保存失败','error'=>$error),'json');
        }
    }

    private function input_csv($handle) {
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
}