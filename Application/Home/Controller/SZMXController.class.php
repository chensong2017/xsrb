<?php
namespace Home\Controller;

use Think\Controller\RestController;
/**
 * 收支明细
 * @author chensong
 *
 */
class SZMXController extends RestController{

    /**
     * 提交收支明细数据
     * @param string $token
     * @param number $type,type=1有效收支明细，type=0无效收支明细
     * @param post json array
     * @param $flag 后台上传导入excel
     */
    public function submit($token='',$type=1,$data=null,$flag=false,$totalCount=0,$delete=0){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        //$dept_id=1;
		
        $date=TODAY;
        $month=date("Y-m",strtotime(TODAY));
        $json=json_decode(file_get_contents("php://input"),true);
        $jsonData=$json['data'];
        if($flag){
            $jsonData=$data;
        }
        if(empty($jsonData)&&$delete!=1){
            $this->response(retmsg(-1,null,'请传入数据！'),'json');
        }

        //产品信息-基础数据
        $sql_check = "select zhizaobm,dalei,feibiao,banhou,biaomianyq,menkuang,huase,suoju,kaixiang from spzmx_list ";
        $re = M()->query($sql_check);
        $temp1 = array();
        foreach($re as $key=>$val){
            foreach($val as $k1=>$v1){
                if (empty($v1))
                    continue;
                if ( !in_array($temp1[$k1],$v1))
                    $temp1[$k1][] = $v1;
            }
        }

        $temp=array();
        foreach ($jsonData as $row=>$data){
            $zhizaobm=$data['zhizaobm'];
            $dalei=$data['dalei'];
            $feibiao=empty(trim($data['feibiao']))?'':trim($data['feibiao']);
            $banhou=empty(trim($data['banhou']))?'':trim($data['banhou']);
            $guige=empty(trim($data['guige']))?'':trim($data['guige']);
            $biaomianyq=empty(trim($data['biaomianyq']))?'':trim($data['biaomianyq']);
            $menkuang=empty(trim($data['menkuang']))?'':trim($data['menkuang']);
            $huase=empty(trim($data['huase']))?'':trim($data['huase']);
            $suoju=empty(trim($data['suoju']))?'':trim($data['suoju']);
            $kaixiang=empty(trim($data['kaixiang']))?'':trim($data['kaixiang']);
            $qita=empty(trim($data['qita']))?'':trim($data['qita']);
            $banhou = str_replace('×','*',$banhou);
            $guige = str_replace('×','*',$guige);
            $data['feibiao']=$feibiao;
            $data['banhou']=$banhou;
            $data['guige']=$guige;
            $data['biaomianyq']=$biaomianyq;
            $data['menkuang']=$menkuang;
            $data['huase']=$huase;
            $data['suoju']=$suoju;
            $data['kaixiang']=$kaixiang;
            $data['qita']=$qita;
            //查询此期初是否存在

            $sql="select benyuedj from spzmxqc where zhizaobm='$zhizaobm' and dalei='$dalei' and 
            feibiao='$feibiao' and banhou='$banhou' and guige='$guige' and 
            biaomianyq='$biaomianyq' and menkuang='$menkuang' and huase='$huase' and 
            suoju='$suoju' and kaixiang='$kaixiang'  and dept='$dept_id' and 
            type=$type and date='$month' and qita='$qita' ";

            $ret=M()->query($sql);
            //$data['danjia']=$ret[0]['benyuedj'];
            //如果没有录入单价（后台上传excel时）则取期初单价

            if (!empty($ret[0]['benyuedj']))
                $data['danjia']=$ret[0]['benyuedj'];


            $k1= in_array($zhizaobm,$temp1['zhizaobm'])?1:0;
            $k2= in_array($dalei,$temp1['dalei'])?1:0;
            $k3= in_array($feibiao,$temp1['feibiao'])?1:0;
            $k4= in_array($banhou,$temp1['banhou'])?1:0;
            $k5= in_array($biaomianyq,$temp1['biaomianyq'])?1:0;
            $k6= in_array($menkuang,$temp1['menkuang'])?1:0;
            $k7= in_array($huase,$temp1['huase'])?1:0;
            $k8= in_array($suoju,$temp1['suoju'])?1:0;
            $k9= in_array($kaixiang,$temp1['kaixiang'])?1:0;
            $k10= is_numeric($data['shuliang'])?1:0;
            $k11= is_numeric($data['danjia'])?1:0;
            $k12= empty($data['shouzhilb'])?0:1;
            $k13= empty($data['shouzzhimx'])?0:1;


            //期初不存在则提示错误信息
            if(empty($ret)){

                $code = '';
                $code .= $k12?'':'收支列表不能为空；';
                $code .= $k13?'':'收支明细不能为空；';
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
                $code .= $k10?'':'数量非数字；';
                $code .= $k11?'':'本月单价非数字；';
                if (empty($code)){
                    $code = '该产品信息行在期初中未录入!';
                }
				$teshuzhizaobm = array('舍零','返利','送货运费收入');
                if (in_array($zhizaobm,$teshuzhizaobm)){
                    $code = '该产品信息行在期初中未录入!';
                    $k1=1;$k2=1;$k3=1;$k4=1;$k5=1;$k6=1;$k7=1;$k8=1;$k9=1;$k10=1;$k11=1;
                }
				if($zhizaobm)
                $errorInfo[] = array(
                    array('type'=>1,'value'=>$data['hanghao']),
                    array('type'=>1,'value'=>$date),
                    array('type'=>$k12,'value'=>$data['shouzhilb']),
                    array('type'=>$k13,'value'=>$data['shouzzhimx']),
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
                    array('type'=>$k10,'value'=>$data['shuliang']),
                    array('type'=>$k11,'value'=>$data['danjia']),
                    array('type'=>0,'value'=>$code)
                );
                $msg.="\n".$zhizaobm.$dalei.$feibiao.$banhou.$guige.$biaomianyq.$menkuang.
                    $huase.$suoju.$kaixiang.$qita;
                //此行不存入数据库
                continue;

            }
            $data['dept']=$dept_id;
            $data['create_date']=$date;
            $data['type']=$type;
            array_push($temp, $data);
        }
        if(!empty($temp)){
            M()->execute("delete from shouzhimx where dept=$dept_id and type=$type and create_date='$date' and pandianId = 0");
        }
        if ($delete==1){
            M()->execute("delete from shouzhimx where dept=$dept_id and type=$type and create_date='$date' ");
        }
        if(!empty($temp)){
            $result=M('shouzhimx')->addAll($temp);
        }
        //更新日报表
        //查询收支明细和销售明细统计
        $sql="select shouzhilb,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,
        qita, sum(shuliang) as shuliang from shouzhimx where dept=$dept_id and type=$type and create_date='$date' 
        group by shouzhilb,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita
        union all 
        select xiaoshoums as shouzhilb,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,
        kaixiang,qita, sum(xiaoliang) as shuliang from xsmx where dept=$dept_id and type=$type and date='$date' 
        group by xiaoshoums,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita";
        $ret=M()->query($sql);
        //将查询结果转成spzmx结构
        //每一种产品对应的收支类别统计
        $dataArray=array();
        foreach ($ret as $row=>$val){
            //判断唯一产品组合是否存在
            $index=-1;//产品位置标示
            $val['qita']=empty(trim($val['qita']))?"":$val['qita'];//为0和“ ”都去“”
            foreach ($dataArray as $key=>$value){
                //产品唯一组合
                $value['qita']=empty($value['qita'])?"":$value['qita'];
                if($val['zhizaobm']==$value['zhizaobm']&&$val['dalei']==$value['dalei']&&
                    $val['feibiao']==$value['feibiao']&&$val['banhou']==$value['banhou']&&
                    $val['guige']==$value['guige']&&$val['biaomianyq']==$value['biaomianyq']&&
                    $val['menkuang']==$value['menkuang']&&$val['huase']==$value['huase']&&
                    $val['suoju']==$value['suoju']&&$val['kaixiang']==$value['kaixiang']&&
                    $val['qita']==$value['qita']){
                    $index=$key;
                    break;
                    /* if(!empty(trim($val['qita']))&&trim($val['qita'])!='0'){
                        if(trim($val['qita'])==trim($value['qita'])){
                            $index=$key;
                            break;
                        }
                    }
                    else{
                        $index=$key;
                        break;
                    } */
                }
            }
            //初始赋值（数据项）
            list($val['dbsr'],$val['zcsr'],$val['phsr'],$val['shsh'],$val['qtsr'],$val['dbzc'],$val['zczc'],
                $val['phzc'],$val['shzc'],$val['qtzc'],$val['zfxszc'],$val['kfxszc'])=array(0,0,0,0,0,0,0,0,0,0,0,0);
            $tempVal=empty($val['shuliang'])?0:$val['shuliang'];
            $error=0;
            //更新对应数据项值
            switch(trim($val['shouzhilb'])){
                case '调拨收入':
                    $tempKey='dbsr';
                    $val[$tempKey]=$tempVal;
                    break;
                case '暂存收入':
                    $tempKey='zcsr';
                    $val[$tempKey]=$tempVal;
                    break;
                case '铺货收入':
                    $tempKey='phsr';
                    $val[$tempKey]=$tempVal;
                    break;
                case '送货收回':
                    $tempKey='shsh';
                    $val[$tempKey]=$tempVal;
                    break;
                case '其他收入':
                    $tempKey='qtsr';
                    $val[$tempKey]=$tempVal;
                    break;
                case '调拨支出':
                    $tempKey='dbzc';
                    $val[$tempKey]=$tempVal;
                    break;
                case '暂存支出':
                    $tempKey='zczc';
                    $val[$tempKey]=$tempVal;
                    break;
                case '铺货支出':
                    $tempKey='phzc';
                    $val[$tempKey]=$tempVal;
                    break;
                case '送货支出':
                    $tempKey='shzc';
                    $val[$tempKey]=$tempVal;
                    break;
                case '其他支出':
                    $tempKey='qtzc';
                    $val[$tempKey]=$tempVal;
                    break;
                case '库房销售':
                    $tempKey='kfxszc';
                    $val[$tempKey]=$tempVal;
                    break;
                case '直发销售':
                    $tempKey='zfxszc';
                    $val[$tempKey]=$tempVal;
                    break;
                default:
                    $error=1;
            }
            if($error){
                continue;
            }
            $val['type']=$type;
            $val['dept']=$dept_id;
            $val['date']=$date;
            if(empty($val['qita'])){
                $val['qita']='';
            }
            //插入产品信息及对应行的收支类别信息
            if($index==-1){
                array_push($dataArray,$val);
            }
            //更新收支类别信息
            else{
                $dataArray[$index][$tempKey]=$tempVal;
            }
        }
        //删除缓存数据
        M()->execute("delete from spzmx where dept=$dept_id and date='$date' and type=$type ");
        /*  //插入最新的收支明细(字段不一样无法批量执行)
        foreach($dataArray as $record){
            M('spzmx')->add($record);
        } */
        M('spzmx')->addAll($dataArray);
        $check_pd = M()->query("select flag from spzpd where dept =$dept_id and `type`=$type and yuefen ='$month' and edate ='$date' ");
        if (count($check_pd)){  //当天盘点过后,再次录入防盗门销售明细数据.flag设置为0
            M()->execute("update spzpd set flag =0,createtime=now() where dept =$dept_id and `type`=$type and edate ='$date' order by createtime desc limit 1");
        }

        //更新结存
        $sql="update spzmx set jiecun=dbsr+zcsr+phsr+shsh+qtsr-ifnull(zfxszc,0)-ifnull(kfxszc,0)-dbzc-zczc-phzc-shzc-qtzc 
        where dept=$dept_id and type=$type and date='$date'";
        M()->execute($sql);

        $successCount=count($temp);//插入收支明细表成功行数
        $resultCode=$successCount>0||$delete==1?0:-1;
        if(!$flag){
            if(!empty($msg)){
                $this->response(retmsg($resultCode,null,"产品：".$msg." 未录入期初！"),'json');
            }
            else
                $this->response(retmsg($resultCode),'json');
        }
        else{
            $m="共 $totalCount 行，导入成功".$successCount."行！";
/*             if(!empty($msg)){
                $m.=" 产品：".$msg." 未录入期初！";
            } */
            $this->response(array('resultcode'=>-1,'resultmsg'=>$m,'error'=>$errorInfo),'json');
        }


    }
    public function search($token='',$type=1,$sdate='',$edate=''){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        //$dept_id=;
        if (empty($sdate))
            $sdate = date('Ym01',strtotime(TODAY));
        if (empty($edate))
            $edate = TODAY;

        if (date('Ym',strtotime($sdate)) != date('Ym',strtotime($edate))){
            $this->response(array('resultcode'=>-1,'resultmsg'=>"不能跨月查询!"),'json');
        }

        //定义表头样式
        $head=[
            //第一行
            [["value"=>"日期","rowspan"=>2,"colspan"=>1],["value"=>"分类信息","rowspan"=>1,"colspan"=>2],["value"=>"产品信息","rowspan"=>1,"colspan"=>11],["value"=>"收支金额","rowspan"=>1,"colspan"=>3],],
            //第二行
            [["value"=>"收支类别","rowspan"=>1,"colspan"=>1],["value"=>"收支明细","rowspan"=>1,"colspan"=>1],["value"=>"制造部门","rowspan"=>1,"colspan"=>1],
                ["value"=>"大类","rowspan"=>1,"colspan"=>1],["value"=>"非标","rowspan"=>1,"colspan"=>1],["value"=>"板厚","rowspan"=>1,"colspan"=>1],
                ["value"=>"规格","rowspan"=>1,"colspan"=>1],["value"=>"表面要求","rowspan"=>1,"colspan"=>1],["value"=>"门框","rowspan"=>1,"colspan"=>1],
                ["value"=>"花色","rowspan"=>1,"colspan"=>1],["value"=>"锁具","rowspan"=>1,"colspan"=>1],["value"=>"开向","rowspan"=>1,"colspan"=>1],
                ["value"=>"其他","rowspan"=>1,"colspan"=>1],["value"=>"数量","rowspan"=>1,"colspan"=>1],["value"=>"单价","rowspan"=>1,"colspan"=>1],
                ["value"=>"金额","rowspan"=>1,"colspan"=>1],
            ],
            //第三行
            [["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],]
        ];

        $month=date('Y-m',strtotime(TODAY));
        $date=date('Y-m-d',strtotime(TODAY));
        //查询期初获取选择项内容
        $option=[
            'shouzhilb'=>['调拨收入','暂存收入','铺货收入','送货收回','其他收入','调拨支出','暂存支出','铺货支出',
                '送货支出','其他支出',],'zhizaobm'=>[],'dalei'=>[],'feibiao'=>[],'banhou'=>[],'guige'=>[],
            'biaomianyq'=>[],'menkuang'=>[],'huase'=>[],'suoju'=>[],'kaixiang'=>[],'qita'=>[],
        ];//选项数组
        $re = M()->query("select sum(shuliang*danjia)as heji,sum(shuliang) as sl from shouzhimx where dept=$dept_id and 
        type=$type and create_date between '$sdate' and '$edate' order by create_date,dalei");
        $head[2][16]['value'] = empty($re[0]['heji'])?0:$re[0]['heji'];
        $head[2][14]['value'] = empty($re[0]['sl'])?0:$re[0]['sl'];
        $sql_option="select DISTINCT zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,
        suoju,kaixiang,qita from spzmxqc where dept=$dept_id and date='$month' and type=$type";
        $retOption=M()->query($sql_option);
        foreach($retOption as $row=>$val){
            foreach($val as $key=>$value){
                if(!in_array($value,$option[$key]))
                    array_push($option[$key], $value);
            }
        }

        //查询今天是否已录入数据
        $sql_data="select create_date as `date`,shouzhilb,shouzzhimx,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,
           suoju,kaixiang,qita,shuliang,danjia,shuliang*danjia as jine from shouzhimx where dept=$dept_id and 
        type=$type and create_date between '$sdate' and '$edate' order by create_date,dalei";
        $retData=M()->query($sql_data);

        //查询期初不存在的情况
        $sql_qc = "select concat(zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita)as temp from spzmxqc where `date` = '$month' and dept =$dept_id and `type`=$type";
        $qc = M()->query($sql_qc);
        foreach($qc as $vqc){
            $tempqc[] = $vqc['temp'];
        }
        foreach($retData as $k=>$v){
            $cpxx = $v['zhizaobm'].$v['dalei'].$v['feibiao'].$v['banhou'].$v['guige'].$v['biaomianyq'].$v['menkuang'].$v['huase'].$v['suoju'].$v['kaixiang'].$v['qita'];
            if (!in_array($cpxx,$tempqc)){
                $error[] = array(
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
        $this->response(retmsg(0,array("head"=>$head,"option"=>$option,"data"=>$retData,'error'=>$error)),'json');
    }

    /**
     * 获取期初单价
     */
    public function getPrice($token='',$type=1,$zhizaobm,$dalei,$feibiao='',$banhou='',$guige='',
                             $biaomianyq='',$menkuang='',$huase='',$suoju='',$kaixiang='',$qita=''){
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        //$dept_id=1;
        $month=date('Y-m');
        $sql="select benyuedj as price from spzmxqc where dept='$dept_id' and date='$month' and type=$type 
        and zhizaobm='$zhizaobm' and dalei='$dalei' and feibiao='$feibiao' and banhou='$banhou' 
        and guige='$guige' and biaomianyq='$biaomianyq' and menkuang='$menkuang' 
        and huase='$huase' and suoju='$suoju' and kaixiang='$kaixiang' ";
        if(!empty($qita)&&$qita!='0'){
            $sql.="  and qita='$qita' ";
        }
        $ret=M()->query($sql);
        if(!empty($ret)){
            $this->response(retmsg(0,$ret[0]),'json');
        }
        else{
            $this->response(retmsg(-1,null,'该产品未录入期初请仔细核对！'),'json');
        }
    }

    /**
     * 导入excel
     * @param string $token
     * @param number $type
     */
    public function importFromExcel($token='',$flagType=1){
        header("Access-Control-Allow-Origin: *");
        //$a = json_decode('{"error":[[{"type":0,"value":"124行"},{"type":0,"value":"齐河一科"},{"type":0,"value":"40门"},{"type":0,"value":"标门"},{"type":0,"value":"0.5*0.5*1.0"},{"type":0,"value":"2050*860"},{"type":1,"value":"常规红转印"},{"type":0,"value":"70三角花边框90"},{"type":0,"value":"和雅"},{"type":0,"value":"大圆头AB锁"},{"type":0,"value":"外右开"},{"type":0,"value":""}]]}',true);
        //$this->response(array('resultcode'=>-1,'resultmsg'=>"上传产品期初信息:1,成功:1;",'error'=>$a['error']),'json');
        $type=$flagType;
        //验证token
        $userinfo = checktoken($token);
        if(!$userinfo)
        {
            $this->response(retmsg(-2),'json');
            return;
        }
        //导入phpexcel所须文件
        vendor("PHPExcel.Classes.PHPExcel");
        //vendor("PHPExcel.Classes.PHPExcel.Writer.Excel2007");
        //$reader = \PHPExcel_IOFactory::createReader('Excel2007'); //设置以Excel2007
        $name=iconv('gbk','utf-8',$_FILES["file"]["name"]);
        /*  $name=iconv('utf-8','gbk',$name);
         $fileName=mb_substr($name,0,mb_strpos($name,'.')).microtime();  */
        $fileName=mt_rand();
        $extension=substr(strrchr($_FILES["file"]["name"], '.'), 1);;
        if($extension!='xlsx'&&$extension!='xls'){
            $this->response(retmsg(-1,null,'请上传Excel文件！'),'json');
        }
        /*  if($extension=='xls'){
             $reader = \PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel2007
         } */
        $tempPath=str_replace('\\','/',realpath(__DIR__.'/../../../')).'/excel/'.$fileName.".".$extension;
        $flag=move_uploaded_file($_FILES["file"]["tmp_name"],$tempPath);
        if(!$flag){
            $this->response(retmsg(-1,null,"文件保存失败：$tempPath"),'json');
        }
        try {
            //$PHPExcel = $reader->load($tempPath); // 载入excel文件
            $PHPExcel=\PHPExcel_IOFactory::load($tempPath);
        } catch (\PHPExcel_Reader_Exception $e) {
            $this->response(retmsg(-1,null,$e->__toString()),'json');
        }
        unlink($tempPath);//删除临时文件
        $sheets=$PHPExcel->getAllSheets();
        if(empty($sheets)){
            $this->response(retmsg(-1,null,"无法读取此Excel!"),'json');
        }
        $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数字符串
        $highestColumm=\PHPExcel_Cell::columnIndexFromString($highestColumm);

        if ($PHPExcel->getActiveSheet()->getCell("A2")->getValue() !='日期')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是日期'),'json');
        if ($PHPExcel->getActiveSheet()->getCell("B2")->getValue() !='收支类别')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是收支类别'),'json');
        if ($PHPExcel->getActiveSheet()->getCell("C2")->getValue() !='收支明细')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是收支明细'),'json');
        if ($PHPExcel->getActiveSheet()->getCell("D2")->getValue() !='制造部门')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是制造部门'),'json');
        if($PHPExcel->getActiveSheet()->getCell("E2")->getValue() !='大类')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是大类'),'json');
        if($PHPExcel->getActiveSheet()->getCell("F2")->getValue() !='非标')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是非标'),'json');
        if($PHPExcel->getActiveSheet()->getCell("G2")->getValue() !='板厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是板厚'),'json');
        if($PHPExcel->getActiveSheet()->getCell("H2")->getValue() !='规格')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是规格'),'json');
        if($PHPExcel->getActiveSheet()->getCell("I2")->getValue() !='表面要求')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'I列不是表面要求'),'json');
        if($PHPExcel->getActiveSheet()->getCell("J2")->getValue() !='门框')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'J列不是门框'),'json');
        if($PHPExcel->getActiveSheet()->getCell("K2")->getValue() !='花色')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'K列不是花色'),'json');
        if($PHPExcel->getActiveSheet()->getCell("L2")->getValue() !='锁具')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'L列不是锁具'),'json');
        if($PHPExcel->getActiveSheet()->getCell("M2")->getValue() !='开向')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是开向'),'json');
        if($PHPExcel->getActiveSheet()->getCell("N2")->getValue() !='其他')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'N列不是其他'),'json');
        if($PHPExcel->getActiveSheet()->getCell("O2")->getValue() !='数量')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'O列不是数量'),'json');
        if($PHPExcel->getActiveSheet()->getCell("P2")->getValue() !='单价')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'P列不是单价'),'json');
        if($PHPExcel->getActiveSheet()->getCell("Q2")->getValue() !='金额')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Q列不是金额'),'json');
        //echo $highestColumm;return;
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
        /** 循环读取每个单元格的数据 */
        //$rowOffset开始读取数据的行数$colOffset列偏移位置，如：A列$colOffset=0，B列开始$colOffset=1
        $rowOffset=5;
        $colOffset=1;
        $totalCount=0;
        for ($row=$rowOffset; $row <= $highestRow; $row++){
            if(!empty($sheet->getCell("B".$row)->getValue())){
                $totalCount++;
            }
            //判断首列的时间是否是今天如不是则跳过此行
            $tempDate=$sheet->getCell("A".$row)->getValue();
            $tempDate=\PHPExcel_Shared_Date::ExcelToPHP($tempDate);
            if(date('Y-m-d',$tempDate)!=date('Y-m-d',strtotime(TODAY))){
                continue;
            }
            $rowPos=$row-$rowOffset;
            for ($column = 0;$column < $highestColumm; $column++) {
                $columnStr=\PHPExcel_Cell::stringFromColumnIndex($column);
                $value=trim($sheet->getCell($columnStr.$row)->getValue());
                switch ($columnStr) {
                    case $index[$colOffset]:
                        $data[$rowPos]['hanghao'] = $rowPos+5;
                        $data[$rowPos]['shouzhilb'] = $value;
                        break;
                    case $index[$colOffset+1]:
                        $data[$rowPos]['shouzzhimx'] = $value;
                        break;
                    case $index[$colOffset+2]:
                        $data[$rowPos]['zhizaobm'] = $value;
                        break;
                    case $index[$colOffset+3]:
                        $data[$rowPos]['dalei'] = $value;
                        break;
                    case $index[$colOffset+4]:
                        $data[$rowPos]['feibiao'] = $value;
                        break;
                    case $index[$colOffset+5]:
                        $data[$rowPos]['banhou'] = $value;
                        break;
                    case $index[$colOffset+6]:
                        $data[$rowPos]['guige'] = $value;
                        break;
                    case $index[$colOffset+7]:
                        $data[$rowPos]['biaomianyq'] = $value;
                        break;
                    case $index[$colOffset+8]:
                        $data[$rowPos]['menkuang'] = $value;
                        break;
                    case $index[$colOffset+9]:
                        $data[$rowPos]['huase'] = $value;
                        break;
                    case $index[$colOffset+10]:
                        $data[$rowPos]['suoju'] = $value;
                        break;
                    case $index[$colOffset+11]:
                        $data[$rowPos]['kaixiang'] = $value;
                        break;
                    case $index[$colOffset+12]:
                        $data[$rowPos]['qita'] = $value;
                        break;
                    case $index[$colOffset+13]:
                        $data[$rowPos]['shuliang'] = $value;
                        break;
                    case $index[$colOffset+14]:
                        $data[$rowPos]['danjia'] = $value;
                        break;
                }
            }
        }
        if(empty($data)){
            $this->response(retmsg(-1,null,"共 $totalCount 行 导入成功0行，请检查日期或格式！"),'json');
        }
        return $this->submit($token,$type,$data,true,$totalCount);
    }

    /**
     * 十进制转26进制
     * @param number $n 十进制参数
     * @return string 26进制返回字符串
     */
    public function  ToNumberSystem26($n=1){
        while ($n > 0){
            $m = $n % 26;
            if ($m == 0) $m = 26;
            $s = chr($m + 64).$s;
            $n = ($n - $m) / 26;
        }
        return $s;
        //echo $s;
    }
    public function printExcel($token='',$s_date='',$date=TODAY,$pageSize=40,$type=1){
        set_time_limit(90);
        header("Access-Control-Allow-Origin: *");
        $Model = M();

//        验证token:根据token获取用户的dept_id
        $userinfo = checktoken($token);
        if(!$userinfo){
            $this->response(retmsg(-2),'json');
            return;
        }
        $dept_id = $userinfo['dept_id'];
        $pid = $userinfo['pid'];
//        $dept_id = 71;
//        $pid = 71;

//        if ($e_date == '')
//        {
//            $e_date = date("Y-m-d");
//        }
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
        $total = M()->query($sql_total);
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
//            $yuechu = date('Y-m-01',strtotime("$e_date -1 day"));
//            if($e_date == $yuechu){//如果所选日期为当月1号，则导出上个月的所有数据
//                $start = date('Y-m-01',strtotime("-1 month"));//2017-02-01
//                $end = date('Y-m-t',strtotime("-1 month"));//2017-02-28
//            }else{
//                $start = $yuechu;
//                $end = date("Y-m-d",strtotime("$e_date -1 day"));//获取所选日期的前一天
//            }
            $yuechu = date("Y-m-01",strtotime("$date -1 day"));
            $start = $yuechu;
            if ($yuechu == date("Y-m-01"))
                $end = date("Y-m-d",strtotime("$date -1 day"));
            else
                $end = date("Y-m-t",strtotime("$date"));

            $sql_one = $sql_data . " limit " . ($i - 1) * $pageSize . ",$pageSize";
            $result = M()->query($sql_one);
            foreach ($result as $key => $value){
                $dname = $value['dname'];//当前片区下的所有部门的部门名称
                $pname = $value['pname'];
                $tr  = ",,,,,,,,,,,,,,,,,\n";
                $tr .= ",,,,,,,,,,,,,,,,,\n";
                $tr .= ",,,,,,,,,,,,,,,,,\n";
                $tr .= ",".$dname."---防盗门收支明细表,,,,,,,,,,,,,,,,\n";
                $tr .= ",,分类信息,,,,,,,产品信息,,,,,,,收支金额,\n";
                $tr .= ",日期,收支类别,收支明细,制造部门,大类,非标,板厚,规格,表面要求,门框,花色,锁具,开向,其他,数量,单价,金额\n";

                $depts = $value['id'];//当前片区下的所有部门的部门id
                $sql = "select * from shouzhimx where create_date between '$start' and '$end' and dept='$depts' and type='$type' ";//查询该片区的所有数据
                $tempData = M()->query($sql);//获取的40个部门的相关收支明细信息
                foreach($tempData as $temp_key=>$temp_value){
                    $riqi = $temp_value["create_date"];
                    $tr .= ",".$riqi.",";
                    $jine = $temp_value["danjia"]*$temp_value["shuliang"];
                    $temp_value = array_values($temp_value);
                    foreach($temp_value as $arr_key=>$arr_val){
                        if($arr_key > 0 && $arr_key < count($temp_value)-3){
                            $tr.=$arr_val.",";
                        }
                    }
                    $tr.=$jine."\n";
                }
                $tr = iconv("UTF-8","GBK",$tr);
                $time = substr($end,0,7);
                if($pid == 0 && $type == 1){
                    $filename = "总部".$time."防盗门有效商品收支明细.csv";
                }elseif($pid == 0 && $type == 0){
                    $filename = "总部".$time."防盗门无效商品收支明细.csv";
                }elseif($pid == 1 && $type == 1){
                    $filename = $pname.$time."防盗门有效商品收支明细.csv";
                }elseif($pid == 1 && $type == 0){
                    $filename = $pname.$time."防盗门无效商品收支明细.csv";
                }elseif($type == 1){
                    $filename = $dname.$time."防盗门有效商品收支明细.csv";
                }else{
                    $filename = $dname.$time."防盗门无效商品收支明细.csv";
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

    //清理spzmx缓存错误数据
    public function test($date){
        set_time_limit(900);
        $sql_dept="select id from xsrb_department ";
        $depts=M()->query($sql_dept);
        //$depts=[["id"=>504]];
        $types=[1,0];//有效无效
        $month=date("Y-m",strtotime($date));
        foreach ($types as $type){

            //循环清理每一个部门的数据
            foreach ($depts as $dept_id){
                $dept_id=$dept_id['id'];
                //更新日报表
                //查询收支明细和销售明细统计
                $sql="select shouzhilb,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,
                qita, sum(shuliang) as shuliang from shouzhimx where dept=$dept_id and type=$type and create_date='$date'
                group by shouzhilb,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita
                union all
                select xiaoshoums as shouzhilb,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,
                kaixiang,qita, sum(xiaoliang) as shuliang from xsmx where dept=$dept_id and type=$type and date='$date'
                group by xiaoshoums,zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita";
                $ret=M()->query($sql);
                //将查询结果转成spzmx结构
                //每一种产品对应的收支类别统计
                $dataArray=array();
                foreach ($ret as $row=>$val){
                    //判断唯一产品组合是否存在
                    $index=-1;//产品位置标示
                    $val['qita']=empty(trim($val['qita']))?"":$val['qita'];//为0和“ ”都去“”
                    foreach ($dataArray as $key=>$value){
                        //产品唯一组合
                        $value['qita']=empty($value['qita'])?"":$value['qita'];
                        if($val['zhizaobm']==$value['zhizaobm']&&$val['dalei']==$value['dalei']&&
                            $val['feibiao']==$value['feibiao']&&$val['banhou']==$value['banhou']&&
                            $val['guige']==$value['guige']&&$val['biaomianyq']==$value['biaomianyq']&&
                            $val['menkuang']==$value['menkuang']&&$val['huase']==$value['huase']&&
                            $val['suoju']==$value['suoju']&&$val['kaixiang']==$value['kaixiang']&&
                            $val['qita']==$value['qita']){
                            $index=$key;
                            break;
                            /* if(!empty(trim($val['qita']))&&trim($val['qita'])!='0'){
                                if(trim($val['qita'])==trim($value['qita'])){
                                    $index=$key;
                                    break;
                                }
                            }
                            else{
                                $index=$key;
                                break;
                            } */
                        }
                    }
                    //初始赋值（数据项）
                    list($val['dbsr'],$val['zcsr'],$val['phsr'],$val['shsh'],$val['qtsr'],$val['dbzc'],$val['zczc'],
                        $val['phzc'],$val['shzc'],$val['qtzc'],$val['zfxszc'],$val['kfxszc'])=array(0,0,0,0,0,0,0,0,0,0,0,0);
                    $tempVal=empty($val['shuliang'])?0:$val['shuliang'];
                    $error=0;
                    //更新对应数据项值
                    switch(trim($val['shouzhilb'])){
                        case '调拨收入':
                            $tempKey='dbsr';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '暂存收入':
                            $tempKey='zcsr';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '铺货收入':
                            $tempKey='phsr';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '送货收回':
                            $tempKey='shsh';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '其他收入':
                            $tempKey='qtsr';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '调拨支出':
                            $tempKey='dbzc';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '暂存支出':
                            $tempKey='zczc';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '铺货支出':
                            $tempKey='phzc';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '送货支出':
                            $tempKey='shzc';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '其他支出':
                            $tempKey='qtzc';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '库房销售':
                            $tempKey='kfxszc';
                            $val[$tempKey]=$tempVal;
                            break;
                        case '直发销售':
                            $tempKey='zfxszc';
                            $val[$tempKey]=$tempVal;
                            break;
                        default:
                            $error=1;
                    }
                    if($error){
                        continue;
                    }
                    $val['type']=$type;
                    $val['dept']=$dept_id;
                    $val['date']=$date;
                    if(empty($val['qita'])){
                        $val['qita']='';
                    }
                    //插入产品信息及对应行的收支类别信息
                    if($index==-1){
                        array_push($dataArray,$val);
                    }
                    //更新收支类别信息
                    else{
                        $dataArray[$index][$tempKey]=$tempVal;
                    }
                }
                //删除缓存数据
                M()->execute("delete from spzmx where dept=$dept_id and date='$date' and type=$type ");
                /*  //插入最新的收支明细(字段不一样无法批量执行)
                 foreach($dataArray as $record){
                 M('spzmx')->add($record);
                 } */
                //print_r($dataArray);return;
                M('spzmx')->addAll($dataArray);
                //更新结存
                $sql="update spzmx set jiecun=dbsr+zcsr+phsr+shsh+qtsr-ifnull(zfxszc,0)-ifnull(kfxszc,0)-dbzc-zczc-phzc-shzc-qtzc
                where dept=$dept_id and type=$type and date='$date'";
                M()->execute($sql);
            }
        }

        echo "success";
    }


}

?>