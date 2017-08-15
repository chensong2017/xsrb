<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class CGXSRBHZController extends RestController
{
    // 计算各部门的常规销售日报汇总并查询
    public function search($page = '', $cntperpage = '5', $date = '', $token='',$type= '',$id ='')
    {
        header("Access-Control-Allow-Origin: *");
		//处理部门查询功能
        if ($type == '')
        {
            //token检测
            $userinfo = checktoken($token);
            if (! $userinfo) {
                $this->response(retmsg(- 2), 'json');
                return;
            }
            $dept_id = $userinfo['dept_id'];
        }
        
        //当upexcel方法调用时处理
        if ($type =='excel')
        {
            $dept_id = $id;
        }
        if ($dept_id == 1)
        {
            // dept_id=1表示为总部
            $sql = "select id,dname from xsrb_department where qt1 !=0 order by qt1 ";
        } else
        {
            // 片区部门
            $sql = "select id,dname from xsrb_department where qt2 like '%." . $dept_id . "' order by qt1";
        }

        //日期选择
        if ($date == '') {
            $date = date("Ymd");
        } else {
            $date = date("Ymd", strtotime($date));
        }
        // 分页
        if ($page <= 0) {
            $page = 1;
        }
        if ($cntperpage <= 0) {
            $cntperpage = 20;
        }
        $limit = " limit " . ($page - 1) * $cntperpage . " , " . $cntperpage;
        $pagination = M()->query($sql); //

        // 判断部门(非片区and总部)的查询
        $count = count($pagination); //部门数
        //部门查询时,只显示本部门
        if ($count == 0) {
            $sql = "select * from xsrb_department where id =" . $dept_id;
            $count = 1;
        }
        $cntpage = ceil($count / $cntperpage);

        $title = json_decode('{"title":[{"tr":[{"dataType":0,"value":"常规销售日报汇总","rowspan":1,"colspan":"48"}]},{"tr":[{"dataType":0,"value":"部门名称","rowspan":"2","colspan":1},{"dataType":0,"value":"产品类别","rowspan":"2","colspan":1},{"dataType":0,"value":"损益类现金收入","rowspan":1,"colspan":"4"},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":"6"},{"dataType":0,"value":"费用类现金支出","rowspan":1,"colspan":"5"},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":"7"},{"dataType":0,"value":"应收款","rowspan":1,"colspan":1},{"dataType":0,"value":"有效收支","rowspan":1,"colspan":"12"},{"dataType":0,"value":"无效收支","rowspan":1,"colspan":"6"}]},{"tr":[{"dataType":0,"value":"当日销现","rowspan":1,"colspan":1},{"dataType":0,"value":"报废收入","rowspan":1,"colspan":1},{"dataType":0,"value":"运费收入","rowspan":1,"colspan":1},{"dataType":0,"value":"其他收入","rowspan":1,"colspan":1},{"dataType":0,"value":"收回欠款","rowspan":1,"colspan":1},{"dataType":0,"value":"其他应收","rowspan":1,"colspan":1},{"dataType":0,"value":"他应付款","rowspan":1,"colspan":1},{"dataType":0,"value":"增加预收款","rowspan":1,"colspan":1},{"dataType":0,"value":"暂存款","rowspan":1,"colspan":1},{"dataType":0,"value":"资金调入","rowspan":1,"colspan":1},{"dataType":0,"value":"经营费用","rowspan":1,"colspan":1},{"dataType":0,"value":"车辆费用","rowspan":1,"colspan":1},{"dataType":0,"value":"资金调拨","rowspan":1,"colspan":1},{"dataType":0,"value":"支付职工浮动薪酬","rowspan":1,"colspan":1},{"dataType":0,"value":"减少预收款","rowspan":1,"colspan":1},{"dataType":0,"value":"代支款","rowspan":1,"colspan":1},{"dataType":0,"value":"增加固定资产","rowspan":1,"colspan":1},{"dataType":0,"value":"增加低易品与待摊费用","rowspan":1,"colspan":1},{"dataType":0,"value":"支付工资","rowspan":1,"colspan":1},{"dataType":0,"value":"支付预提","rowspan":1,"colspan":1},{"dataType":0,"value":"支付购款","rowspan":1,"colspan":1},{"dataType":0,"value":"待处理","rowspan":1,"colspan":1},{"dataType":0,"value":"新增","rowspan":1,"colspan":1},{"dataType":0,"value":"支付工资","rowspan":1,"colspan":1},{"dataType":0,"value":"外购入库","rowspan":1,"colspan":1},{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1},{"dataType":0,"value":"门或数码销售成本","rowspan":1,"colspan":1},{"dataType":0,"value":"门配销售成本","rowspan":1,"colspan":1},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1},{"dataType":0,"value":"换货支出","rowspan":1,"colspan":1},{"dataType":0,"value":"送货收支","rowspan":1,"colspan":1},{"dataType":0,"value":"暂存商品","rowspan":1,"colspan":1},{"dataType":0,"value":"铺货商品","rowspan":1,"colspan":1},{"dataType":0,"value":"待处理商品","rowspan":1,"colspan":1},{"dataType":0,"value":"报废支出","rowspan":1,"colspan":1},{"dataType":0,"value":"降(升)值亏(盈)","rowspan":1,"colspan":1},{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1},{"dataType":0,"value":"商品暂存","rowspan":1,"colspan":1},{"dataType":0,"value":"报废支出","rowspan":1,"colspan":1},{"dataType":0,"value":"降(升)值亏(盈)","rowspan":1,"colspan":1}]}]}',true);
        $data = array(
            'page'=>$page,
            'cntpage'=>$cntpage,
            'title'=>$title['title']
        );
        $sql = $sql . $limit;
        //分页后的部门集
        $result = M()->query($sql);
        if (strtotime($date)>=strtotime('20170401')){
            $json = '';
            foreach ($result as $kbumen => $vbumen) {
                // 获取销售日报录入
                $query = M()->query("select json from xsrblr_json where dept = ".$vbumen['id']." and date='$date'");
                $rblr=$query[0]['json'];
                if ($rblr == '') {
                    $rblr = $this->tomysql($date, $vbumen['id']);
                }
                $rblr = json_decode($rblr, true);
                foreach($rblr['data'] as $key => $val){
                    if ($key == 0)
                        continue;
                    $arr = $val['tr'];
                    if (count($arr) == 8){  //提取录入项数据
                        foreach($arr as $karr => $varr){
                            if($karr >=3)
                                $new_arr[$varr['type_detail']][] = $varr['value']/10000; //数据以 万 为单位
                        }
                    }
                }
                $leibie = array('防盗门合计','其中:直发','其中:库房','数码产品','门配产品');  //产品行信息
                foreach($leibie as $kleibie=> $vleibie){
                    if ($kleibie ==0){
                        if ($type !='excel')
                            $bumen = '{"dataType":0,"value":"'.$vbumen['dname'].'","rowspan":5,"colspan":1},'; //部门头
                    }
                    else
                        $bumen =null;
                    //部门数据统计json
                    $json .= '{"tr":['.$bumen.'{"dataType":0,"value":"'.$vleibie.'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['损益类现金收入当日销现'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['损益类现金收入报废收入'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['损益类现金收入运费收入'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['损益类现金收入其他收入'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金收入收回欠款'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['资产类现金收入职工还借'][$kleibie]*10000,$new_arr['资产类现金支出职工借款'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['资产类现金收入收押金'][$kleibie]*10000,$new_arr['资产类现金支出支押金'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金收入增加预收款'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['资产类现金收入增加暂存款'][$kleibie]*10000,$new_arr['资产类现金支出减少暂存款'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.($new_arr['资产类现金收入经营部资金调入'][$kleibie]+$new_arr['资产类现金收入代收款'][$kleibie]).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['费用类现金支出经营费用'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['费用类现金支出车辆费用'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.($new_arr['资产类现金支出资金调成总'][$kleibie]+$new_arr['资产类现金支出资金调经营部'][$kleibie]).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出支付职工浮动薪酬'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出减少预收款'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.($new_arr['资产类现金支出代支采购货款'][$kleibie]+$new_arr['资产类现金支出代支其他部门'][$kleibie]).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出增加固定资产'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出增加低易品与待摊费用'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出支付工资'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出支付预提'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出支外购款'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['资产类现金支出待处理'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['应收款新增'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效收入外购入库'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效收入调拨收入'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效支出门或数码销售成本'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效支出门配销售成本'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效支出调拨支出'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效支出换货支出'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['有效支出送货支出'][$kleibie]*10000,$new_arr['有效收入送货收回'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['有效支出减少暂存商品'][$kleibie]*10000,$new_arr['有效收入增加暂存商品'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['有效支出增加铺货商品'][$kleibie]*10000,$new_arr['有效收入减少铺货商品'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['有效支出增加待处理商品'][$kleibie]*10000,$new_arr['有效收入减少待处理商品'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['有效支出报废支出'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub(($new_arr['有效支出调价降值'][$kleibie]+$new_arr['有效支出盘亏'][$kleibie])*10000,($new_arr['有效收入调价升值'][$kleibie]+$new_arr['有效收入盘盈'][$kleibie])*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['无效收入调拨收入'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['无效收入换货收回'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['无效支出调拨支出'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub($new_arr['无效支出减少暂存商品'][$kleibie]*10000,$new_arr['无效收入增加暂存商品'][$kleibie]*10000,2)/10000).'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.$new_arr['无效支出报废支出'][$kleibie].'","rowspan":1,"colspan":1},{"dataType":0,"value":"'.(bcsub(($new_arr['无效支出调价降值'][$kleibie]+$new_arr['无效支出盘亏'][$kleibie])*10000,($new_arr['无效收入调价升值'][$kleibie]+$new_arr['无效收入盘盈'][$kleibie])*10000,2)/10000).'","rowspan":1,"colspan":1}]},';
                }
                $content = json_decode('{"content":['.trim($json,',').']}',true);
                $data['data'][] =array(
                    'depart'=>$vbumen['dname'],
                    'content'=>$content['content']
                );unset($new_arr);unset($json);
            }
            if ($type =='excel')
                return $data;
            else
                $this->response(json_encode($data));
        }
    }
	    
    //部门下载excel
	public function toexcel($token='',$date='')
    {
        header("Access-Control-Allow-Origin:*");
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
                    $sql = "select * from xsrb_excel where `biao` ='cgxsrbhz' and `dept_id` =".$dept_id.' and `date` ='.$date.' limit 1';
                    $result = M()->query($sql);
                    if (count($result))
                    {
						if($_SERVER['SERVER_NAME'] =='172.16.10.252')
						{
							$excel_url ="http://172.16.10.252/files/".$dept_id."-".$result[0]['biao']."-".$date.".xls" ;
						}else
						{
							$excel_url =$result[0]['url'];
						}
    					$arr = array(
    						'url'=>$excel_url
    					);
                    }else 
                    {
                        $arr = array(
    						'url'=>C('Controller_url')."/CGXSRBHZ/uploadExcel/date/".$date."/bumen_id/".$dept_id
    					);
                    }
					//将一维关联数组转换为json字符串
					$json = json_encode($arr);	
					echo $json;
                }
        }
    }
	
    //常规销售日报汇总excel生成
    //Excel表处理
    public function uploadExcel($date ='',$bumen_id = '')
    {
		ini_set('max_execution_time',0);
        ini_set('memory_limit', "-1");		
        header("Access-Control-Allow-Origin:*");
		if($date =='')
		{
			$date = date("Ymd",strtotime("-1 day"));		//根据昨天的数据生成Excel
		}
		$ret = 1;
        $sql = "select id from xsrb_department where id =1 or qt1 =0 ";
        $dept_id = M()->query($sql);
        if ($bumen_id !='' && isset($bumen_id))
        {
            $dept_id = array(
                array('id' =>$bumen_id)
            );
        }

        if (strtotime($date)>=strtotime('20170401')){
            $j = 3;         //数据开始行号
            foreach ($dept_id as $kde=>$vde) {
                $cx = M()->query("select * from xsrb_excel where dept_id =" . $vde['id'] . " and `date` =" . $date . " and `biao` ='cgxsrbhz' ");
                if (!count($cx))  //判断此循环下的部门是否已导入
                {
                    //new一个phpexcel
                    $objPHPExcel = new \PHPExcel();
                    $jsondom = array();
                    $json = $this->search('', 1000, $date, '', 'excel', $vde['id']);    //type=excel时,输出excel文件
                    foreach($json['data'] as $k1=>$v1){
                        //部门信息
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue( 'A'.$j, $v1['depart']);
                        $objPHPExcel->getActiveSheet()->mergeCells('A'.$j.':A'.($j+4));  //以目前5行数据合并
                        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$j)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);


                        //数据信息
                        foreach($v1['content'] as $k2=>$v2){
                            $arr = $v2['tr'];
                            $currentColumn = 'B';
                            for ($i = 0; $i <= 41; $i++)
                            {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue( $currentColumn.$j, $arr[$i]['value'] ); //数据处理
                                $currentColumn++;
                            }
                            $j++;
                        }
                    }
//                    echo json_encode($json);


                $title = array(
                    //第一行
                    array('A1','部门名称'),array('B1','产品类别'), array('C1','损益类现金收入'),array('G1','资产类现金收入'),
                    array('M1','费用类现金支出'), array('R1','资产类现金支出'),array('Y1','应收款'),array('Z1','有效收支'),array('AL1','无效收支'),
                    //第二行
                    array('C2','当日销现'),array('D2','报废收入'),array('E2','运费收入'),array('F2','其他收入'),array('G2','收回欠款'),
                    array('H2','其他应收'), array('I2','他应付款'),array('J2','增加预收款'),array('K2','暂存款'),array('L2','资金调入'),
                    array('M2','经营费用'),array('N2','车辆费用'),array('O2','资金调拨'),array('P2','支付职工浮动薪酬'), array('Q2','减少预存款'),
                    array('R2','代支款'),array('S2','增加固定资产'), array('T2','增加低易品与待摊费用'),
                    array('U2','支付工资'),array('V2','支付预提'),array('W2','支付购款'), array('X2','待处理'), array('Y2','新增'),
                    array('Z2','外购入库'),array('AA2','调拨收入'),array('AB2','门或数码销售成本'),array('AC2','门配销售成本'),
                    array('AD2','调拨支出'),array('AE2','换货支出'),array('AF2','送货收支'),array('AG2','暂存商品'),
                    array('AH2','铺货商品'),array('AI2','待处理商品'),array('AJ2','报废支出'),array('AK2','降(升)值亏(盈)'),array('AL2','调拨收入'),
                    array('AM2','换货收入'),array('AN2','调拨支出'),array('AO2','商品暂存'),
                    array('AP2','报废支出'),array('AQ2','降(升)值亏(盈)')
                );
                //合并单元格
                $hebing = array(
                    'Z1:AK1','AL1:AQ1','M1:Q1','A1:A2','B1:B2','C1:F1','G1:L1','R1:X1'
                );
                foreach($title as $vtitle){
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($vtitle[0],$vtitle[1]);
                }
                foreach($hebing as $vhebing){
                    $objPHPExcel->getActiveSheet()->mergeCells($vhebing);
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle($vhebing)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }
                //背景色
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AQ2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AQ2')->getFill()->getStartColor()->setARGB("0099CCFF");  //浅蓝色
                $objPHPExcel->getActiveSheet()->freezePane('C3');       //冻结单元格
                //生成xls文件,保存在当前项目目录下

                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-cgxsrbhz-'.$date.'.xls';

                if ($bumen_id !='')
                {
                    $fileName = $vde['id'].'-cgxsrbhz-'.$date.'.xls';
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"$fileName\"");
                    header('Cache-Control: max-age=0');
                    $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);;
                    $objWriter->save('php://output'); //文件通过浏览器下载
                    return;
                }
                //生成xls文件,保存在当前项目目录下

                $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-cgxsrbhz-'.$date.'.xls';

                if ($bumen_id !='')
                {
                    $fileName = $vde['id'].'-cgxsrbhz-'.$date.'.xls';
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"$fileName\"");
                    header('Cache-Control: max-age=0');
                    $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);;
                    $objWriter->save('php://output'); //文件通过浏览器下载
                    return;
                }
                $objWriter1 = new \PHPExcel_Writer_Excel5($objPHPExcel);
                $objWriter1->save($keys);
                ob_clean();
                //执行上传的excel文件,返回文件的下载地址
//					$revurl = uploadfile_ali_160112($keys);
//					$xls = json_decode($revurl,true);
                $keys = "http://xsrb.wsy.me:801/files/".$vde['id'].'-cgxsrbhz-'.$date.'.xls';
                $cxo = M()->query("select * from xsrb_excel where dept_id =".$vde['id']." and `date` =".$date." and `biao` ='cgxsrbhz' ");
                if(!count($cxo))
                {
                    if ($keys !='')    //上传成功返回url时,存入数据库
                    {
                        //当前部门的文件下载地址存入数据库
                        $sql = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$vde['id'].",'cgxsrbhz',$date,'$keys')";
                        M()->execute($sql);
                    }
                }
                $ret = -1;
			  }
            }
            if($ret ==1)
                return '{"resultcode":1,"resultmsg":"常规销售日报汇总表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"常规销售日报汇总表上传失败"}';
        }

        foreach ($dept_id as $kde=>$vde)
        {
				$cx = M()->query("select * from xsrb_excel where dept_id =".$vde['id']." and `date` =".$date." and `biao` ='cgxsrbhz' ");
				if (!count($cx))  //判断此循环下的部门是否已导入
				{
                    $jsondom = array();
                    $json = $this->search( '',1000, $date,'','excel',$vde['id']);    //type=excel时,输出excel文件

                    //遍历需要整合的部门
                    foreach ($json as $k=>$v)
                    {
                        foreach ($v as $k1=>$v1)
                        {
                            foreach ($v1 as $k2=>$v2)
                            {
                                if ($k2 =='content')
                                {
                                    foreach ($v2 as $k3=>$v3)
                                    {
                                        $jsondom['data'][]=$v3;     //获取部门json,整合
                                    }
                                }
                            }
                        }
                    }

                    //new一个phpexcel
                    $objPHPExcel = new \PHPExcel();
                    
                    //设置excel标题
                    if (strtotime($date) >=strtotime('20170101'))
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门名称');  $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);      $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','产品类别');       $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','损益类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('C1:F1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','资产类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('G1:N1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O1','费用类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('O1:U1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('O1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('V1','资产了现金支出');   $objPHPExcel->getActiveSheet()->mergeCells('V1:AC1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('V1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD1','应收款');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE1','有效收支');  $objPHPExcel->getActiveSheet()->mergeCells('AE1:AQ1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('AE1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AR1','无效收支');  $objPHPExcel->getActiveSheet()->mergeCells('AR1:AX1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('AR1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2','当日销现'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2','报废收入');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2','运费收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2','其他收入');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2','收回欠款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2','职工还借');      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2','收押金');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2','计提浮动薪酬');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2','增加预收款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2','增加暂存款');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2','资金调入');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2','维修费');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O2','经营费用');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P2','车辆费用'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q2','资金调拨');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R2','职工借款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S2','支押金');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T2','支付职工浮动薪酬');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('U2','减少预存款');$objPHPExcel->setActiveSheetIndex(0)->setCellValue('V2','减少暂存款');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('W2','代支款');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('X2','增加固定资产'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y2','增加低易品与待摊费用');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z2','支付工资'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AA2','支付预提');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AB2','支付购款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AC2','待处理');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD2','新增'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE2','外购入库');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AF2','调拨收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AG2','送货收回');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AH2','门或整机销售成本');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AI2','门配或配件销售成本');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AJ2','调拨支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AK2','换货支出'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AL2','送货支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AM2','暂存商品'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AN2','铺货商品');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AO2','待处理商品'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AP2','报废支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AQ2','降(升)值');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AR2','亏(盈)');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AS2','调拨收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AT2','换货收入');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AU2','调拨住处'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AV2','商品暂存');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AW2','报废支出'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AX2','降(升)值');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AY2','亏(盈)');
                        $nu1 = 1;
                    }else
                    {
                        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门名称');  $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);      $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','产品类别');       $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','损益类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('C1:F1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','资产类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('G1:N1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O1','费用类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('O1:U1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('O1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('V1','资产了现金支出');   $objPHPExcel->getActiveSheet()->mergeCells('V1:AC1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('V1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD1','应收款');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE1','有效收支');  $objPHPExcel->getActiveSheet()->mergeCells('AE1:AQ1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('AE1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AR1','无效收支');  $objPHPExcel->getActiveSheet()->mergeCells('AR1:AX1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('AR1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2','当日销现'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2','报废收入');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2','运费收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2','其他收入');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2','收回欠款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2','职工还借');      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2','收押金');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2','计提浮动薪酬');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2','增加预收款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2','增加暂存款');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2','资金调入');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2','维修费');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O2','经营费用');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P2','车辆费用'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q2','资金调拨');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R2','职工借款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S2','支押金');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T2','支付职工浮动薪酬');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('U2','减少预存款');$objPHPExcel->setActiveSheetIndex(0)->setCellValue('V2','减少暂存款');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('W2','代支款');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('X2','增加固定资产'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y2','增加低易品与待摊费用');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z2','支付工资'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AA2','支付预提');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AB2','支付购款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AC2','待处理');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD2','新增'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE2','外购入库');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AF2','调拨收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AG2','送货收回');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AH2','销售成本');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AI2','调拨支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Aj2','换货支出'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AK2','送货支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AL2','暂存商品'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AM2','铺货商品');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AN2','待处理商品'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AO2','报废支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AP2','降(升)值');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AQ2','亏(盈)');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AR2','调拨收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AS2','换货收入');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AT2','调拨住处'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AU2','商品暂存');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AV2','报废支出'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AW2','降(升)值');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AX2','亏(盈)');
                        $nu1 = 0;
                    }
                    //遍历整合过的jsondom,然后给每个单元格赋值,合并

                    foreach ($jsondom as $k1=>$v1)
                    {
                        foreach ($v1 as $k2=>$v2)
                        {
                            $k2 = $k2+3;        //数据从第三行开始写入
                            foreach ($v2 as $k3=>$v3)
                            {
                                foreach ($v3 as $k4=>$v4)
                                {
                                    //tr为51列时处理数据
                                    if (count($v3) ==(50 + $nu1))
                                    {
                    
                                        $key = chr(ord('A')+$k4).$k2;
                                        if ((ord('A')+$k4) >90)
                                        {
                                            $key = 'A'.chr(ord('A')+$k4-26).$k2;        //ascii码值超过Z时,key从AA开始增加
                                        }
                                    }
                                    //tr为50列时处理数据
                                    else
                                    {
                                        if ((ord('A')+$k4 + 1) >90)     //ascii码值超过Z时,key从AA开始增加
                                        {
                                            $key = 'A'.chr(ord('A')+$k4-25).$k2;
                                        }else
                                        {
                                            $key = chr(ord('A')+$k4+1).$k2;
                                        }
                    
                                    }
                                    $val = $v4['value'];
                                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue( $key, $val );         //给表的单元格设置数据
                    
                                    //需要合并的单元格
                                    if ($v4['rowspan'] >1)
                                    {
                                        $hebing = $key.':'.'A'.($k2+$v4['rowspan']-1);      //合并那些单元格
                                        $objPHPExcel->getActiveSheet()->mergeCells( $hebing );
                                        $objPHPExcel->setActiveSheetIndex(0)->getStyle($key)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);      //水平居中
                                    }
                                }
                            }
                        }
                    }
                    //背景色
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AY2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AY2')->getFill()->getStartColor()->setARGB("0099CCFF");  //浅蓝色
                    
                    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension( 'J')->setWidth(13);         //J列->宽
                    $objPHPExcel->getActiveSheet()->freezePane('C3');       //冻结单元格
                    $objPHPExcel->getActiveSheet()->setTitle('Simple');     // 给当前活动的表设置名称
                    
                    //生成xls文件,保存在当前项目目录下

                    $keys = str_replace('\\','/',realpath(__DIR__.'/../../../')).'/files/'.$vde['id'].'-cgxsrbhz-'.$date.'.xls';
                    
                    if ($bumen_id !='')
                    {
                        $fileName = $vde['id'].'-cgxsrbhz-'.$date.'.xls';
                         header('Content-Type: application/vnd.ms-excel');
    		             header("Content-Disposition: attachment;filename=\"$fileName\"");
    		             header('Cache-Control: max-age=0');
    	  	             $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);;
    	                 $objWriter->save('php://output'); //文件通过浏览器下载
                         return;
                    }
                    $objWriter1 = new \PHPExcel_Writer_Excel5($objPHPExcel);
                    $objWriter1->save($keys);
                    ob_clean();
					//执行上传的excel文件,返回文件的下载地址
//					$revurl = uploadfile_ali_160112($keys);
//					$xls = json_decode($revurl,true);
					$keys = "http://xsrb.wsy.me:801/files/".$vde['id'].'-cgxsrbhz-'.$date.'.xls';
					$cxo = M()->query("select * from xsrb_excel where dept_id =".$vde['id']." and `date` =".$date." and `biao` ='cgxsrbhz' ");
					if(!count($cxo))
					{
						$cxo =1;
	                    if ($keys !='')    //上传成功返回url时,存入数据库
						{	
							//当前部门的文件下载地址存入数据库
							$sql = "insert into xsrb_excel(`createtime`,`dept_id`,`biao`,`date`,`url`) values(now(),".$vde['id'].",'cgxsrbhz',$date,'$keys')";
							M()->execute($sql);
						}					
					}

					$ret = -1;
                }
		}
         if($ret ==1)
            return '{"resultcode":1,"resultmsg":"常规销售日报汇总表上传成功"}';
            else
                return '{"resultcode":-1,"resultmsg":"常规销售日报汇总表上传失败"}';
    }
	
	//xsrblr不存在json时,读取数据库数据
	public function tomysql($date ='',$dept ='')
    {
        if (strtotime($date)>=strtotime('20170401'))     //因为201701修改了销售日报录入的行结构, 要重新使用新的json
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR.txt");
        else
        $filename=str_replace('\\','/',realpath(__DIR__)."/tempJson/XSRBLR_old1.txt");

        $handle=fopen($filename,'r');
        $rblr=fread($handle, filesize($filename));
        fclose($handle);
		$rblr = json_decode($rblr,true);
        $result = M()->query("select * from xsrblr where `dept` =".$dept." and `date` = '".$date."'");
        if (count($result))
        {
            $arr = array();
            foreach($result as $key=>$val)
            {
                $arr[$val['type'].$val['type_detail'].'防盗门合计'] = $val['fdm'];
				$arr[$val['type'].$val['type_detail'].'防盗门'] = $val['fdm'];
                $arr[$val['type'].$val['type_detail'].'其中:直发'] = $val['zf'];
                $arr[$val['type'].$val['type_detail'].'其中:库房'] = $val['kf'];
                $arr[$val['type'].$val['type_detail'].'数码产品'] = $val['sdj'];
				$arr[$val['type'].$val['type_detail'].'门配产品'] = $val['dmb'];
            }
            foreach ($rblr as $k1=>$v1)
            {
                foreach ($v1 as $k2=>$v2)
                {
                    foreach ($v2['tr'] as $k3=>$v3)
                    {
						if ($arr[$v3['type'].$v3['type_detail'].$v3['product']] !='')
                        {
                            $rblr[$k1][$k2]['tr'][$k3]['value'] = $arr[$v3['type'].$v3['type_detail'].$v3['product']];
                        }
                    }
                }
            }
        }

        $rblr = json_encode($rblr);
		return $rblr;
    }
}