<?php
namespace NewSPZ\Controller;

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
        $sql_check = "select * from new_spzmx_list ";
        $re = M()->query($sql_check);
        $standardProduct = array();
        foreach($re as $key=>$val){
            foreach($val as $k1=>$v1){
                if (empty($v1))
                    continue;
                if ( !in_array($v1,$standardProduct[$k1]))
                    $standardProduct[$k1][] = excel_trim2($v1);
            }
        }

        $temp=array();//待插入数据库的记录数组
        foreach ($jsonData as $row=>$data){
           /* //组合条件查询期初
            foreach ($data as $tempKey=>$tempVal){
                if(!empty(excel_trim($tempVal))&&$tempKey!='shangpinjc'&&$tempKey!='dingdanlb'){
                    $where[$tempKey]=excel_trim($tempVal);
                }
            }*/
            $data['guige']=str_replace('×','*',$data['guige']);
            $product_info = excel_trim2($data['zhizaobm']).excel_trim2($data['dingdanlb']).excel_trim2($data['dangci']).
                    excel_trim2($data['menkuang']).floatval(excel_trim2($data['kuanghou'])).floatval(excel_trim2($data['qianbanhou'])).
                    floatval(excel_trim2($data['houbanhou'])).excel_trim2($data['dikuangcl']).excel_trim2($data['menshan']).
                    excel_trim2($data['guige']).excel_trim2($data['kaixiang']).excel_trim2($data['jiaolian']).
                    excel_trim2($data['huase']).excel_trim2($data['biaomianfs']).excel_trim2($data['biaomianyq']).
                    excel_trim2($data['chuanghua']).excel_trim2($data['maoyan']).excel_trim2($data['biaopai'])
                    .excel_trim2($data['zhusuo']).excel_trim2($data['fusuo']).excel_trim2($data['suoba']).
                    excel_trim2($data['biaojian']).excel_trim2($data['baozhuangpp']).excel_trim2($data['baozhuangfs']).
                    excel_trim2($data['qita']).$dept_id.$month.$type;
            //echo $product_info ;exit();
            $product_md5 = md5($product_info);
            $where['product_md5']=$product_md5;
            //查询此期初是否存在,在期初表中查询产品product_md5
            $ret=M('new_spzmxqc')->where($where)->field('benyuedj')->select();

            //匹配产品属性是否标准
            $legal_zhizaobm= in_array(excel_trim($data['zhizaobm']),$standardProduct['zhizaobm'])?1:0;
            $legal_dingdanlb= in_array(excel_trim($data['dingdanlb']),$standardProduct['dingdanlb'])?1:0;
            $legal_dangci= in_array(excel_trim($data['dangci']),$standardProduct['dangci'])?1:0;
            $legal_menkuang= in_array(excel_trim($data['menkuang']),$standardProduct['menkuang'])?1:0;
            $legal_kuanghou= in_array(excel_trim($data['kuanghou']),$standardProduct['kuanghou'])?1:0;
            $legal_qianbanhou= in_array(excel_trim($data['qianbanhou']),$standardProduct['qianbanhou'])?1:0;
            $legal_houbanhou= in_array(excel_trim($data['houbanhou']),$standardProduct['houbanhou'])?1:0;
            $legal_dikuangcl= in_array(excel_trim($data['dikuangcl']),$standardProduct['dikuangcl'])?1:0;
            $legal_menshan= in_array(excel_trim($data['menshan']),$standardProduct['menshan'])?1:0;
            //$legal_guige= in_array(excel_trim($data['guige']),$standardProduct['guige'])?1:0;
            $legal_kaixiang= in_array(excel_trim($data['kaixiang']),$standardProduct['kaixiang'])?1:0;
            $legal_jiaolian= in_array(excel_trim($data['jiaolian']),$standardProduct['jiaolian'])?1:0;
            $legal_huase= in_array(excel_trim($data['huase']),$standardProduct['huase'])?1:0;
            $legal_biaomianfs= in_array(excel_trim($data['biaomianfs']),$standardProduct['biaomianfs'])?1:0;
            $legal_biaomianyq= in_array(excel_trim($data['biaomianyq']),$standardProduct['biaomianyq'])?1:0;
            $legal_chuanghua= in_array(excel_trim($data['chuanghua']),$standardProduct['chuanghua'])?1:0;
            $legal_maoyan= in_array(excel_trim($data['maoyan']),$standardProduct['maoyan'])?1:0;
            $legal_biaopai= in_array(excel_trim($data['biaopai']),$standardProduct['biaopai'])?1:0;
            $legal_zhusuo= in_array(excel_trim($data['zhusuo']),$standardProduct['zhusuo'])?1:0;
            $legal_fusuo= in_array(excel_trim($data['fusuo']),$standardProduct['fusuo'])?1:0;
            $legal_suoba= in_array(excel_trim($data['suoba']),$standardProduct['suoba'])?1:0;
            $legal_biaojian= in_array(excel_trim($data['biaojian']),$standardProduct['biaojian'])?1:0;
            $legal_baozhuangpp= in_array(excel_trim($data['baozhuangpp']),$standardProduct['baozhuangpp'])?1:0;
            $legal_baozhuangfs= in_array(excel_trim($data['baozhuangfs']),$standardProduct['baozhuangfs'])?1:0;

            //判断必填项是否合法
            $legal_shouzhimx=!empty(excel_trim($data['shouzhimx']))?1:0;
            //判断收支明细是否合法
            $shouzhilb=['调拨收入(直发)','调拨收入(库房)','暂存收入','铺货收入','送货收回','其他收入','调拨支出','暂存支出','铺货支出','送货支出','报废支出','其他支出',];
            $legal_shouzhilb=in_array(excel_trim($data['shouzhilb']),$shouzhilb);
            //期初不存在,或者是收支类别不合法则提示错误信息,错误信息可能是整行期初不存在，也可能是不符合产品标准信息，也可能为空
            if(empty($ret)||!$legal_shouzhilb){
                $code = '';
                $code .= $legal_shouzhilb?'':'收支类别:'.$data['shouzhilb']."不存在";
                $code .= $legal_shouzhimx?'':'收支明细不能为空；';
                $code .=$legal_zhizaobm?'':"制造部门：".$data['zhizaobm']."不存在";
                $code .=$legal_dingdanlb?'':"订单类别 ：".$data['dingdanlb']."不存在";
                $code .=$legal_dangci?'':"档次：".$data['dangci']."不存在";
                $code .=$legal_menkuang?'':"门框：".$data['menkuang']."不存在";
                $code .=$legal_kuanghou?'':"框厚：".$data['kuanghou']."不存在";
                $code .=$legal_qianbanhou?'':"前板厚：".$data['qianbanhou']."不存在";
                $code .=$legal_houbanhou?'':"后板厚：".$data['houbanhou']."不存在";
                $code .=$legal_dikuangcl?'':"底框材料：".$data['dikuangcl']."不存在";
                $code .=$legal_menshan?'':"门扇：".$data['menshan']."不存在";
                $code .=$legal_kaixiang?'':"开向：".$data['kaixiang']."不存在";
                $code .=$legal_jiaolian?'':"铰链：".$data['jiaolian']."不存在";
                $code .=$legal_huase?'':"花色：".$data['huase']."不存在";
                $code .=$legal_biaomianfs?'':"表面方式：".$data['biaomianfs']."不存在";
                $code .=$legal_biaomianyq?'':"表面要求：".$data['biaomianyq']."不存在";
                $code .=$legal_chuanghua?'':"窗花：".$data['chuanghua']."不存在";
                $code .=$legal_maoyan?'':"猫眼：".$data['maoyan']."不存在";
                $code .=$legal_biaopai?'':"标牌：".$data['biaopai']."不存在";
                $code .=$legal_zhusuo?'':"主锁：".$data['zhusuo']."不存在";
                $code .=$legal_fusuo?'':"副锁：".$data['fusuo']."不存在";
                $code .=$legal_suoba?'':"锁把：".$data['suoba']."不存在";
                $code .=$legal_biaojian?'':"标件：".$data['biaojian?']."不存在";
                $code .=$legal_baozhuangpp?'':"包装品牌：".$data['baozhuangpp']."不存在";
                $code .=$legal_baozhuangfs?'':"包装方式：".$data['baozhuangfs']."不存在";
                if (empty($code)){
                    $code = '该产品信息行在期初中未录入!';
                }
				/*$teshuzhizaobm = array('舍零','返利','送货运费收入');
                if (in_array($zhizaobm,$teshuzhizaobm)){
                    $code = '该产品信息行在期初中未录入!';
                    $k1=1;$k2=1;$k3=1;$k4=1;$k5=1;$k6=1;$k7=1;$k8=1;$k9=1;$k10=1;$k11=1;
                }*/
                $errorInfo[] = array(
                    array('type'=>1,'value'=>$data['hanghao']),
                    array('type'=>1,'value'=>$date),
                    array('type'=>$legal_shouzhilb,'value'=>$data['shouzhilb']),
                    array('type'=>$legal_shouzhimx,'value'=>$data['shouzhimx']),
                    array('type'=>1,'value'=>$data['shangpinjc']),
                    array('type'=>$legal_zhizaobm,'value'=>$data['zhizaobm']),
                    array('type'=>$legal_dingdanlb,'value'=>$data['dingdanlb']),
                    array('type'=>$legal_dangci,'value'=>$data['dangci']),
                    array('type'=>$legal_menkuang,'value'=>$data['menkuang']),
                    array('type'=>$legal_kuanghou,'value'=>$data['kuanghou']),
                    array('type'=>$legal_qianbanhou,'value'=>$data['qianbanhou']),
                    array('type'=>$legal_houbanhou,'value'=>$data['houbanhou']),
                    array('type'=>$legal_dikuangcl,'value'=>$data['dikuangcl']),
                    array('type'=>$legal_menshan,'value'=>$data['menshan']),
                    array('type'=>1,'value'=>$data['guige']),
                    array('type'=>$legal_kaixiang,'value'=>$data['kaixiang']),
                    array('type'=>$legal_jiaolian,'value'=>$data['jiaolian']),
                    array('type'=>$legal_huase,'value'=>$data['huase']),
                    array('type'=>$legal_biaomianfs,'value'=>$data['biaomianfs']),
                    array('type'=>$legal_biaomianyq,'value'=>$data['biaomianyq']),
                    array('type'=>$legal_chuanghua,'value'=>$data['chuanghua']),
                    array('type'=>$legal_maoyan,'value'=>$data['maoyan']),
                    array('type'=>$legal_biaopai,'value'=>$data['biaopai']),
                    array('type'=>$legal_zhusuo,'value'=>$data['zhusuo']),
                    array('type'=>$legal_fusuo,'value'=>$data['fusuo']),
                    array('type'=>$legal_suoba,'value'=>$data['suoba']),
                    array('type'=>$legal_biaojian,'value'=>$data['biaojian']),
                    array('type'=>$legal_baozhuangpp,'value'=>$data['baozhuangpp']),
                    array('type'=>$legal_baozhuangfs,'value'=>$data['baozhuangfs']),
                    array('type'=>1,'value'=>$data['qita']),
                    array('type'=>is_numeric($data['shuliang'])?1:0,'value'=>$data['shuliang']),
                    array('type'=>is_numeric($data['danjia'])?1:0,'value'=>$data['danjia']),
                    array('type'=>0,'value'=>'期初不存在，请仔细核对期初数据；'.$code)
                );
                //此行不存入数据库
                continue;

            }
            $data['dept']=$dept_id;
            $data['create_date']=$date;
            $data['type']=$type;
            $data['danjia']=$ret[0]['benyuedj'];
            $data['product_md5']=$product_md5;
            array_push($temp, $data);
        }
        if(!empty($temp)){
            M()->execute("delete from new_shouzhimx where dept=$dept_id and type=$type and create_date='$date' and pandianId = 0");
        }
        //页面强制删除所有数据
        if ($delete==1){
            M()->execute("delete from new_shouzhimx where dept=$dept_id and type=$type and create_date='$date' ");
            M()->execute("delete from new_spzmx where dept=$dept_id and type=$type and date='$date' ");
        }
        if(!empty($temp)){
            $result=M('new_shouzhimx')->addAll($temp);
        }
        //更新日报表
        //查询收支明细和销售明细统计
        $sql="select shouzhilb,product_md5,sum(shuliang) as shuliang from new_shouzhimx 
        where dept=$dept_id and type=$type and create_date='$date' group by product_md5,shouzhilb
        union all 
        select xiaoshoums as shouzhilb,product_md5,sum(xiaoliang) as shuliang from new_xsmx 
        where dept=$dept_id and type=$type and date='$date' 
        group by product_md5,shouzhilb";
        $ret=M()->query($sql);
        //将查询结果转成spzmx结构
        //每一种产品对应的收支类别统计
        $dataArray=array();
        foreach ($ret as $row=>$val){
            //判断唯一产品组合是否存在,同一产品是否已经插入到$dataArray中，如果已有则记录位置，更新收支类别值
            $index=-1;//产品位置标示
            foreach ($dataArray as $key=>$value){
                //产品唯一组合
                if($val['product_md5']==$value['product_md5']){
                    $index=$key;
                    break;
                }
            }
            //初始赋值（数据项）
            list($val['dbsr'],$val['zfdbsr'],$val['kfdbsr'],$val['zcsr'],$val['phsr'],$val['shsh'],$val['qtsr'],$val['dbzc'],$val['zczc'],
                $val['phzc'],$val['shzc'],$val['bfzc'],$val['qtzc'],$val['zfxszc'],$val['kfxszc'])=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $tempVal=empty($val['shuliang'])?0:$val['shuliang'];
            $error=0;
            //更新对应数据项值
            switch(excel_trim($val['shouzhilb'])){
                case '调拨收入(直发)':
                    $tempKey='zfdbsr';
                    $val[$tempKey]=$tempVal;
                    break;
                case '调拨收入(库房)':
                    $tempKey='kfdbsr';
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
                case '报废支出':
                    $tempKey='bfzc';
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
        M()->execute("delete from new_spzmx where dept=$dept_id and date='$date' and type=$type ");
        M('new_spzmx')->addAll($dataArray);

        //如果在盘点过后录入收支明细，则将盘点标志置为无效
        $check_pd = M()->query("select flag from new_spzpd where dept =$dept_id and `type`=$type and yuefen ='$month' and edate ='$date' ");
        if (count($check_pd)){  //当天盘点过后,再次录入防盗门销售明细数据.flag设置为0
            M()->execute("update new_spzpd set flag =0,createtime=now() where dept =$dept_id and `type`=$type and edate ='$date' order by createtime desc limit 1");
        }

        //更新结存和调拨收入（调拨收入(库房)+调拨收入(直发)）
        $sql="update new_spzmx set dbsr=kfdbsr+zfdbsr,jiecun=dbsr+zcsr+phsr+shsh+qtsr-ifnull(zfxszc,0)-ifnull(kfxszc,0)-dbzc-zczc-phzc-shzc-bfzc-qtzc 
        where dept=$dept_id and type=$type and date='$date'";
        M()->execute($sql);

        $successCount=count($temp);//插入收支明细表成功行数
        $resultCode=$successCount>0||$delete==1?0:-1;
       /* if(!$flag){
            $this->response(retmsg($resultCode),'json');
        }
        else{
            $m="共 $totalCount 行，导入成功".$successCount."行！";
            $this->response(array('resultcode'=>-1,'resultmsg'=>$m,'error'=>$errorInfo),'json');
        }*/
       if(empty($totalCount)){
           $totalCount=count($jsonData);
       }
        $m="共 $totalCount 行，保存成功".$successCount."行！";
        $this->response(array('resultcode'=>$resultCode,'resultmsg'=>$m,'error'=>$errorInfo),'json');
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
            [["value"=>"分类信息","rowspan"=>1,"colspan"=>4],["value"=>"产品信息","rowspan"=>1,"colspan"=>25],["value"=>"收支金额","rowspan"=>1,"colspan"=>3],],
            //第二行
            [
                ["value"=>"日期","rowspan"=>1,"colspan"=>1],
                ["value"=>"收支类别","rowspan"=>1,"colspan"=>1],["value"=>"收支明细","rowspan"=>1,"colspan"=>1],
                ["value"=>"商品简称","rowspan"=>1,"colspan"=>1],["value"=>"制造部门","rowspan"=>1,"colspan"=>1],
                ["value"=>"订单类别","rowspan"=>1,"colspan"=>1],["value"=>"档次","rowspan"=>1,"colspan"=>1],
                ["value"=>"门框","rowspan"=>1,"colspan"=>1],["value"=>"框厚","rowspan"=>1,"colspan"=>1],
                ["value"=>"前板厚","rowspan"=>1,"colspan"=>1],["value"=>"后板厚","rowspan"=>1,"colspan"=>1],
                ["value"=>"底框材料","rowspan"=>1,"colspan"=>1],["value"=>"门扇","rowspan"=>1,"colspan"=>1],
                ["value"=>"规格","rowspan"=>1,"colspan"=>1],["value"=>"开向","rowspan"=>1,"colspan"=>1],
                ["value"=>"铰链","rowspan"=>1,"colspan"=>1],["value"=>"花色","rowspan"=>1,"colspan"=>1],
                ["value"=>"表面方式","rowspan"=>1,"colspan"=>1], ["value"=>"表面要求","rowspan"=>1,"colspan"=>1],
                ["value"=>"窗花","rowspan"=>1,"colspan"=>1],["value"=>"猫眼","rowspan"=>1,"colspan"=>1],
                ["value"=>"标牌","rowspan"=>1,"colspan"=>1],["value"=>"主锁","rowspan"=>1,"colspan"=>1],
                ["value"=>"副锁","rowspan"=>1,"colspan"=>1],["value"=>"锁把","rowspan"=>1,"colspan"=>1],
                ["value"=>"标件","rowspan"=>1,"colspan"=>1],["value"=>"包装品牌","rowspan"=>1,"colspan"=>1],
                ["value"=>"包装方式","rowspan"=>1,"colspan"=>1],["value"=>"其他","rowspan"=>1,"colspan"=>1],
                ["value"=>"数量","rowspan"=>1,"colspan"=>1],["value"=>"单价","rowspan"=>1,"colspan"=>1],
                ["value"=>"金额","rowspan"=>1,"colspan"=>1],
            ],
            //第三行
            [   ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],["value"=>"","rowspan"=>1,"colspan"=>1],
                ["value"=>"","rowspan"=>1,"colspan"=>1],
            ]
        ];

        $month=date('Y-m',strtotime(TODAY));
        $date=date('Y-m-d',strtotime(TODAY));
        //选项数组,选择收支类别订单类别
        $option=[
            'shouzhilb'=>['调拨收入(直发)','调拨收入(库房)','暂存收入','铺货收入','送货收回','其他收入','调拨支出','暂存支出','铺货支出','送货支出','报废支出','其他支出',],
            'dingdanlb'=>['直接工程订单','经销商工程订单', '经销商招商订单', '常规订单', '工程样品订单'],
        ];
        //计算合计
        $re = M()->query("select sum(shuliang*danjia)as heji,sum(shuliang) as sl from new_shouzhimx where dept=$dept_id and 
        type=$type and create_date between '$sdate' and '$edate' ");
        $head[2][31]['value'] = empty($re[0]['heji'])?0:$re[0]['heji'];
        $head[2][29]['value'] = empty($re[0]['sl'])?0:$re[0]['sl'];

        //查询时间范围内天是否已录入数据
        $sql_data="select * from new_shouzhimx t1 left join  new_spzmxqc t2 on t1.product_md5=t2.product_md5 
        where t1.dept=$dept_id and t1.type=$type and create_date between '$sdate' and '$edate' ";
        $retData=M()->query($sql_data);
        foreach ($retData as $key=>$val){
            $retData[$key]['jine']=$val['shuliang']*$val['danjia'];
        }


        //查询期初不存在的情况
        $sql_qc = "select product_md5 from new_spzmxqc where `month` = '$month' and dept =$dept_id and `type`=$type";
        $qc = M()->query($sql_qc);
        foreach($qc as $vqc){
            $tempqc[] = $vqc['product_md5'];
        }
        foreach($retData as $k=>$v){
            if (!in_array($v['product_md5'],$tempqc)){
                $error[] = array(
                    array('type'=>1,'value'=>$k+1),
                    array('type'=>1,'value'=>$v['create_date']),
                    array('type'=>0,'value'=>$v['shouzhilb']),
                    array('type'=>0,'value'=>$v['shouzhimx']),
                    array('type'=>1,'value'=>$v['shangpinjc']),
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
        $sql="select benyuedj as price from new_spzmxqc where dept='$dept_id' and date='$month' and type=$type 
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
        if($extension!='xlsx'&&$extension!='xls'&&$extension!='csv'){
            $this->response(retmsg(-1,null,'请上传Excel或csv文件！'),'json');
        }
        /*  if($extension=='xls'){
             $reader = \PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel2007
         } */
        $tempPath=str_replace('\\','/',realpath(__DIR__.'/../../../')).'/excel/'.$fileName.".".$extension;
        $flag=move_uploaded_file($_FILES["file"]["tmp_name"],$tempPath);
        if(!$flag){
            $this->response(retmsg(-1,null,"文件保存失败：$tempPath"),'json');
        }
        //上传文件类型为csv
        if($extension=='csv'){
            $this->importFromCsv($tempPath,$token,$flagType);
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

        if (excel_trim($PHPExcel->getActiveSheet()->getCell("A2")->getValue())!='日期')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是日期'),'json');
        if (excel_trim($PHPExcel->getActiveSheet()->getCell("B2")->getValue()) !='收支类别')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是收支类别'),'json');
        if (excel_trim($PHPExcel->getActiveSheet()->getCell("C2")->getValue()) !='收支明细')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是收支明细'),'json');
        if (excel_trim($PHPExcel->getActiveSheet()->getCell("D2")->getValue()) !='商品简称')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是商品简称'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("E2")->getValue()) !='制造部门')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是制造部门'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("F2")->getValue()) !='订单类别')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是订单类别'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("G2")->getValue()) !='档次')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是档次'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("H2")->getValue()) !='门框')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是门框'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("I2")->getValue()) !='框厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'I列不是框厚'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("J2")->getValue()) !='前板厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'J列不是前板厚'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("K2")->getValue()) !='后板厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'K列不是后板厚'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("L2")->getValue()) !='底框材料')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'L列不是底框材料'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("M2")->getValue()) !='门扇')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是门扇'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("N2")->getValue()) !='规格')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'N列不是规格'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("O2")->getValue()) !='开向')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'O列不是开向'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("P2")->getValue()) !='铰链')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'P列不是铰链'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("Q2")->getValue()) !='花色')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Q列不是花色'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("R2")->getValue()) !='表面方式')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'R列不是表面方式'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("S2")->getValue()) !='表面要求')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'S列不是表面要求'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("T2")->getValue()) !='窗花')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'T列不是窗花'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("U2")->getValue()) !='猫眼')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'U列不是猫眼'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("V2")->getValue()) !='标牌')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'V列不是标牌'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("W2")->getValue()) !='主锁')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'W列不是主锁'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("X2")->getValue()) !='副锁')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'X列不是副锁'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("Y2")->getValue()) !='锁把')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Y列不是锁把'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("Z2")->getValue()) !='标件')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Z列不是标件'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("AA2")->getValue()) !='包装品牌')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AA列不是包装品牌'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("AB2")->getValue()) !='包装方式')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AB列不是包装方式'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("AC2")->getValue()) !='其他')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AC列不是其他'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("AD2")->getValue()) !='数量')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AD列不是数量'),'json');
        if(excel_trim($PHPExcel->getActiveSheet()->getCell("AE2")->getValue()) !='单价')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AE列不是单价'),'json');
        //echo $highestColumm;return;
        //excel列索引
        $index=array("A","B","C","D","E","F","G","H","I",
            "J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",'AA','AB','AC','AD','AE','AF');
        /** 循环读取每个单元格的数据 */
        //$rowOffset开始读取数据的行数$colOffset列偏移位置，如：A列$colOffset=0，B列开始$colOffset=1
        $rowOffset=4;
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
                    case $index[$colOffset]://B列 $colOffset=1
                        $data[$rowPos]['hanghao'] = $rowPos+$rowOffset;
                        $data[$rowPos]['shouzhilb'] = $value;
                        break;
                    case $index[$colOffset+1]://C列
                        $data[$rowPos]['shouzhimx'] = $value;
                        break;
                    case $index[$colOffset+2]:
                        $data[$rowPos]['shangpinjc'] = $value;
                        break;
                    case $index[$colOffset+3]:
                        $data[$rowPos]['zhizaobm'] = $value;
                        break;
                    case $index[$colOffset+4]:
                        $data[$rowPos]['dingdanlb'] = $value;
                        break;
                    case $index[$colOffset+5]:
                        $data[$rowPos]['dangci'] = $value;
                        break;
                    case $index[$colOffset+6]:
                        $data[$rowPos]['menkuang'] = $value;
                        break;
                    case $index[$colOffset+7]:
                        $data[$rowPos]['kuanghou'] = $value;
                        break;
                    case $index[$colOffset+8]:
                        $data[$rowPos]['qianbanhou'] = $value;
                        break;
                    case $index[$colOffset+9]:
                        $data[$rowPos]['houbanhou'] = $value;
                        break;
                    case $index[$colOffset+10]:
                        $data[$rowPos]['dikuangcl'] = $value;
                        break;
                    case $index[$colOffset+11]:
                        $data[$rowPos]['menshan'] = $value;
                        break;
                    case $index[$colOffset+12]:
                        $data[$rowPos]['guige'] = $value;
                        break;
                    case $index[$colOffset+13]:
                        $data[$rowPos]['kaixiang'] = $value;
                        break;
                    case $index[$colOffset+13]:
                        $data[$rowPos]['kaixiang'] = $value;
                        break;
                    case $index[$colOffset+14]:
                        $data[$rowPos]['jiaolian'] = $value;
                        break;
                    case $index[$colOffset+15]:
                        $data[$rowPos]['huase'] = $value;
                        break;
                    case $index[$colOffset+16]:
                        $data[$rowPos]['biaomianfs'] = $value;
                        break;
                    case $index[$colOffset+17]:
                        $data[$rowPos]['biaomianyq'] = $value;
                        break;
                    case $index[$colOffset+18]:
                        $data[$rowPos]['chuanghua'] = $value;
                        break;
                    case $index[$colOffset+19]:
                        $data[$rowPos]['maoyan'] = $value;
                        break;

                    case $index[$colOffset+20]:
                        $data[$rowPos]['biaopai'] = $value;
                        break;
                    case $index[$colOffset+21]:
                        $data[$rowPos]['zhusuo'] = $value;
                        break;
                    case $index[$colOffset+22]:
                        $data[$rowPos]['fusuo'] = $value;
                        break;
                    case $index[$colOffset+23]:
                        $data[$rowPos]['suoba'] = $value;
                        break;
                    case $index[$colOffset+24]:
                        $data[$rowPos]['biaojian'] = $value;
                        break;
                    case $index[$colOffset+25]:
                        $data[$rowPos]['baozhuangpp'] = $value;
                        break;
                    case $index[$colOffset+26]:
                        $data[$rowPos]['baozhuangfs'] = $value;
                        break;
                    case $index[$colOffset+27]:
                        $data[$rowPos]['qita'] = $value;
                        break;
                    case $index[$colOffset+28]:
                        $data[$rowPos]['shuliang'] = $value;
                        break;
                    case $index[$colOffset+29]:
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
     * 导入csv
     * @param $tempPath
     * @param $token
     * @param $flag 有效无效标志
     * @return  $this->submit($token,$type,$data,true,$totalCount);
     */
    public function importFromCsv($tempPath,$token,$type,$flag){
        $data = array ();//csv 数据数组
        $n = 0;
        $handle=fopen($tempPath,'r');
        //循环读取每一行数据解析成二维数组
        while ($row = fgetcsv($handle, 10000)) {
            $num = count($row);
            for ($i = 0; $i < $num; $i++) {
                $data[$n][$i] =iconv("gbk","utf-8",$row[$i]);
            }
            $n++;
        }
        fclose($handle);
        //检查表头标题是否符合要求
        if (excel_trim($data[1][0])!='日期')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是日期'),'json');
        if (excel_trim($data[1][1])!='收支类别')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是收支类别'),'json');
        if (excel_trim($data[1][2])!='收支明细')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是收支明细'),'json');
        if (excel_trim($data[1][3])!='商品简称')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是商品简称'),'json');
        if(excel_trim($data[1][4])!='制造部门')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是制造部门'),'json');
        if(excel_trim($data[1][5])!='订单类别')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是订单类别'),'json');
        if(excel_trim($data[1][6])!='档次')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是档次'),'json');
        if(excel_trim($data[1][7])!='门框')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是门框'),'json');
        if(excel_trim($data[1][8])!='框厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'I列不是框厚'),'json');
        if(excel_trim($data[1][9])!='前板厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'J列不是前板厚'),'json');
        if(excel_trim($data[1][10])!='后板厚')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'K列不是后板厚'),'json');
        if(excel_trim($data[1][11])!='底框材料')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'L列不是底框材料'),'json');
        if(excel_trim($data[1][12])!='门扇')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'M列不是门扇'),'json');
        if(excel_trim($data[1][13])!='规格')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'N列不是规格'),'json');
        if(excel_trim($data[1][14])!='开向')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'O列不是开向'),'json');
        if(excel_trim($data[1][15])!='铰链')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'P列不是铰链'),'json');
        if(excel_trim($data[1][16])!='花色')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Q列不是花色'),'json');
        if(excel_trim($data[1][17])!='表面方式')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'R列不是表面方式'),'json');
        if(excel_trim($data[1][18])!='表面要求')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'S列不是表面要求'),'json');
        if(excel_trim($data[1][19])!='窗花')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'T列不是窗花'),'json');
        if(excel_trim($data[1][20])!='猫眼')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'U列不是猫眼'),'json');
        if(excel_trim($data[1][21])!='标牌')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'V列不是标牌'),'json');
        if(excel_trim($data[1][22])!='主锁')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'W列不是主锁'),'json');
        if(excel_trim($data[1][23])!='副锁')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'X列不是副锁'),'json');
        if(excel_trim($data[1][24])!='锁把')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Y列不是锁把'),'json');
        if(excel_trim($data[1][25])!='标件')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'Z列不是标件'),'json');
        if(excel_trim($data[1][26])!='包装品牌')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AA列不是包装品牌'),'json');
        if(excel_trim($data[1][27])!='包装方式')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AB列不是包装方式'),'json');
        if(excel_trim($data[1][28])!='其他')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AC列不是其他'),'json');
        if(excel_trim($data[1][29])!='数量')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AD列不是数量'),'json');
        if(excel_trim($data[1][30])!='单价')
            $this->response(array('resultcode'=>-1,'resultmsg'=>'AE列不是单价'),'json');

        $dataSet=array();
        //组合数据，去掉表头和非今天数据
        foreach ($data as $key=>$row){
            //格式化csv里的日期，eg:6月30日 =>2017-6-30
            if(mb_strpos($row[0],'月')!==false){
                //$month=substr();
                $str=mb_substr($row[0],0,-1,'utf-8');
                $date=explode("月",$str);
                $month=$date[0];
                $day=$date[1];
                $year=date("Y");
                $row[0]="$year-$month-$day";
            }
            if($key<3||date('Y-m-d',strtotime($row[0]))!=date('Y-m-d',strtotime(TODAY))){
                continue;
            }
            $test=1;
            /*$tempRow=array('date'=>'','shouzhimx'=>'','shangpinjc'=>'','zhizaobm'=>'','dingdanlb'=>'','dangci'=>'',
                'kuanghou'=>'','qianbanhou'=>'','houbanhou'=>'','dikuangcl'=>'','menshan'=>'','guige'=>'','kaixiang'=>'',
                'jiaolian'=>'','huase'=>'', 'biaomianfs'=>'','biaomianyq'=>'','chuanghua'=>'','maoyan'=>'','biaopai'=>'',
                'zhusuo'=>'','fusuo'=>'','suoba'=>'','biaojian'=>'','baozhuangpp'=>'', 'baozhuangfs'=>'','qita'=>'','shuliang'=>'');*/
            $tempRow=array();
            list($tempRow['date'],$tempRow['shouzhilb'],$tempRow['shouzhimx'],$tempRow['shangpinjc'],
               $tempRow['zhizaobm'],$tempRow['dingdanlb'],$tempRow['dangci'],$tempRow['menkuang'],
               $tempRow['kuanghou'],$tempRow['qianbanhou'],$tempRow['houbanhou'],
               $tempRow['dikuangcl'],$tempRow['menshan'],$tempRow['guige'],
               $tempRow['kaixiang'],$tempRow['jiaolian'],$tempRow['huase'],
               $tempRow['biaomianfs'],$tempRow['biaomianyq'],$tempRow['chuanghua'],
               $tempRow['maoyan'],$tempRow['biaopai'],$tempRow['zhusuo'],
               $tempRow['fusuo'],$tempRow['suoba'],$tempRow['biaojian'],
               $tempRow['baozhuangpp'],$tempRow['baozhuangfs'],$tempRow['qita'],$tempRow['shuliang'],
               )=$row;
            $tempRow['hanghao']=$key+1;
            array_push($dataSet,$tempRow);
        }
        $totalCount=count($data)-3;
        if(empty($dataSet)){
            $this->response(retmsg(-1,null,"共 $totalCount 行 导入成功0行，请检查日期或格式！"),'json');
        }
        $this->submit($token,$type,$dataSet,true,$totalCount);
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
                $tr .= ",,分类信息,,产品信息,,,,,,,,,,,,,,,,,,,,,,,,,,收支金额,\n";
                $tr .= ",日期,收支类别,收支明细,商品简称,制造部门,订单类别,档次,门框,框厚,前板厚,后板厚,底框材料,门扇,规格,开向,铰链,花色,表面方式,表面要求,窗花,猫眼,标牌,主锁,副锁,锁把,标件,包装品牌,包装方式,其他,数量,单价,金额\n";

                $depts = $value['id'];//当前片区下的所有部门的部门id
                $sql = "select t1.create_date,shouzhilb,shouzhimx,t2.shangpinjc,zhizaobm,t2.dingdanlb,dangci,menkuang,kuanghou,qianbanhou,houbanhou,dikuangcl,menshan,guige,kaixiang,jiaolian,huase,biaomianfs,biaomianyq,chuanghua,maoyan,biaopai,zhusuo,fusuo,suoba,biaojian,baozhuangpp,baozhuangfs,qita,shuliang,danjia,shuliang*danjia 
                from  new_shouzhimx t1 left join new_spzmxqc t2  on t1.product_md5=t2.product_md5 where t1.dept='$depts' and t1.type='$type' and create_date between '$start' and '$end' ";//查询该片区的所有数据
                $tempData = M()->query($sql);//获取的40个部门的相关收支明细信息
                foreach($tempData as $temp_key=>$temp_value){
                    $riqi = $temp_value["create_date"];
                    //$tr .= ",".$riqi.",";
                    //$jine = $temp_value["danjia"]*$temp_value["shuliang"];
                    $tr .=",";
                    $temp_value = array_values($temp_value);
                    foreach($temp_value as $arr_key=>$arr_val){
                       /* if($arr_key > 0 && $arr_key < count($temp_value)-3){
                            $tr.=$arr_val.",";
                        }*/
                        $tr.=$arr_val.",";
                    }
                    $tr.="\n";
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

    public function test(){
        $data=M()->query("SELECT CAST(\"0.700\" AS DECIMAL(10,2))");
        $this->response(retmsg(0,null,$data),'json');
    }

}

?>