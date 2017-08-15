<?php

namespace Home\Controller;

use Think\Controller\RestController;

/**
 * 销售日报表期初
 */
class XSRBQCController extends RestController
{

    //销售日报表期初提交
    public function submit($token = "")
    {
        header("Access-Control-Allow-Origin: *");
        //验证token
        $userinfo = checktoken($token);
        if (!$userinfo) {
            $this->response(retmsg(-2), 'json');
            return;
        }
        $jsonData = json_decode(file_get_contents("php://input"), true);
        //当前月
        $now = date("Ym", strtotime(TODAY));
        /*         if (date("Ym",strtotime($now)) == '201704'){
                    $this->response(retmsg(-41),'json');
                } */
        $dept = $userinfo['dept_id'];
        $data = $jsonData['data'];
        $sql = "replace into xsrbqc(fdm,zf,kf,sdj,dmb,type,type_detail,date,dept) values ";
        foreach ($data as $key => $tr) {
            $tds = $tr['tr'];
            //第一行不入库
            if ($key > 0) {
                $sql .= "(";
                foreach ($tds as $td) {
                    $type = trim($td['type']);
                    $type_detail = trim($td['type_detail']);
                    $product = trim($td['product']);
                    $value = $td['value'];
                    if (is_numeric($value))
                        $sql .= "$value,";
                }
                $sql .= "'$type','$type_detail',date_format(now(),'%Y-%m'),'$dept'),";
            }
        }
        $sql = substr($sql, 0, strlen($sql) - 1);
        $result = M()->execute($sql);

        //当天保存过销售日报录入之后,修改期初数据,更新数据表中的 json数据
        $date = date("Ymd");
        $query = M()->query("select json from xsrblr_json where dept = $dept and date='$date'");
        $json = $query[0]['json'];
        if ($json == '') {
            $filename = str_replace('\\', '/', realpath(__DIR__) . "/tempJson/XSRBLR.txt");
            $handle = fopen($filename, 'r');
            $json = fread($handle, filesize($filename));
            fclose($handle);
        }
        //调用销售日报录入保存接口
        $gx = new XSRBLRController();
        $gx->submit($json, $token, 'gx', '', '', '', 'qc', 'a20170208');

        if ($result)
            echo '{"resultcode":0,"resultmsg":"保存成功"}';
        else
            echo '{"resultcode":1,"resultmsg":"保存失败，数据库发生了未知错误"}';
    }

    public function search($token = "", $tj_dept = '', $tj_date = '')
    {
        header("Access-Control-Allow-Origin: *");
        if (empty($tj_date)) {
            //验证token
            $userinfo = checktoken($token);
            if (!$userinfo) {
                $this->response(retmsg(-2), 'json');
                return;
            }
            $now = date("Y-m", strtotime(TODAY));
            $dept = $userinfo['dept_id'];
        } else {
            $dept = $tj_dept;
            $now = $tj_date;
        }

        //获取本地模版数据
        $filename = str_replace('\\', '/', realpath(__DIR__) . "/tempJson/XSRBQC.txt");
        $handle = fopen($filename, 'r');
        $json = fread($handle, filesize($filename));
        fclose($handle);
        //当前月
        //查询xsrbqc数据
        $query = M()->query("select * from xsrbqc where dept = $dept and date='$now'");
        if (!count($query)) {
            //没有数据直接返回json模版
            $js = $json;
        } else {
            //归类数据方便json赋值
            foreach ($query as $value) {
                $result[$value['type_detail']]['防盗门合计'] = $value['fdm'];
                $result[$value['type_detail']]['其中:直发'] = $value['zf'];
                $result[$value['type_detail']]['其中:库房'] = $value['kf'];
                $result[$value['type_detail']]['数码产品'] = $value['sdj'];
                $result[$value['type_detail']]['门配产品'] = $value['dmb'];
            }
            $data = json_decode($json, true);
            //json赋值
            foreach ($data['data'] as $key => $val) {
                foreach ($val['tr'] as $ktr => $vtr) {
                    if (isset($result[$vtr['type_detail']][$vtr['product']])) {
                        $data['data'][$key]['tr'][$ktr]['value'] = $result[$vtr['type_detail']][$vtr['product']];
                    }
                }
            }
            $js = json_encode($data);
        }
        if (empty($tj_dept)) {
            echo $js;
        } else {
            return $js;
        }
    }
}


?>