<?php
namespace Home\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
class CGXSRBHZController extends RestController
{
    // 计算各部门的常规销售日报汇总并查询
    public function search($page = '', $cntperpage = '', $date = '', $token='',$type= '',$id ='')
    {
        header("Access-Control-Allow-Origin: *");
        $redis = new \Redis();
		$redis->connect(C('REDIS_URL'),"6379");
		$redis->auth(C('REDIS_PWD'));
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

        // 判断模版是否存在
        //销售日报录入模版
        if ($redis->get('report-xsrblr-template') == '') {
            $redis->set('report-xsrblr-template', 
                '{"data":[{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"业务名称","product":"","type":"业务名称","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"项目类别","product":"","type":"业务名称","type_detail":"项目类别"},{"colspan":1,"rowspan":1,"dataType":"0","value":"项目名称","product":"","type":"业务名称","type_detail":"项目类别项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"防盗门","product":"防盗门","type":"业务名称","type_detail":"项目类别项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"三代机","product":"三代机","type":"业务名称","type_detail":"项目类别项目名称"},{"colspan":1,"rowspan":1,"dataType":"0","value":"地面波","product":"地面波","type":"业务名称","type_detail":"项目类别项目名称"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"当日销现","product":"","type":"现金业务","type_detail":"损益类现金收入当日销现"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入当日销现"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入当日销现"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入当日销现"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"报废收入","product":"","type":"现金业务","type_detail":"损益类现金收入报废收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入报废收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入报废收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入报废收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"运费收入","product":"","type":"现金业务","type_detail":"损益类现金收入运费收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入运费收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入运费收入"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入运费收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"损益类现金收入","product":"","type":"现金业务","type_detail":"损益类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"其他收入","product":"","type":"现金业务","type_detail":"损益类现金收入其他收入","child":{"child_data":[{"project":"项目","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"损益类现金收入其他收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"损益类现金收入其他收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"损益类现金收入其他收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"收回欠款","product":"","type":"现金业务","type_detail":"资产类现金收入收回欠款","child":{"child_data":[{"customname":"客户名称","class":"类别","newarrear":"新增欠款","recoverarrear":"收回欠款"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入收回欠款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入收回欠款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"职工还借","product":"","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入职工还借"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入职工还借"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"收押金","product":"","type":"现金业务","type_detail":"资产类现金收入收押金"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入收押金"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入收押金"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入收押金"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加预收款","product":"","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入增加预收款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入增加预收款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加暂存款","product":"","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入增加暂存款"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入增加暂存款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"经营部资金调入","product":"","type":"现金业务","type_detail":"资产类现金收入经营部资金调入","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入经营部资金调入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"代收款","product":"","type":"现金业务","type_detail":"资产类现金收入代收款","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入代收款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入代收款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入代收款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金收入","product":"","type":"现金业务","type_detail":"资产类现金收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"维修费","product":"","type":"现金业务","type_detail":"资产类现金收入维修费"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金收入维修费"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金收入维修费"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金收入维修费"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"费用类现金支出","product":"","type":"现金业务","type_detail":"费用类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"经营费用","product":"","type":"现金业务","type_detail":"费用类现金支出经营费用","child":{"child_data":[{"projectclass":"项目类别","projectname":"项目名称","door":"防盗门","genera":"三代机","ground_wave":"地面波"},{"projectclass":"经营费","projectname":"办公费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"财务费用","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"差旅费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"差旅费补助","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"调拨费用","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"返利","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"其他","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"其他运费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"市内交通费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"水电气费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"税金","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"维修费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"销售运费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"行政管理费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"业务招待费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"邮电费","door":0,"genera":0,"ground_wave":0},{"projectclass":"经营费","projectname":"租赁费","door":0,"genera":0,"ground_wave":0},{"projectclass":"车辆费","projectname":"车杂费","door":0,"genera":0,"ground_wave":0},{"projectclass":"车辆费","projectname":"车修费","door":0,"genera":0,"ground_wave":0},{"projectclass":"车辆费","projectname":"车油费","door":0,"genera":0,"ground_wave":0}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"费用类现金支出经营费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"费用类现金支出经营费用"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"费用类现金支出","product":"","type":"现金业务","type_detail":"费用类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"车辆费用","product":"","type":"现金业务","type_detail":"费用类现金支出车辆费用","child":{"child_data":[{"projectclass":"项目类别","projectname":"项目名称","door":"防盗门","genera":"三代机"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"费用类现金支出车辆费用"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"费用类现金支出车辆费用"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"资金调成总","product":"","type":"现金业务","type_detail":"资产类现金支出资金调成总","child":{"child_data":[{"otherdepartment":"对方部门","accountname":"户名","accountbank":"开户行","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出资金调成总"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出资金调成总"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"资金调经营部","product":"","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出资金调经营部"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出资金调经营部"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"职工借款","product":"","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出职工借款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出职工借款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支押金","product":"","type":"现金业务","type_detail":"资产类现金支出支押金"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支押金"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支押金"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支押金"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支付职工浮动薪酬","product":"","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支付职工浮动薪酬"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少预收款","product":"","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出减少预收款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出减少预收款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少暂存款","product":"","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出减少暂存款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出减少暂存款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"代支采购货款","product":"","type":"现金业务","type_detail":"资产类现金支出代支采购货款","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出代支采购货款"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出代支采购货款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"代支其他部门","product":"","type":"现金业务","type_detail":"资产类现金支出代支其他部门","child":{"child_data":[{"project":"项目","amount":"金额"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出代支其他部门"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出代支其他部门"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加固定资产","product":"","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出增加固定资产"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出增加固定资产"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加低易品与待摊费用","product":"","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出增加低易品与待摊费用"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支付工资","product":"","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付工资"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支付工资"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支付预提","product":"","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支付预提"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支付预提"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"支外购款","product":"","type":"现金业务","type_detail":"资产类现金支出支外购款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出支外购款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出支外购款"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出支外购款"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"资产类现金支出","product":"","type":"现金业务","type_detail":"资产类现金支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"待处理","product":"","type":"现金业务","type_detail":"资产类现金支出待处理"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"现金业务","type_detail":"资产类现金支出待处理"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"现金业务","type_detail":"资产类现金支出待处理"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"现金业务","type_detail":"资产类现金支出待处理"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款","product":"","type":"应收款","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"应收款","product":"","type":"应收款","type_detail":"应收款"},{"colspan":1,"rowspan":1,"dataType":"5","value":"新增","product":"","type":"应收款","type_detail":"应收款新增","child":{"child_data":[{"project":"项目","door":"防盗门","genera":"三代机"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"应收款","type_detail":"应收款新增"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"应收款","type_detail":"应收款新增"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"应收款","type_detail":"应收款新增"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"现金业务","product":"","type":"现金业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"现金结存","product":"","type":"现金业务","type_detail":"现金结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"现金结存","product":"","type":"现金业务","type_detail":"现金结存现金结存"},{"colspan":3,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"现金业务","type_detail":"现金结存现金结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"应收款","product":"","type":"应收款","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"应收款结存","product":"","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"应收款","type_detail":"应收款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"应收款","type_detail":"应收款结存"}]},{"tr":[{"colspan":3,"rowspan":1,"dataType":"6","value":"预收账款结存","product":"","type":"预收账款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"预收账款结存","type_detail":"预收账款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"预收账款结存","type_detail":"预收账款结存"}]},{"tr":[{"colspan":3,"rowspan":1,"dataType":"6","value":"暂存款结存","product":"","type":"暂存款结存","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"暂存款结存","type_detail":"暂存款结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"暂存款结存","type_detail":"暂存款结存"}]},{"tr":[{"colspan":1,"rowspan":8,"dataType":"6","value":"销售情况","product":"","type":"销售情况","type_detail":""},{"colspan":1,"rowspan":4,"dataType":"6","value":"当月","product":"","type":"销售情况","type_detail":"当月"},{"colspan":1,"rowspan":1,"dataType":"6","value":"销售收入累计","product":"","type":"销售情况","type_detail":"当月销售收入累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况","type_detail":"当月销售收入累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况","type_detail":"当月销售收入累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况","type_detail":"当月销售收入累计"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"销售成本累计","product":"","type":"销售成本累计","type_detail":"当月销售成本累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售成本累计","type_detail":"当月销售成本累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售成本累计","type_detail":"当月销售成本累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售成本累计","type_detail":"当月销售成本累计"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"毛利累计","product":"","type":"毛利累计","type_detail":"当月毛利累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"毛利累计","type_detail":"当月毛利累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"毛利累计","type_detail":"当月毛利累计"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"毛利累计","type_detail":"当月毛利累计"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"毛利率","product":"","type":"毛利率","type_detail":"当月毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"毛利率","type_detail":"当月毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"毛利率","type_detail":"当月毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"毛利率","type_detail":"当月毛利率"}]},{"tr":[{"colspan":1,"rowspan":4,"dataType":"6","value":"当日","product":"","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"当日销售收入","product":"","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"当日","type_detail":"当日销售收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"当日","type_detail":"当日销售收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"当日销售成本","product":"","type":"销售情况 ","type_detail":"当日销售成本"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况 ","type_detail":"当日销售成本"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况 ","type_detail":"当日销售成本"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况 ","type_detail":"当日销售成本"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"当日毛利","product":"","type":"销售情况 ","type_detail":"当日毛利"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况 ","type_detail":"当日毛利"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况 ","type_detail":"当日毛利"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况 ","type_detail":"当日毛利"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"6","value":"当日毛利率","product":"","type":"销售情况 ","type_detail":"当日毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"销售情况 ","type_detail":"当日毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"销售情况 ","type_detail":"当日毛利率"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"销售情况 ","type_detail":"当日毛利率"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"外购入库","product":"","type":"商品业务","type_detail":"有效收入外购入库","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入外购入库"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入外购入库"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入外购入库"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"调拨收入","product":"","type":"商品业务","type_detail":"有效收入调拨收入","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入调拨收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"送货收回","product":"","type":"商品业务","type_detail":"有效收入送货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入送货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入送货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入送货收回"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少铺货商品","product":"","type":"商品业务","type_detail":"有效收入减少铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入减少铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入减少铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入减少铺货商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少待处理商品","product":"","type":"商品业务","type_detail":"有效收入减少待处理商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入减少待处理商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入减少待处理商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入减少待处理商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加暂存商品","product":"","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入增加暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价升值","product":"","type":"商品业务","type_detail":"有效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入调价升值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效收入","product":"","type":"商品业务","type_detail":"有效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘盈","product":"","type":"商品业务","type_detail":"有效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效收入盘盈"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"销售成本","product":"","type":"商品业务","type_detail":"有效支出销售成本"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出销售成本"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出销售成本"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出销售成本"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"调拨支出","product":"","type":"商品业务","type_detail":"有效支出调拨支出","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出调拨支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"换货支出","product":"","type":"商品业务","type_detail":"有效支出换货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出换货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出换货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出换货支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"送货支出","product":"","type":"商品业务","type_detail":"有效支出送货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出送货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出送货支出"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出送货支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加铺货商品","product":"","type":"商品业务","type_detail":"有效支出增加铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出增加铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出增加铺货商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出增加铺货商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加待处理商品","product":"","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出增加待处理商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出增加待处理商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少暂存商品","product":"","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出减少暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"报废支出","product":"","type":"商品业务","type_detail":"有效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出报废支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价降值","product":"","type":"商品业务","type_detail":"有效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出调价降值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"有效支出","product":"","type":"商品业务","type_detail":"有效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘亏","product":"","type":"商品业务","type_detail":"有效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"有效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"有效支出盘亏"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"4","value":"调拨收入","product":"","type":"商品业务","type_detail":"无效收入调拨收入","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入调拨收入"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入调拨收入"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"换货收回","product":"","type":"商品业务","type_detail":"无效收入换货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入换货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入换货收回"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入换货收回"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"增加暂存商品","product":"","type":"商品业务","type_detail":"无效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入增加暂存商品"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入增加暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价升值","product":"","type":"商品业务","type_detail":"无效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入调价升值"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入调价升值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效收入","product":"","type":"商品业务","type_detail":"无效收入"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘盈","product":"","type":"商品业务","type_detail":"无效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"三代机","type":"商品业务","type_detail":"无效收入盘盈"},{"colspan":1,"rowspan":1,"dataType":"2","value":"0","product":"地面波","type":"商品业务","type_detail":"无效收入盘盈"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"5","value":"调拨支出","product":"","type":"商品业务","type_detail":"无效支出调拨支出","child":{"child_data":[{"otherpartment":"其他部门","door":"防盗门","genera":"三代机","ground_wave":"地面波"}]}},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出调拨支出"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出调拨支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"报废支出","product":"","type":"商品业务","type_detail":"无效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出报废支出"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出报废支出"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"减少暂存商品","product":"","type":"商品业务","type_detail":"无效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出减少暂存商品"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出减少暂存商品"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"调价降值","product":"","type":"商品业务","type_detail":"无效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出调价降值"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出调价降值"}]},{"tr":[{"colspan":1,"rowspan":1,"dataType":"0","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":1,"rowspan":1,"dataType":"0","value":"无效支出","product":"","type":"商品业务","type_detail":"无效支出"},{"colspan":1,"rowspan":1,"dataType":"0","value":"盘亏","product":"","type":"商品业务","type_detail":"无效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"三代机","type":"商品业务","type_detail":"无效支出盘亏"},{"colspan":1,"rowspan":1,"dataType":"3","value":"0","product":"地面波","type":"商品业务","type_detail":"无效支出盘亏"}]},{"tr":[{"colspan":1,"rowspan":6,"dataType":"6","value":"商品业务","product":"","type":"商品业务","type_detail":""},{"colspan":2,"rowspan":1,"dataType":"6","value":"有效结存","product":"","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"有效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"有效结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"送货结存","product":"","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"送货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"送货结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"无效结存","product":"","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"无效结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"无效结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"暂存商品结存","product":"","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"暂存商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"暂存商品结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"铺货结存","product":"","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"铺货结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"铺货结存"}]},{"tr":[{"colspan":2,"rowspan":1,"dataType":"6","value":"待处理商品结存","product":"","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"防盗门","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"三代机","type":"商品业务","type_detail":"待处理商品结存"},{"colspan":1,"rowspan":1,"dataType":"6","value":"0","product":"地面波","type":"商品业务","type_detail":"待处理商品结存"}]}]}');
        }
        //销售日报汇总
        if ($redis->get('report-title-template') == '') {
            $redis->set('report-title-template', '{"title":[{"tr":[{"dataType":0,"value":"常规销售日报汇总","rowspan":1,"colspan":"50","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"部门名称","rowspan":"2","colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"产品类别","rowspan":"2","colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"损益类现金收入","rowspan":1,"colspan":"4","product":"","type":"","type_detail":""},{"dataType":0,"value":"资产类现金收入","rowspan":1,"colspan":"8","product":"","type":"","type_detail":""},{"dataType":0,"value":"费用类现金支出","rowspan":1,"colspan":"7","product":"","type":"","type_detail":""},{"dataType":0,"value":"资产类现金支出","rowspan":1,"colspan":"8","product":"","type":"","type_detail":""},{"dataType":0,"value":"应收款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"有效收支","rowspan":1,"colspan":"13","product":"","type":"","type_detail":""},{"dataType":0,"value":"无效收支","rowspan":1,"colspan":"7","product":"","type":"","type_detail":""}]},{"tr":[{"dataType":0,"value":"当日销现","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"报废收入","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"运费收入","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"其他收入","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"收回欠款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"职工还借","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"收押金","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"计提浮动薪酬","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"增加预收款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"增加暂存款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"资金调入","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"维修费","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"经营费用","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"车辆费用","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"资金调拨","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"职工借款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"支押金","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"支付职工浮动薪酬","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"减少预收款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"减少暂存款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"代支款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"增加固定资产","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"增加低易品与待摊费用","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"支付工资","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"支付预提","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"支外购款","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"待处理","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"新增","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"外购入库","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"送货收回","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"销售成本","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"换货支出","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"送货支出","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"暂存商品","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"铺货商品","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"待处理商品","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"报废支出","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"降(升)值","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"亏(盈)","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨收入","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"换货收回","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"调拨支出","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"暂存商品","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"报废支出","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"降(升)值","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"亏(盈)","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""}]}]}');
        }
        //部门数据模版
        if ($redis->get('report-depart-template') == '') {
            $redis->set('report-depart-template', '{"depart":"depart","content":[{"tr":[{"dataType":0,"value":"depart","rowspan":"3","colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":"防盗门","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"损益类现金收入当日销现"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"损益类现金收入报废收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"损益类现金收入运费收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"损益类现金收入其他收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入收回欠款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入职工还借"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入收押金"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入计提浮动薪酬"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入增加预收款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入增加暂存款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入资金调入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金收入维修费"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"费用类现金支出经营费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"费用类现金支出车辆费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出资金调拨"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出职工借款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出支押金"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出减少预收款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出减少暂存款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出代支款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出增加固定资产"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出支付工资"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出支付预提"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出支外购款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"资产类现金支出待处理"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"应收款新增"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支外购入库"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支送货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支销售成本"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支送货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支暂存商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支铺货商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支待处理商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支报废支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支降(升)值"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"有效收支亏(盈)"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"无效收支调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"无效收支","type_detail":"无效收支换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"无效收支调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"无效收支暂存商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"无效收支报废支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"无效收支降(升)值"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"防盗门","type":"","type_detail":"无效收支亏(盈)"}]},{"tr":[{"dataType":0,"value":"三代机","rowspan":1,"colspan":1},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"损益类现金收入当日销现"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"损益类现金收入报废收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"损益类现金收入运费收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"损益类现金收入其他收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入收回欠款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入职工还借"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入收押金"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入计提浮动薪酬"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入增加预收款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入增加暂存款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入资金调入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金收入维修费"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"费用类现金支出经营费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"费用类现金支出车辆费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出资金调拨"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出职工借款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出支押金"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出减少预收款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出减少暂存款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出代支款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出增加固定资产"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出支付工资"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出支付预提"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出支外购款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"资产类现金支出待处理"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"应收款新增"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支外购入库"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支送货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支销售成本"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支送货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支暂存商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支铺货商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支待处理商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支报废支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支降(升)值"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"有效收支亏(盈)"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支暂存商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支报废支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支降(升)值"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"三代机","type":"","type_detail":"无效收支亏(盈)"}]},{"tr":[{"dataType":0,"value":"地面波","rowspan":1,"colspan":1,"product":"","type":"","type_detail":""},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"损益类现金收入当日销现"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"损益类现金收入报废收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"损益类现金收入运费收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"损益类现金收入其他收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入收回欠款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入职工还借"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入收押金"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入计提浮动薪酬"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入增加预收款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入增加暂存款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入资金调入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金收入维修费"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"费用类现金支出经营费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"费用类现金支出车辆费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出资金调拨"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出职工借款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出支押金"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出支付职工浮动薪酬"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出减少预收款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出减少暂存款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出代支款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出增加固定资产"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出增加低易品与待摊费用"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出支付工资"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出支付预提"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出支外购款"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"资产类现金支出待处理"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"应收款新增"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支外购入库"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支送货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支销售成本"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支换货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支送货支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支暂存商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支铺货商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支待处理商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支报废支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支降(升)值"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"有效收支亏(盈)"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"无效收支调拨收入"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"无效收支","type_detail":"无效收支换货收回"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"无效收支调拨支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"无效收支暂存商品"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"无效收支报废支出"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"无效收支降(升)值"},{"dataType":0,"value":0,"rowspan":1,"colspan":1,"product":"地面波","type":"","type_detail":"无效收支亏(盈)"}]}]}');
        }
        $depart = json_decode($redis->get('report-depart-template'), true); //获取部门数据模版
        
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
        
        $sql = $sql . $limit;
        //分页后的部门集
        $result = M()->query($sql);
        foreach ($result as $key => $val) 
        {
            // 获取销售日报录入
            $rblr = $redis->get("report-" . $val['id'] . "-" . $date . "-XSRBLR");
            if ($rblr == '') 
            {
                $rblr = $this->tomysql($date,$val['id']);
            }
            $rblr = json_decode($rblr,true);
            
            // 有效收入 有效支出 合并为有效收支,并且录入数据存入shuju数组
            foreach ($rblr['data'] as $x1 => $j1) {     //遍历rblr
                if ($x1 > 0) {
                    $tr = $j1['tr'];
                    foreach ($tr as $x2 => $j2) {
                        if ($j2['product'] != '') 
                        {
                            if (strpos("%**#" . $j2['type_detail'], '有效')) {        //判断type_detail里面有'有效'时,type_detail值改为有效收支
                                $j2['type_detail'] = '有效收支' . mb_substr($j2['type_detail'], 4, mb_strlen($j2['type_detail'], 'utf-8'), 'utf-8');
                            } elseif (strpos("%**#" . $j2['type_detail'], '无效')) {      //判断type_detail里面有'无效'时,type_detail值改为无效收支
                                $j2['type_detail'] = '无效收支' . mb_substr($j2['type_detail'], 4, mb_strlen($j2['type_detail'], 'utf-8'), 'utf-8');
                            }
                            $shuju[$val['dname'].$j2['product'].'资产类现金收入计提浮动薪酬'] = 0;  //计提浮动薪酬 =0
                            $shuju[$val['dname'].$j2['product'] . $j2['type_detail']] = $j2['value'];   //所有rblr的数据存入shuju数组里面
                        }
                    }
                }
            }
            // 需要计算的数据
            foreach ($rblr['data'] as $x1 => $j1) {     //遍历rblr,根据shuju数组里面的数据计算
                if ($x1 > 0) {
                    $tr = $j1['tr'];
                    foreach ($tr as $x2 => $j2) {
                        if ($j2['product'] != '') 
                        {
                            //资金调入的计算方法  = 经营部资金调入+代收款
                            if ($j2['type_detail'] == '资产类现金收入经营部资金调入' || $j2['type_detail'] == '资产类现金收入代收款') {
                                if ($j2['type_detail'] == '资产类现金收入经营部资金调入') {
                                    $shuju[$val['dname'].$j2['product'] . '1'] = $j2['value'];     //临时存入一个变量中
                                }
                                if ($j2['type_detail'] == '资产类现金收入代收款') {
                                    $shuju[$val['dname'].$j2['product'] . '2'] = $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '资产类现金收入资金调入'] = $shuju[$val['dname'].$j2['product'] . '1'] + $shuju[$val['dname'].$j2['product'] . '2'];
                            } 
                            //资金调拨 = 资金调成总+资金调经营部
                            elseif ($j2['type_detail'] == '资产类现金支出资金调成总' || $j2['type_detail'] == '资产类现金支出资金调经营部') {
                                if ($j2['type_detail'] == '资产类现金支出资金调成总') {
                                    $shuju[$val['dname'].$j2['product'] . '1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '资产类现金支出资金调经营部') {
                                    $shuju[$val['dname'].$j2['product'] . '2'] = $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '资产类现金支出资金调拨'] = $shuju[$val['dname'].$j2['product'] . '1'] + $shuju[$val['dname'].$j2['product'] . '2'];
                            } 
                            //代支款 = 代支采购货款+代支其他部门
                            elseif ($j2['type_detail'] == '资产类现金支出代支采购货款' || $j2['type_detail'] == '资产类现金支出代支其他部门') {
                                if ($j2['type_detail'] == '资产类现金支出代支采购货款') {
                                    $shuju[$val['dname'].$j2['product'] . '1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '资产类现金支出代支其他部门') {
                                    $shuju[$val['dname'].$j2['product'] . '2'] = $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '资产类现金支出代支款'] = $shuju[$val['dname'].$j2['product'] . '1'] + $shuju[$val['dname'].$j2['product'] . '2'];
                            } 
                            //暂存商品 = 有效减少暂存商品-有效增加暂存商品
                            elseif ($j2['type_detail'] == '有效支出减少暂存商品' || $j2['type_detail'] == '有效收入增加暂存商品') {
                                if ($j2['type_detail'] == '有效支出减少暂存商品') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支暂存商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '有效收入增加暂存商品') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支暂存商品2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '有效收支暂存商品'] = $shuju[$val['dname'].$j2['product'] . '有效收支暂存商品1'] + $shuju[$val['dname'].$j2['product'] . '有效收支暂存商品2'];
                            } 
                            //铺货商品 = 有效增加铺货商品-有效减少铺货商品
                            elseif ($j2['type_detail'] == '有效支出增加铺货商品' || $j2['type_detail'] == '有效收入减少铺货商品') {
                                if ($j2['type_detail'] == '有效支出增加铺货商品') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支铺货商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '有效收入减少铺货商品') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支铺货商品2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '有效收支铺货商品'] = $shuju[$val['dname'].$j2['product'] . '有效收支铺货商品1'] + $shuju[$val['dname'].$j2['product'] . '有效收支铺货商品2'];
                            } 
                            //待处理商品 = 有效增加待处理商品-有效减少待处理商品
                            elseif ($j2['type_detail'] == '有效支出增加待处理商品' || $j2['type_detail'] == '有效收入减少待处理商品') {
                                if ($j2['type_detail'] == '有效支出增加待处理商品') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支待处理商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '有效收入减少待处理商品') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支待处理商品2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '有效收支待处理商品'] = $shuju[$val['dname'].$j2['product'] . '有效收支待处理商品1'] + $shuju[$val['dname'].$j2['product'] . '有效收支待处理商品2'];
                            } 
                            //降(升)值 = 有效调价降值-有效调价升值
                            elseif ($j2['type_detail'] == '有效支出调价降值' || $j2['type_detail'] == '有效收入调价升值') {
                                if ($j2['type_detail'] == '有效支出调价降值') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支降(升)值1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '有效收入调价升值') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支降(升)值2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '有效收支降(升)值'] = $shuju[$val['dname'].$j2['product'] . '有效收支降(升)值1'] + $shuju[$val['dname'].$j2['product'] . '有效收支降(升)值2'];
                            } 
                            //亏(盈) = 有效盘亏-有效盘盈
                            elseif ($j2['type_detail'] == '有效支出盘亏' || $j2['type_detail'] == '有效收入盘盈') {
                                if ($j2['type_detail'] == '有效支出盘亏') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支亏(盈)1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '有效收入盘盈') {
                                    $shuju[$val['dname'].$j2['product'] . '有效收支亏(盈)2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '有效收支亏(盈)'] = $shuju[$val['dname'].$j2['product'] . '有效收支亏(盈)1'] + $shuju[$val['dname'].$j2['product'] . '有效收支亏(盈)2'];
                            } 
                            //暂存商品 = 无效减少暂存商品-无效增加暂存商品
                            elseif ($j2['type_detail'] == '无效支出减少暂存商品' || $j2['type_detail'] == '无效收入增加暂存商品') {
                                if ($j2['type_detail'] == '无效支出减少暂存商品') {
                                    $shuju[$val['dname'].$j2['product'] . '无效收支暂存商品1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '无效收入增加暂存商品') {
                                    $shuju[$val['dname'].$j2['product'] . '无效收支暂存商品2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '无效收支暂存商品'] = $shuju[$val['dname'].$j2['product'] . '无效收支暂存商品1'] + $shuju[$val['dname'].$j2['product'] . '无效收支暂存商品2'];
                            } 
                            //降(升)值 = 无效调价降值-无效调价升值
                            elseif ($j2['type_detail'] == '无效支出调价降值' || $j2['type_detail'] == '无效收入调价升值') {
                                if ($j2['type_detail'] == '无效支出调价降值') {
                                    $shuju[$val['dname'].$j2['product'] . '无效收支降(升)值1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '无效收入调价升值') {
                                    $shuju[$val['dname'].$j2['product'] . '无效收支降(升)值2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '无效收支降(升)值'] = $shuju[$val['dname'].$j2['product'] . '无效收支降(升)值1'] + $shuju[$val['dname'].$j2['product'] . '无效收支降(升)值2'];
                            } 
                            //亏(盈) = 无效盘亏-无效盘盈
                            elseif ($j2['type_detail'] == '无效支出盘亏' || $j2['type_detail'] == '无效收入盘盈') {
                                if ($j2['type_detail'] == '无效支出盘亏') {
                                    $shuju[$val['dname'].$j2['product'] . '无效收支亏(盈)1'] = $j2['value'];
                                }
                                if ($j2['type_detail'] == '无效收入盘盈') {
                                    $shuju[$val['dname'].$j2['product'] . '无效收支亏(盈)2'] = - $j2['value'];
                                }
                                $shuju[$val['dname'].$j2['product'] . '无效收支亏(盈)'] = $shuju[$val['dname'].$j2['product'] . '无效收支亏(盈)1'] + $shuju[$val['dname'].$j2['product'] . '无效收支亏(盈)2'];
                            }
                        }
                    }
                }
            }
            
            //汇总表里面单位以 万元 为单位
            foreach ($shuju as $kk=>$vv)
            {
                $shuju[$kk] = $vv/10000;
            }
            
            // 部门销售日报数据更新
            $depart['depart'] = $val['dname']; //更新部门信息
            $depart['content'][0]['tr'][0]['value'] = $val['dname']; 
            foreach ($depart['content'] as $k => $v) {      //遍历部门模版,更新当前部门的数据
                foreach ($v as $k1 => $v1) {
                    foreach ($v1 as $k3 => $v3) {
                        if ($v3['product'] != '' && $v3['type_detail'] != '') 
                        {
                            //数据赋值
                            $depart['content'][$k][$k1][$k3]['value'] = $shuju[$val['dname'].$v3['product'] . $v3['type_detail']];
                        }
                    }
                }
            }
            $depart_json = json_encode($depart);
            $cgxsrbhz["report-" . $val['id'] . "-" . $date . "-CGXSRBHZ"] = $depart_json; //生成部门日报汇总json数据,存入数组
        }
        $title = $redis->get("report-title-template"); //获取日报汇总表的标题
        //组合各部门json数据
        $data = ",\"data\":[";
        if (count($result) > 0) 
        {
            // 组合全部门json
            foreach ($result as $k => $v) {
                if ($cgxsrbhz["report-" . $v['id'] . "-" . $date . "-CGXSRBHZ"] != '')       //提取查询的部门日报汇总,组合在一起
                    $data .= $cgxsrbhz["report-" . $v['id'] . "-" . $date . "-CGXSRBHZ"] . ',';    
                else {
                    $retmsg = array(
                        'retmsgcode' => - 1,
                        'retmsgresult' => '当前日期未报表录入!'
                    );
                    $this->response($retmsg, 'json');
                }
            }
            $title = trim($title, '}');
            $title = trim($title, '{');
            //组合 分页,title,data 的数据json,
            $json = "{\"cntperpage\":" . $cntperpage . ",\"cntpage\":" . $cntpage . ",\"page\":" . $page . ',' . $title . rtrim($data, ',') . ']' . '}';
            if ($type =='excel')
            {
                return $json;
            }else 
            {
                $this->response($json);
            }
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
		ini_set('max_execution_time',2000);
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
        foreach ($dept_id as $kde=>$vde)
        {
				$cx = M()->query("select * from xsrb_excel where dept_id =".$vde['id']." and `date` =".$date." and `biao` ='cgxsrbhz' ");
				if (!count($cx))  //判断此循环下的部门是否已导入
				{
                    $jsondom = array();
                    $json = json_decode($this->search( '',1000, $date,'','excel',$vde['id']),true);    //type=excel时,输出excel文件
                    
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
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','部门名称');  $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);      $objPHPExcel->getActiveSheet()->mergeCells('A1:A2');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','产品类别');       $objPHPExcel->setActiveSheetIndex(0)->getStyle('B1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER); $objPHPExcel->getActiveSheet()->mergeCells('B1:B2');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','损益类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('C1:F1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('C1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G1','资产类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('G1:N1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('G1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O1','费用类现金收入');   $objPHPExcel->getActiveSheet()->mergeCells('O1:U1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('O1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('V1','资产了现金支出');   $objPHPExcel->getActiveSheet()->mergeCells('V1:AC1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('V1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD1','应收款');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE1','有效收支');  $objPHPExcel->getActiveSheet()->mergeCells('AE1:AQ1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('AE1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AR1','无效收支');  $objPHPExcel->getActiveSheet()->mergeCells('AR1:AX1');   $objPHPExcel->setActiveSheetIndex(0)->getStyle('AR1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2','当日销现'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D2','报废收入');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2','运费收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2','其他收入');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G2','收回欠款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2','职工还借');      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2','收押金');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J2','计提浮动薪酬');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K2','增加预收款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L2','增加暂存款');     $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M2','资金调入');   $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N2','维修费');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O2','经营费用');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P2','车辆费用'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q2','资金调拨');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R2','职工借款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S2','支押金');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T2','支付职工浮动薪酬');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('U2','减少预存款');$objPHPExcel->setActiveSheetIndex(0)->setCellValue('V2','减少暂存款');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('W2','代支款');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('X2','增加固定资产'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Y2','增加低易品与待摊费用');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Z2','支付工资'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AA2','支付预提');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AB2','支付购款'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AC2','待处理');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AD2','新增'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AE2','外购入库');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AF2','调拨收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AG2','送货收回');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AH2','销售成本');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AI2','调拨支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Aj2','换货支出'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AK2','送货支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AL2','暂存商品'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AM2','铺货商品');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AN2','待处理商品'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AO2','报废支出');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AP2','降(升)值');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AQ2','亏(盈)');    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AR2','调拨收入'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AS2','换货收入');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AT2','调拨住处'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AU2','商品暂存');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AV2','报废支出'); $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AW2','降(升)值');  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('AX2','亏(盈)');
                    
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
                                    //tr为50列时处理数据
                                    if (count($v3) ==50 )
                                    {
                    
                                        $key = chr(ord('A')+$k4).$k2;
                                        if ((ord('A')+$k4) >90)
                                        {
                                            $key = 'A'.chr(ord('A')+$k4-26).$k2;        //ascii码值超过Z时,key从AA开始增加
                                        }
                                    }
                                    //tr为49列时处理数据
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
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AX2')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->setActiveSheetIndex(0)->getStyle('A1:AX2')->getFill()->getStartColor()->setARGB("0099CCFF");  //浅蓝色
                    
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
    	  	             $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
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
							$sql = "insert into xsrb_excel(`dept_id`,`biao`,`date`,`url`) values(".$vde['id'].",'cgxsrbhz',$date,'$keys')";
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
	
	//xsrblr不存在redis时,读取数据库数据
	public function tomysql($date ='',$dept ='')
    {
		$redis = new \Redis();
		$redis->connect(C('REDIS_URL'),"6379");
 		$redis->auth(C('REDIS_PWD'));
		$rblr = json_decode($redis->get('report-xsrblr-template'),true);
        $result = M()->query("select * from xsrblr where `dept` =".$dept." and `date` = '".$date."'");
        if (count($result))
        {
            $arr = array();
            foreach($result as $key=>$val)
            {
                $arr[$val['type'].$val['type_detail'].'防盗门'] = $val['fdm'];
                $arr[$val['type'].$val['type_detail'].'三代机'] = $val['sdj'];
            }
            foreach ($rblr as $k1=>$v1)
            {
                foreach ($v1 as $k2=>$v2)
                {
                    foreach ($v2['tr'] as $k3=>$v3)
                    {
                        $rblr[$k1][$k2]['tr'][$k3]['value'] = $arr[$v3['type'].$v3['type_detail'].$v3['product']];
                    }
                }
            }
        }
        $rblr = json_encode($rblr);
		$redis->set("report-".$dept."-".$date."-XSRBLR",$rblr);
		return $rblr;
    }
}