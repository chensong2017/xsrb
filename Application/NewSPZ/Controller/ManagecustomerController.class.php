<?php
namespace NewSPZ\Controller;
use Think\Controller\RestController;
include_once 'Application/Common/Classes/PHPExcel.php';
include_once 'upload/uploadfile.php';
/**
 * web端对销售日报客户进行增删改查操作
 * */
class ManagecustomerController extends RestController{
    /**
     * 私有参数，利用其保存post过来的数据
     * @vsr string 均为string类型，其中id为int类型
     * @access private
     */
    private $id;
    private $user_tel;
    private $user_name;
    private $level_id;
    private $province;
    private $city;
    private $country;
    private $addr_detail;

    /**
     * 获取新增、修改客户信息操作传递过来的数据
     * @access private
     * @param string $data post的数据
     */
    private function getpostdata($data){
        $this->id = safe($data["id"]);
        $this->user_tel = safe($data["data"]["user_tel"]);
        $this->user_name = safe($data["data"]["user_name"]);
        $this->level_id = safe($data["data"]["level_id"]);
        $this->province = safe($data["data"]["province"]);
        $this->city = safe($data["data"]["city"]);
        $this->country= safe($data["data"]["country"]);
        $this->addr_detail = $data["data"]["addr_detail"];
    }

    /**
     * 根据客户电话或者姓名查询客户信息，也可全查
     * @access public
     * @param string $token 根据token获取用户信息
     * @param string $uid   根据客户电话精准查询用户信息
     * @param string $uname 根据客户姓名模糊查询客户信息
     * @return object json对象：查询成功 or 失败
     */
    public function customers($token="",$uid="",$uname="",$page=1,$pagesize=20){
        header("Access-Control-Allow-Origin:*");
        $Model = M();
        switch($this->_method) {
            case 'get':{
                $userinfo = checktoken($token);
                if (! $userinfo) {
                    $this->response(retmsg(- 2), 'json');
                    return;
                }
                $dept_id = $userinfo['dept_id'];
                $where = "";
                if($uid != ''){
                    $where .=" phone='".$uid."' and";
                }
                if($uname!=''){
                    $where .=" name like '%".$uname."%' and";
                }
                $temp_sql = "select a.*,b.dname from xsrb_customer_info as a,xsrb_department as b where a.dept_id=b.id and a.dept_id='$dept_id'";
                if($where == ""){
                    $sql = $temp_sql;
                }else{
                    $where = rtrim($where,' and');
                    $sql = $temp_sql." and".$where;
                }
                $result = $Model->query($sql);
                $n = count($result);
                $m = ceil($n/$pagesize);
                $l = ($page-1)*$pagesize;

                if($where == "") {
                    $sql1 = $temp_sql." order by a.created_at desc limit ".$l.",".$pagesize;
                }else{
                    $where = rtrim($where,' and');
                    $sql1 = $temp_sql." and".$where." order by a.created_at desc limit ".$l.",".$pagesize;
                }
                $list = $Model->query($sql1);
                if($list == null){
                    $this->response(retmsg(-1,null,"当前部门暂无客户信息"),'json');
                    return;
                }else{
                    $data = array();
                    $data["page"] = $page;
                    $data["pagecount"] = $m;
                    $data["subitem"] = $list;
                    $this->response(retmsg(0,$data,"承运信息查询成功"),'json');
                }
                break;
            }
        }
    }

    public function customer_levels($token=""){
        header("Access-Control-Allow-Origin:*");
        $Model = M();
        switch($this->_method) {
            case 'get':{
                $data = array();
                $data['subitem'] = array(array("id"=>1,"level_name"=>"一级"),
                    array("id"=>2,"level_name"=>"二级"),
                    array("id"=>3,"level_name"=>"三级"),
                    array("id"=>4,"level_name"=>"四级"),
                    array("id"=>5,"level_name"=>"五级"),);
                $this->response(retmsg(0,$data,'查询用户等级成功'),'json');
                break;
            }
        }
    }


    /**
     * 部门人员新增客户信息
     * @access public
     * @param string $token 根据获取部门操作员所在部门
     */
    public function add_custom($token=""){
        header("Access-Control-Allow-Origin:*");
        $Model = M();
        switch($this->_method) {
            case 'post':{
                $userinfo = checktoken($token);
                if (! $userinfo) {
                    $this->response(retmsg(- 2), 'json');
                    return;
                }
                $dept_id = $userinfo['dept_id'];
                $data = json_decode(file_get_contents("php://input"), true);
                $this->getpostdata($data);
                $time = date("Y-m-d H:i:s");
                $uname = $userinfo['user_name'];
//                if($this->level_id == 1){
//                    $level = "一级";
//                }elseif($this->level_id == 2){
//                    $level = "二级";
//                }elseif($this->level_id == 3){
//                    $level = "三级";
//                }elseif($this->level_id == 4){
//                    $level = "四级";
//                }else{
//                    $level = "五级";
//                }
                $sql = "select phone from xsrb_customer_info where phone='$this->user_tel' and dept_id='$dept_id'";
                $result = $Model->query($sql);
                if($result != null){
                    $this->response(retmsg(-1,null,"该客户已经存在"),'json');
                }else{
                    //客户信息入库
                    $insert = "insert into xsrb_customer_info(phone,name,level,addr_province,
addr_city,addr_country,addr_detail,created_at,created_by,modified_at,modified_by,dept_id) values ('".safe($this->user_tel)."','"
                        .safe($this->user_name)."','".safe($this->level_id)."','".safe($this->province)."','".safe($this->city)."','"
                        .safe($this->country)."','".safe($this->addr_detail)."','".safe($time)."','".safe($uname)."','"
                        .safe($time)."','".safe($uname)."',".safe($dept_id).")";
                    $is_insert = $Model->execute($insert);
                    if(!$is_insert){
                        $this->response(retmsg(-1,null,"添加客户信息失败"),'json');
                    }else{
                        $this->response(retmsg(0,null,"添加客户信息成功"),'json');
                    }
                }
                break;
            }
        }
    }

    /**
     * 部门人员修改客户信息
     * @access public
     * @param string $token 根据获取部门操作员信息
     */
    public function update_custom($token=""){
        header("Access-Control-Allow-Origin:*");
        $Model = M();
        switch($this->_method) {
            case 'post':{
                $userinfo = checktoken($token);
                if (! $userinfo) {
                    $this->response(retmsg(- 2), 'json');
                    return;
                }
                $dept_id = $userinfo['dept_id'];
                $data = json_decode(file_get_contents("php://input"), true);
                $this->getpostdata($data);
                $time = date("Y-m-d H:i:s");
                $uname = $userinfo['user_name'];
//                if($this->level_id == 1){
//                    $level = "一级";
//                }elseif($this->level_id == 2){
//                    $level = "二级";
//                }elseif($this->level_id == 3){
//                    $level = "三级";
//                }elseif($this->level_id == 4){
//                    $level = "四级";
//                }else{
//                    $level = "五级";
//                }
                $sql = "select phone from xsrb_customer_info where phone='$this->user_tel' and dept_id='$dept_id' and id!='$this->id'";
                $result = $Model->query($sql);
                if($result != null){
                    $this->response(retmsg(-1,null,"该客户已经存在"),'json');
                }else{
                    //修改客户信息
                    $update = "update xsrb_customer_info set phone='$this->user_tel',name='$this->user_name',
level='$this->level_id',addr_province='$this->province',addr_city='$this->city',addr_country='$this->country',
addr_detail='$this->addr_detail',modified_at='$time',modified_by='$uname' where id='$this->id'";
                    $is_update = $Model->execute($update);
                    if(!$is_update){
                        $this->response(retmsg(-1,null,"修改客户信息失败"),'json');
                    }else{
                        $this->response(retmsg(0,null,"修改客户信息成功"),'json');
                    }
                }
                break;
            }
        }
    }


    /**
     * 部门人员删除客户信息
     * @access public
     * @param string $token 根据获取部门操作员信息
     */
    public function del_custom($token=""){
        header("Access-Control-Allow-Origin:*");
        $Model = M();
        switch($this->_method) {
            case 'post':{
                $userinfo = checktoken($token);
                if (! $userinfo) {
                    $this->response(retmsg(- 2), 'json');
                    return;
                }
                $dept_id = $userinfo['dept_id'];
                $data = json_decode(file_get_contents("php://input"), true);
                $id = $data["id"];
                $sql = "SELECT a.phone from xsrb_customer_info a,new_xsmx b where a.phone=b.phone and a.dept_id=b.dept and a.id=$id and a.dept_id=$dept_id";
                $result = $Model->query($sql);
                if($result[0]['phone'] != null){
                    $this->response(retmsg(-1,null,"当前客户有销售记录，不可随意删除"),'json');
                    return;
                }
                $del_sql = "delete from xsrb_customer_info where id='$id'";
                $del_result = $Model->execute($del_sql);
                if($del_result){
                    $this->response(retmsg(0,null,"成功删除客户信息"),'json');
                }else{
                    $this->response(retmsg(-1,null,"删除客户信息失败"),'json');
                }
                break;
            }
        }
    }


    /**
     * 客户管理导入excel
     */
    public function load_custom_excel($token=""){
        header("Access-Control-Allow-Origin: *");
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
//            echo "total hang :".$highestRow;
            //客户级别限定

            if ($objPHPExcel->getActiveSheet()->getCell("A2")->getValue() !='日期')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'A列不是日期'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("B2")->getValue() !='客户姓名')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'B列不是客户姓名'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("C2")->getValue() !='客户级别')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'C列不是客户级别'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("D2")->getValue() !='省(直辖市)')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'D列不是省(直辖市)'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("E2")->getValue() !='地(市)')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'E列不是地(市)'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("F2")->getValue() !='县(市)')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'F列不是县(市)'),'json');
//            if ($objPHPExcel->getActiveSheet()->getCell("G2")->getValue() !='乡镇')
//                $this->response(array('resultcode'=>-1,'resultmsg'=>'G列不是乡镇'),'json');
            if ($objPHPExcel->getActiveSheet()->getCell("H2")->getValue() !='电话')
                $this->response(array('resultcode'=>-1,'resultmsg'=>'H列不是电话'),'json');

            $level_check = "select * from level_type ";
            $re = M()->query($level_check);
            $temp = array();
            foreach($re as $key=>$val){
                foreach($val as $k1=>$v1){
                    if (empty($v1))
                        continue;
                    if ( !in_array($temp[$k1],$v1))
                        $temp[$k1][] = $v1;
                }
            }
            for ($j = 4; $j <= $highestRow; $j++){//从第4行开始取数据
                $m++;
                $date = empty($objPHPExcel->getActiveSheet()->getCell("A$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("A$j")->getValue();
                $name = empty($objPHPExcel->getActiveSheet()->getCell("B$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("B$j")->getValue();
                $level = empty($objPHPExcel->getActiveSheet()->getCell("C$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("C$j")->getValue();
                $province = empty($objPHPExcel->getActiveSheet()->getCell("D$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("D$j")->getValue();
                $city = empty($objPHPExcel->getActiveSheet()->getCell("E$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("E$j")->getValue();
                $district = empty($objPHPExcel->getActiveSheet()->getCell("F$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("F$j")->getValue();
                $country = empty($objPHPExcel->getActiveSheet()->getCell("G$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("G$j")->getValue();
                $tel = empty($objPHPExcel->getActiveSheet()->getCell("H$j")->getValue())?'':$objPHPExcel->getActiveSheet()->getCell("H$j")->getValue();

                $flag = in_array($level,$temp['level_name'])?1:0;
                if($flag){
                    $create_time = date("Y-m-d H:i:s");
                    $sql .= "($types,$dept,'$date','" . $shangpinjc . "','" . $zhizaobm . "','" . $dingdanlb . "','" . $dangci . "','" . $menkuang . "','" . $kuanghou . "','" . $qianbanhou . "','" . $houbanhou . "','" . $dikuangcl . "','" . $menshan . "','" . $guige . "','".$kaixiang."','".$jiaolian."','".$huase."','".$biaomianfs."','".$biaomianyq."','".$chuanghua."','".$maoyan."','".$biaopai."','".$zhusuo."','".$fusuo."','".$suoba."','".$biaojian."','".$baozhuangpp."','".$baozhuangfs."','".$qita."', $bydj , $xydj , $sl ,'$qichuje','$create_time','$product_md5'),";
                    $n++;
                }else{
                    $code = '';
                    $code .= $flag?'':(empty($level)?'客户级别列不能为空；':"制造部门:[".$zhizaobm."]不存在；");
                }
            }
        }
    }

    //省市县三级联动查询
    public function shengshixian(){
        header("Access-Control-Allow-Origin:*");
        $sql_search = "select json from xsrb_kehugrade_json ORDER  by id desc limit 1";
        $result = M()->query($sql_search);
        echo $result[0]['json'];return;
    }

    //数据存储为json
    public function  saveJson(){
        $sql_chaxun = "select province from xsrb_kehugrade GROUP by province order by id ";
        $result = M()->query($sql_chaxun);
        foreach($result as $key=>$val){
            $sql_shi = "select city from xsrb_kehugrade where province='".$val['province']."' GROUP by city order by id";
            $city = M()->query($sql_shi);
            foreach ($city as $key2=>$val2) {
                $sql_country = "select country from xsrb_kehugrade where province='".$val['province']."' and city ='".$val2['city']."' order by id";
                $country = M()->query($sql_country);
                foreach ($country as $key3=>$val3){
                    $data['citylist'][$key]['p'] = $val['province'];
                    $data['citylist'][$key]['c'][$key2]['n'] = $val2['city'];
                    $data['citylist'][$key]['c'][$key2]['a'][$key3]['s'] = $val3['country'];
                }
            }
        }
        $sql = "replace into xsrb_kehugrade_json(`json`)VALUE ('".json_encode($data,JSON_UNESCAPED_UNICODE)."')";
        M()->execute($sql);
        $retmsg = array(
            'resultcode'=>0,
            'resultmsg'=>'json保存成功'
        );
        $this->response($retmsg,'json');
    }

    //获取省市县对应的客户级别
    public function kehugrade($province,$city,$country){
        header("Access-Control-Allow-Origin:*");
        $sql_search = "select grade from xsrb_kehugrade where province='$province' and city='$city' and country='$country'";
        try{
            $result = M()->query($sql_search);
        }catch(\Exception $a){
            $this->response(retmsg(-1,null,"查询条件不正确!"),'json');
        }
        $retmsg = array(
            'resultcode'=>0,
            'resultmsg'=>'查询成功',
            'grade'=>$result[0]['grade'],
        );
        $this->response($retmsg,'json');
    }
}