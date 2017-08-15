<?php
namespace Home\Controller;

use Think\Controller\RestController;

class CGXSController extends RestController
{
    //计算各部门的常规销售日报汇总并存入redis
    public function submit()
    {
        header("Access-Control-Allow-Origin: *");
//         echo '<pre/>';
        $redis = new \Redis();
        $redis->connect($this->url,"6379");
        $date = date("Ymd");
        $depart = json_decode($redis->get('report-depart-template'),true);

        $sql = "select * from depart";
        $result = M()->query($sql);
        foreach ($result as $key=>$val)
        {
            //销售日报录入-现金业务数据-商品业务
            $rblr = json_decode($redis->get("report-admin-20160602-XSRBLR"),true);
            foreach ($rblr['data'] as $x1=>$j1)
            {
                if ($x1>0)
                {
                    $tr = $j1['tr'];
                    foreach ($tr as $x2=>$j2)
                    {
                        if ($j2['product'] !='')
                        {
                            //有效收入 有效支出 合并为有效收支
                            if (strpos("%**#".$j2['type_detail'], '有效'))
                            {
                                $j2['type_detail'] = '有效收支'.mb_substr($j2['type_detail'],4,mb_strlen($j2['type_detail'],'utf-8'),'utf-8');
                            }elseif (strpos("%**#".$j2['type_detail'], '无效'))
                            {
                                $j2['type_detail'] = '无效收支'.mb_substr($j2['type_detail'],4,mb_strlen($j2['type_detail'],'utf-8'),'utf-8');
                            }
            
                            $shuju[$j2['product'].$j2['type_detail']] = $j2['value'];
                        }
                    }
                }
            }
            
            foreach ($rblr['data'] as $x1=>$j1)
            {
                if ($x1>0)
                {
                    $tr = $j1['tr'];
                    foreach ($tr as $x2=>$j2)
                    {
                        if ($j2['product'] !='')
                        {
                            //需要计算的数据
                            if ($j2['type_detail'] =='资产类现金收入经营部资金调入' || $j2['type_detail'] =='资产类现金收入代收款')
                            {
                                if ($j2['type_detail'] == '资产类现金收入经营部资金调入')
                                {
                                    $shuju[$j2['product'].'1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='资产类现金收入代收款')
                                {
                                    $shuju[$j2['product'].'2'] = $j2['value'];
                                }
                                $shuju[$j2['product'].'资产类现金收入资金调入'] = $shuju[$j2['product'].'1'] + $shuju[$j2['product'].'2'];
                            }elseif ($j2['type_detail'] =='资产类现金支出资金调成总' || $j2['type_detail'] =='资产类现金支出资金调经营部')
                            {
                                if ($j2['type_detail'] == '资产类现金支出资金调成总')
                                {
                                    $shuju[$j2['product'].'1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='资产类现金支出资金调经营部')
                                {
                                    $shuju[$j2['product'].'2'] = $j2['value'];
                                }
                                $shuju[$j2['product'].'资产类现金支出资金调拨'] = $shuju[$j2['product'].'1'] + $shuju[$j2['product'].'2'];
                            }elseif ($j2['type_detail'] =='资产类现金支出代支采购货款' || $j2['type_detail'] =='资产类现金支出代支其他部门')
                            {
                                if ($j2['type_detail'] == '资产类现金支出代支采购货款')
                                {
                                    $shuju[$j2['product'].'1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='资产类现金支出代支其他部门')
                                {
                                    $shuju[$j2['product'].'2'] = $j2['value'];
                                }
                                $shuju[$j2['product'].'资产类现金支出代支款'] = $shuju[$j2['product'].'1'] + $shuju[$j2['product'].'2'];
                            }elseif ($j2['type_detail'] =='有效支出减少暂存商品' || $j2['type_detail'] =='有效收入增加暂存商品')
                            {
                                if($j2['type_detail'] =='有效支出减少暂存商品')
                                {
                                    $shuju[$j2['product'].'有效收支暂存商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='有效收入增加暂存商品')
                                {
                                    $shuju[$j2['product'].'有效收支暂存商品2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'有效收支暂存商品'] = $shuju[$j2['product'].'有效收支暂存商品1']+$shuju[$j2['product'].'有效收支暂存商品2'] ;
                            }elseif ($j2['type_detail'] =='有效支出增加铺货商品' || $j2['type_detail'] =='有效收入减少铺货商品')
                            {
                                if ($j2['type_detail'] =='有效支出增加铺货商品')
                                {
                                    $shuju[$j2['product'].'有效收支铺货商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='有效收入减少铺货商品')
                                {
                                    $shuju[$j2['product'].'有效收支铺货商品2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'有效收支铺货商品'] = $shuju[$j2['product'].'有效收支铺货商品1']+$shuju[$j2['product'].'有效收支铺货商品2'];
            
                            }elseif ($j2['type_detail'] =='有效支出增加待处理商品' || $j2['type_detail'] =='有效收入减少待处理商品')
                            {
                                if ($j2['type_detail'] =='有效支出增加待处理商品')
                                {
                                    $shuju[$j2['product'].'有效收支待处理商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='有效收入减少待处理商品')
                                {
                                    $shuju[$j2['product'].'有效收支待处理商品2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'有效收支待处理商品'] = $shuju[$j2['product'].'有效收支待处理商品1']+$shuju[$j2['product'].'有效收支待处理商品2'];
            
                            }elseif ($j2['type_detail'] =='有效支出调价降值' || $j2['type_detail'] =='有效收入调价升值')
                            {
                                if ($j2['type_detail'] =='有效支出调价降值')
                                {
                                    $shuju[$j2['product'].'有效收支降(升)值1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='有效收入调价升值')
                                {
                                    $shuju[$j2['product'].'有效收支降(升)值2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'有效收支降(升)值'] = $shuju[$j2['product'].'有效收支降(升)值1']+$shuju[$j2['product'].'有效收支降(升)值2'];
            
                            }elseif ($j2['type_detail'] =='有效支出盘亏' || $j2['type_detail'] =='有效收入盘盈')
                            {
                                if ($j2['type_detail'] =='有效支出盘亏')
                                {
                                    $shuju[$j2['product'].'有效收支亏(盈)1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='有效收入盘盈')
                                {
                                    $shuju[$j2['product'].'有效收支亏(盈)2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'有效收支亏(盈)'] = $shuju[$j2['product'].'有效收支亏(盈)1']+$shuju[$j2['product'].'有效收支亏(盈)2'];
            
                            }elseif ($j2['type_detail'] =='无效支出减少暂存商品' || $j2['type_detail'] =='无效收入增加暂存商品')
                            {
                                if ($j2['type_detail'] =='无效支出减少暂存商品')
                                {
                                    $shuju[$j2['product'].'无效收支暂存商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='无效收入增加暂存商品')
                                {
                                    $shuju[$j2['product'].'无效收支暂存商品2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'无效收支暂存商品'] = $shuju[$j2['product'].'无效收支暂存商品1']+$shuju[$j2['product'].'无效收支暂存商品2'];
            
                            }elseif ($j2['type_detail'] =='无效支出调价降值' || $j2['type_detail'] =='无效收入调价升值')
                            {
                                if ($j2['type_detail'] =='无效支出调价降值')
                                {
                                    $shuju[$j2['product'].'无效收支降(升)值1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='无效收入调价升值')
                                {
                                    $shuju[$j2['product'].'无效收支降(升)值2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'无效收支降(升)值'] = $shuju[$j2['product'].'无效收支降(升)值1']+$shuju[$j2['product'].'无效收支降(升)值2'];
            
                            }elseif ($j2['type_detail'] =='无效支出盘亏' || $j2['type_detail'] =='无效收入盘盈')
                            {
                                if ($j2['type_detail'] =='无效支出盘亏')
                                {
                                    $shuju[$j2['product'].'无效收支亏(盈)1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] =='无效收入盘盈')
                                {
                                    $shuju[$j2['product'].'无效收支亏(盈)2'] =- $j2['value'];
                                }
                                $shuju[$j2['product'].'无效收支亏(盈)'] = $shuju[$j2['product'].'无效收支亏(盈)1']+$shuju[$j2['product'].'无效收支亏(盈)2'];
            
                            }
                        }
                    }
                }
            }

            //部门销售日报数据更新
            foreach ($depart['content'] as $k=>$v)
            {
                foreach ($v as $k1=>$v1)
                {
                    foreach ($v1 as $k3=>$v3)
                    {
                        if ($v3['product'] !='' &&  $v3['type_detail'] !='')
                        {
                            if ($shuju[$v3['product'].$v3['type_detail']] !='')
                                $depart['content'][$k][$k1][$k3]['value'] = $shuju[$v3['product'].$v3['type_detail']];
                        }
                    }
                }
            }
//             $depart['depart'] = $val['depart'];
            $depart['depart'] = 'admin';
//             $depart['content'][0]['tr'][0]['value'] = $val['depart'];
            $depart['content'][0]['tr'][0]['value'] = 'admin';
            $depart_json = json_encode($depart);
//             print_r($shuju);return;
//             $redis->set("report-".$val['md5']."-".$date."-CGXSRBHZ",$depart_json);
            $redis->set("report-admin-".$date."-CGXSRBHZ",$depart_json);
        }
        
    }
    
    //合并,查询全部门的常规销售日报汇总表
    public function search($page='',$cntperpage=5,$date='20160430')
    {
        header("Access-Control-Allow-Origin: *");
        
        $redis = new \Redis();
        $redis->connect($this->url,"6379");
//         echo $redis->get('report-admin-20160602-XSRBLR');return;
        if ($date =='')
        {
            $date = date("Ymd");
        }
        else
        {
            $date = date("Ymd",strtotime($date));
        }
//         echo $redis->get('DEPART');return;
        //分页
        if ($page <= 0) {
            $page = 1;
        }
        if ($cntperpage <= 0) {
            $cntperpage = 20;
        }
//         echo $date;return;
        $limit = " limit " . ($page - 1) * $cntperpage . " , " . $cntperpage ;
        $sql = "select * from depart";
        $pagination = M()->query($sql);
        $count = count($pagination);
        $cntpage = ceil($count / $cntperpage);
        
        $sql = "select * from depart ".$limit;
        $result = M()->query($sql);
//         $title = $redis->get("report-title-CGXSRBHZ");
        $title = $redis->get("title");
        $data = ",\"data\":[";
        if (count($result) >0)
        {
            //组合全部门json
            foreach ($result as $k=>$v)
            {
                $data .= $redis->get("report-".$v['md5']."-".$date."-CGXSRBHZ").',';           
            }
            $title = trim($title,'}');
            $title = trim($title,'{');
            $json = "{\"cntperpage\":".$cntperpage.",\"cntpage\":".$cntpage.",\"page\":".$page.','.$title.rtrim($data,',').']'.'}';
//             echo $title.trim($data,',').']'.'}';return;
            $this->response($json);
        }else 
        {
            $retmsg = array(
                'retmsgcode'=>-1,
                'retmsgresult'=>'当前日期未报表录入!'
            );
            $this->response($retmsg,'json');
        }
        
    }
}