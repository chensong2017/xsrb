<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/26
 * Time: 17:47
 */
namespace Api\Controller;
use Think\Controller\RestController;
class IndexController extends RestController
{
    public function index($sdate ='20150601',$edate='20160601',$dept='',$biao='',$type ='del',$zhiding ='')
    {
        $mx_json = M()->query("select * from fdm_json order by id");
        $mulu = M()->query("select * from fdm_json order by id");
        foreach($mulu as $kmulu =>$vmulu)
        {
            if ($vmulu['men'] !='')
                $men =$vmulu['men'];
            if ($vmulu['fenlei'])
                $fenlei =$vmulu['fenlei'];
            $sql = "update fdm_json set men='$men',fenlei='$fenlei' where id =".$vmulu['id'];
            M()->execute($sql);
        }
        //mx_json1

        $str_mx1 ='{ "data": [ { "tr": [ { "dataType": 0, "value": "防盗门库存表明细", "rowspan": 1, "colspan": "16", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "门类型", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "门类别", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "表面处理", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "调拨收入", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他收入", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "销售量", "rowspan": 1, "colspan": "3", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效收支", "rowspan": 1, "colspan": "4", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "无效收支", "rowspan": 1, "colspan": "4", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "成都生产", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "齐河生产", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "外购门", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "报废支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "调拨支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效结存", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效转入", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "报废支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "无效结存", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "depart", "rowspan": 1, "colspan": "16", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }';
        foreach($mx_json as $vmx1)
        {
            $men = $vmx1['men'];
            $fenlei = $vmx1['fenlei'];
            $biaomian = $vmx1['biaomian'];
            $aa = ',{ "tr": [ { "dataType": 0, "value": "'.$men.'", "rowspan": "1", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "1", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "调拨收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "其他收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "成都生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "齐河生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "外购门" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "调拨支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "有效结存" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "有效转入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "无效结存" } ] }';
            $str_mx1 .=$aa;
        }
        $str_mx1 .=']}';
        $file1 = 'newjson/mx_json1.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $str_mx1);
        fclose($fp);

        //mx_json2
        $str_mx2 = '{"data": [{ "tr": [ { "dataType": 0, "value": "depart", "rowspan": 1, "colspan": "16", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }';
        foreach($mx_json as $vmx2)
        {
            $men = $vmx2['men'];
            $fenlei = $vmx2['fenlei'];
            $biaomian = $vmx2['biaomian'];
            $aa = ', { "tr": [ { "dataType": 0, "value": "'.$men.'", "rowspan": "1", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "1", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "调拨收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "其他收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "成都生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "齐河生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "外购门" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "调拨支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "有效结存" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "有效转入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "无效结存" } ] }';
            $str_mx2 .=$aa;
        }
        $str_mx2 .=']}';
        $file1 = 'newjson/mx_json2.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $str_mx2);
        fclose($fp);

        //qc_json1
        $qc_json1 = '{ "page": 1, "total": 1, "pagesize": 1, "leftTitle": [ { "tr": [ { "dataType": 0, "value": "片区", "rowspan": 1, "colspan": "3" } ] }, { "tr": [ { "dataType": 0, "value": "销售部门", "rowspan": 1, "colspan": "3" } ] }, { "tr": [ { "dataType": 0, "value": "门类型", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "门类别", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "表面处理", "rowspan": 1, "colspan": 1 } ] }';
        $oldmen = 'xx';
        $oldfenlei = 'xx';
        foreach($mx_json as $vqc1)
        {
            $men = $vqc1['men'];
            $cntmen = M()->query("select count(men) as cntmen from fdm_json where men='$men'");

            $fenlei = $vqc1['fenlei'];
            $cntfenlei = M()->query("select count(fenlei) as cntfenlei from fdm_json where fenlei='$fenlei' and men ='$men'");
            if ($fenlei =='进户门' || $fenlei =='进户子母门')
                $cntfenlei = array(array('cntfenlei'=>0));

            $biaomian = $vqc1['biaomian'];

            if($men !=$oldmen)
            {
                $aa = ', { "tr": [ { "dataType": 0, "value": "'.$men.'", "rowspan": "'.$cntmen[0]['cntmen'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "'.$cntfenlei[0]['cntfenlei'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1 } ] }';
                $oldmen = $men;
                $oldfenlei = $fenlei;
            }else
            {
                if ($fenlei != $oldfenlei)
                {
                    $aa =',{"tr": [{"dataType": 0,"value": "'.$fenlei.'","rowspan": "'.$cntfenlei[0]['cntfenlei'].'","colspan": 1},{"dataType": 0,"value": "'.$biaomian.'","rowspan": 1,"colspan": 1}]}';
                    $oldfenlei = $fenlei;
                }else
                {
                    $aa = ',{"tr": [{"dataType": 0,"value": "'.$biaomian.'","rowspan": 1,"colspan": 1}]}';
                }
            }
            $qc_json1 .=$aa;
        }
        $qc_json1 .=']}';
        $file1 = 'newjson/qc_json1.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $qc_json1);
        fclose($fp);

        //qc_json2
        $qc_json2 = '{ "topTitle": [ { "tr": [ { "dataType": 0, "value": "齐河销售处", "rowspan": 1, "colspan": "2" } ] }, { "tr": [ { "dataType": 0, "value": "齐河防盗门一科", "rowspan": 1, "colspan": "2" } ] }, { "tr": [ { "dataType": 0, "value": "结存量", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "无效结存量", "rowspan": 1, "colspan": 1 } ] } ], "content": [';
        foreach($mx_json as $vqc2)
        {
            $men = $vqc2['men'];
            $fenlei = $vqc2['fenlei'];
            $biaomian = $vqc2['biaomian'];
            $aa ='{ "tr": [ { "dataType": "1", "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "结存量" }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "无效结存量" } ] },';
            $qc_json2 .=$aa;
        }
        $qc_json2 = trim($qc_json2,',').']}';  //去掉多余的一个 ',';

        $file1 = 'newjson/qc_json2.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $qc_json2);
        fclose($fp);

        //qc_json3
        $qc_json3 = '{ "content": [ { "tr": [ { "dataType": 0, "value": "大区", "rowspan": 1, "colspan": "2" } ] }, { "tr": [ { "dataType": 0, "value": "部门", "rowspan": 1, "colspan": "2" } ] }, { "tr": [ { "dataType": 0, "value": "结存量", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "无效结存量", "rowspan": 1, "colspan": 1 } ] }';
        foreach($mx_json as $vqc3)
        {
            $men = $vqc3['men'];
            $fenlei = $vqc3['fenlei'];
            $biaomian = $vqc3['biaomian'];
            $aa = ', { "tr": [ { "dataType": "1", "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "结存量" }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "无效结存量" } ] }';
            $qc_json3 .=$aa;
        }
        $qc_json3 .= ']}';  //去掉多余的一个 ',';
        $file1 = 'newjson/qc_json3.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $qc_json3);
        fclose($fp);

        //tj_json1
        $tj_json1 = '{ "page": 1, "pagesize": 3, "total": 4, "leftTitle": [ { "tr": [ { "dataType": 0, "value": "片区", "rowspan": "1", "colspan": 3 } ] }, { "tr": [ { "dataType": 0, "value": "部门", "rowspan": "1", "colspan": 3 } ] }, { "tr": [ { "dataType": 0, "value": "属性", "rowspan": "1", "colspan": 3 } ] }, { "tr": [ { "dataType": 0, "value": "门类型", "rowspan": "1", "colspan": 1 }, { "dataType": 0, "value": "门类别", "rowspan": "1", "colspan": 1 }, { "dataType": 0, "value": "表面处理", "rowspan": "1", "colspan": 1 } ] }';
        $tjoldmen = 'xx';
        $tjoldfenlei = 'xx';
        foreach($mx_json as $vtj1)
        {
            $men = $vtj1['men'];
            $cntmen = M()->query("select count(men) as cntmen from fdm_json where men='$men'");

            $fenlei = $vtj1['fenlei'];
            $cntfenlei = M()->query("select count(fenlei) as cntfenlei from fdm_json where fenlei='$fenlei' and men ='$men'");
            if ($fenlei =='进户门' || $fenlei =='进户子母门')
                $cntfenlei = array(array('cntfenlei'=>0));
            $biaomian = $vtj1['biaomian'];

            if($men !=$tjoldmen)
            {
                $aa = ', { "tr": [ { "dataType": 0, "value": "'.$men.'", "rowspan": "'.$cntmen[0]['cntmen'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "'.$cntfenlei[0]['cntfenlei'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1 } ] }';
                $tjoldmen = $men;
                $tjoldfenlei = $fenlei;
            }else
            {
                if ($fenlei != $tjoldfenlei)
                {
                    $aa =',{"tr": [{"dataType": 0,"value": "'.$fenlei.'","rowspan": "'.$cntfenlei[0]['cntfenlei'].'","colspan": 1},{"dataType": 0,"value": "'.$biaomian.'","rowspan": 1,"colspan": 1}]}';
                    $tjoldfenlei = $fenlei;
                }else
                {
                    $aa = ',{"tr": [{"dataType": 0,"value": "'.$biaomian.'","rowspan": 1,"colspan": 1}]}';
                }
            }
            $tj_json1 .=$aa;
        }
        $tj_json1 .=']}';
        $file1 = 'newjson/tj_json1.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $tj_json1);
        fclose($fp);

        //tj_json2
        $tj_json2 = '{ "topTitle": [ { "tr": [ { "dataType": 0, "value": "area", "rowspan": 1, "colspan": "13", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "depart", "rowspan": 1, "colspan": "13", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "调拨收入", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他收入", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "销售量", "rowspan": 1, "colspan": "3", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效收支", "rowspan": 1, "colspan": "4", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "无效收支", "rowspan": 1, "colspan": "4", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "自产直发销售", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "自产库房销售", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "外购销售", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "报废支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "调拨支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效结存", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效转入", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "报废支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "无效结存", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" } ] } ], "content": [';
        foreach($mx_json as $vtj2)
        {
            $men = $vtj2['men'];
            $fenlei = $vtj2['fenlei'];
            $biaomian = $vtj2['biaomian'];
            $aa ='{ "tr": [ { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "调拨收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "其他收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "成都生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "齐河生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "外购门" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "调拨支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "有效结存" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "有效转入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "无效结存" } ] },';
            $tj_json2 .=$aa;
        }
        $tj_json2 = trim($tj_json2,',').']}';  //去掉多余的一个 ',';
        $file1 = 'newjson/tj_json2.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $tj_json2);
        fclose($fp);

        //tj_json3
        foreach($mx_json as $vtj3)
        {
            $tj3[$vtj3['men'].$vtj3['fenlei'].$vtj3['biaomian'].'有效收支有效结存'] = 0;
            $tj3[$vtj3['men'].$vtj3['fenlei'].$vtj3['biaomian'].'无效收支无效结存'] = 0;
        }
        $tj_json3 = serialize($tj3);
        $file1 = 'newjson/tj_json3.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $tj_json3);
        fclose($fp);

        //tj_json4
        $shuju = array(
            'dbsr','qtsr','cdsc','qhsc','wgm','bfzc','dbzc','qtzc','yxjc','yxzr','wxbfzc','wxqtzc','wxjc'
        );
        foreach($mx_json as $kvtj4=>$vtj4)
        {
            $tj4[$kvtj4]['product'] = $vtj4['men'].$vtj4['fenlei'].$vtj4['biaomian'];
            foreach($shuju as $kk=>$vv)
            {
                $tj4[$kvtj4][$vv] =0;
            }

        }
        $tj_json4 = serialize($tj4);
        $file1 = 'newjson/tj_json4.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $tj_json4);
        fclose($fp);

        //tj_json5
        foreach($mx_json as $vtj5)
        {
            $tj5[] = $vtj5['men'].$vtj5['fenlei'].$vtj5['biaomian'];
        }
        $tj_json5 = serialize($tj5);
        $file1 = 'newjson/tj_json5.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $tj_json5);
        fclose($fp);

        //fdmkc
        $fdmkc = '{ "data": [ { "tr": [ { "dataType": 0, "value": "防盗门库存表", "rowspan": 1, "colspan": "16", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "门类型", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "门类别", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "表面处理", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "调拨收入", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他收入", "rowspan": "2", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "销售量", "rowspan": 1, "colspan": "3", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效收支", "rowspan": 1, "colspan": "4", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "无效收支", "rowspan": 1, "colspan": "4", "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "成都生产", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "齐河生产", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "外购门", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "报废支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "调拨支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效结存", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "有效转入", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "报废支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "其他支出", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "无效结存", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" } ] }, { "tr": [ { "dataType": 0, "value": "合计", "rowspan": 1, "colspan": "3", "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "", "type_detail": "调拨收入" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "", "type_detail": "其他收入" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "销售量", "type_detail": "成都生产" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "销售量", "type_detail": "齐河生产" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "销售量", "type_detail": "外购门" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "有效收支", "type_detail": "报废支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "有效收支", "type_detail": "调拨支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "有效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "有效收支", "type_detail": "有效结存" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "无效收支", "type_detail": "有效转入" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "无效收支", "type_detail": "报废支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "无效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "合计", "product_type": "", "type": "无效收支", "type_detail": "无效结存" } ] }';
        $fdmoldmen = 'xx';
        $fdmoldfenlei = 'xx';
        foreach($mx_json as $vfdm)
        {
            $men = $vfdm['men'];
            $cntmen = M()->query("select count(men) as cntmen from fdm_json where men='$men'");

            $fenlei = $vfdm['fenlei'];
            $cntfenlei = M()->query("select count(fenlei) as cntfenlei from fdm_json where fenlei='$fenlei' and men ='$men'");
            if ($fenlei =='进户门' || $fenlei =='进户子母门')
                $cntfenlei = array(array('cntfenlei'=>0));
            $biaomian = $vfdm['biaomian'];

            if($men !=$fdmoldmen)
            {
                $aa = ', { "tr": [ { "dataType": 0, "value": "'.$men.'", "rowspan": "'.$cntmen[0]['cntmen'].'", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "'.$cntfenlei[0]['cntfenlei'].'", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "调拨收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "其他收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "成都生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "齐河生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "外购门" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "调拨支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "有效结存" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "有效转入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "无效结存" } ] }';
                $fdmoldmen = $men;
                $fdmoldfenlei = $fenlei;
            }else
            {
                if ($fenlei != $fdmoldfenlei)
                {
                    $aa =', { "tr": [ { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "'.$cntfenlei[0]['cntfenlei'].'", "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1, "product": "", "product_type": "", "type": "", "type_detail": "" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "调拨收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "", "type_detail": "其他收入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "成都生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "齐河生产" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "销售量", "type_detail": "外购门" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "调拨支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "有效收支", "type_detail": "有效结存" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "有效转入" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "报废支出" }, { "dataType": 1, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "其他支出" }, { "dataType": 0, "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "product_type": "", "type": "无效收支", "type_detail": "无效结存" } ] }';
                    $fdmoldfenlei = $fenlei;
                }else
                {
                    $aa =',{"tr":[{"dataType":0,"value":"'.$biaomian.'","rowspan":1,"colspan":1,"product":"","product_type":"","type":"","type_detail":""},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"","type_detail":"调拨收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"","type_detail":"其他收入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"销售量","type_detail":"成都生产"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"销售量","type_detail":"齐河生产"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"销售量","type_detail":"外购门"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"有效收支","type_detail":"报废支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"有效收支","type_detail":"调拨支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"有效收支","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"有效收支","type_detail":"有效结存"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"无效收支","type_detail":"有效转入"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"无效收支","type_detail":"报废支出"},{"dataType":1,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"无效收支","type_detail":"其他支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"'.$men.$fenlei.$biaomian.'","product_type":"","type":"无效收支","type_detail":"无效结存"}]}';
                }
            }
            $fdmkc .=$aa;
        }
        $fdmkc .=']}';
        $file1 = 'newjson/FDMKC.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $fdmkc);
        fclose($fp);

        //fdmkcqc
        $fdmkcqc ='{ "data": [ { "tr": [ { "dataType": 0, "value": "防盗门库存期初表", "rowspan": 1, "colspan": "5" } ] }, { "tr": [ { "dataType": 0, "value": "门类型", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "门类别", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "表面处理", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "有效结存量", "rowspan": 1, "colspan": 1 }, { "dataType": 0, "value": "无效结存量", "rowspan": 1, "colspan": 1 } ] }';
        $fdmqcoldmen = 'xx';
        $fdmqcoldfenlei = 'xx';
        foreach($mx_json as $vfdm)
        {
            $men = $vfdm['men'];
            $cntmen = M()->query("select count(men) as cntmen from fdm_json where men='$men'");

            $fenlei = $vfdm['fenlei'];
            $cntfenlei = M()->query("select count(fenlei) as cntfenlei from fdm_json where fenlei='$fenlei' and men ='$men'");
            if ($fenlei =='进户门' || $fenlei =='进户子母门')
                $cntfenlei = array(array('cntfenlei'=>0));
            $biaomian = $vfdm['biaomian'];

            if($men !=$fdmqcoldmen)
            {
                $aa = ', { "tr": [ { "dataType": 0, "value": "'.$men.'", "rowspan": "'.$cntmen[0]['cntmen'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "'.$cntfenlei[0]['cntfenlei'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1 }, { "dataType": "1", "value": 0, "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "有效结存" }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "无效结存" } ] }';
                $fdmqcoldmen = $men;
                $fdmqcoldfenlei = $fenlei;
            }else
            {
                if ($fenlei != $fdmqcoldfenlei)
                {
                    $aa =', { "tr": [ { "dataType": 0, "value": "'.$fenlei.'", "rowspan": "'.$cntfenlei[0]['cntfenlei'].'", "colspan": 1 }, { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1 }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "有效结存" }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "无效结存" } ] }';
                    $fdmqcoldfenlei = $fenlei;
                }else
                {
                    $aa =', { "tr": [ { "dataType": 0, "value": "'.$biaomian.'", "rowspan": 1, "colspan": 1 }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "有效结存" }, { "dataType": "1", "value": "0", "rowspan": 1, "colspan": 1, "product": "'.$men.$fenlei.$biaomian.'", "type_detail": "无效结存" } ] }';
                }
            }
            $fdmkcqc .=$aa;
        }
        $fdmkcqc .=']}';
        $file1 = 'newjson/FDMKCQC.txt';
        $fp = fopen($file1, 'w');
        fwrite($fp, $fdmkcqc);
        fclose($fp);
    }
}
