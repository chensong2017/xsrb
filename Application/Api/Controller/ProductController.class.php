<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/7
 * Time: 14:18
 */

namespace Api\Controller;

use Think\Controller\RestController;

class ProductController extends RestController{
    /**
     * @param $deptId
     * @param string $searchString
     * @param int $page
     * @param int $pageSize
     */
    public function getProductList($deptId,$searchString='',$page=1,$pageSize=10){
        $where['dept']=$deptId;
        $where['date']=date('Y-m');
        $condition="1=1";
        if(!empty($searchString)){
            //根据searchString查询产品属性名称
            $attr=$this->getAttrArrayByString($searchString);
           if(!empty($attr)){
               $condition='';
               foreach ($attr as $v){
                   $condition.=" $v like'%$searchString%' or";
               }
               $condition=substr($condition,0,strlen($condition)-2);
           }
           else{
               $this->response(retmsg(0,array('total'=>0,'list'=>array())),'json');
           }

        }
        $list=M('spzmxqc')->where($where)->where($condition)->page($page,$pageSize)->select();
        $total=M('spzmxqc')->where($where)->where($condition)->count();
        $this->response(retmsg(0,array('total'=>$total,'list'=>$list)),'json');
    }

    public function getPrice(){
        $where=json_decode(file_get_contents('php://input'),true);
        $price=M('spzmxqc','')->field('benyuedj as price')->where($where)->select();
        $this->response(retmsg(0,array('price'=>$price[0]['price'])),'json');
    }
    /**
     * 根据string查询所属产品的属性eg:60 属性为dalei menkuang
     * @param string $string
     * @return array
     */
    public function getAttrArrayByString($string){
        $product=$this->getStandardProduct();
        $attr=array();//返回的属性数组
        foreach ($product as $key=>$value){
            foreach ($value as $v){
                if(strpos($v,$string)!==false){
                    array_push($attr,$key);
                    break;
                }
            }
        }
        return $attr;
    }

    /**
     * 获取标准产品信息
     * @return array|mixed ["attr1"=>["v1","c2"],"attr2"=>["v1","c2"],]
     */
    public function getStandardProduct(){
        $redis = new \Redis();
        $redis->connect(C('REDIS_URL'),"6379");
        $redis->auth(C('REDIS_PWD'));
        $list=json_decode($redis->get('standardProduct'),true);
        if(!empty($list)){
            return $list;
        }
        $product=M('spzmx_list','')->select();//产品标准信息列表
        //将产品标准列表转换成["attr1"=>["v1","c2"],"attr2"=>["v1","c2"],]的形式
        $list=array();//转化后的数组
        foreach ($product as $key=>$row){
            foreach ($row as $k=>$v){
                if($k!=='id'&&!empty($v)){
                    $list[$k][]=$v;
                }
            }
        }
        //$redis->auth(C('REDIS_PWD'));
        $redis->set("standardProduct",json_encode($list));
        return $list;
    }

    public function test($string){
        $data=$this->getAttrArrayByString($string);
        print_r($data);
    }
}
