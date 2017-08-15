<?php
namespace Api\Controller;
use Think\Controller\RestController;
define("EARTH_RADIUS","6378.138");
class DepartmentController extends RestController{
    
    public function getDeptsByPostion($longitude,$latitude,$range=100){
        $earth_radius=EARTH_RADIUS;//地球赤道半径
        //查询100公里以内的经营部信息
         $sql="SELECT
            	id,dname,office_name,longitude,latitude
            FROM
            	xsrb_department
            WHERE
            	ROUND(
            		$earth_radius * 2 * ASIN(
            			SQRT(
            				POW(
            					SIN(
            						(
            							$latitude * PI() / 180 - latitude * PI() / 180
            						) / 2
            					),
            					2
            				) + COS($latitude * PI() / 180) * COS(latitude * PI() / 180) * POW(
            					SIN(
            						(
            							$longitude * PI() / 180 - $longitude * PI() / 180
            						) / 2
            					),
            					2
            				)
            			)
            		)
            	) < $range ";
        $depts=M()->query($sql);
        $this->response(retmsg(0,$depts),'json');
    }
    
    public function getDeptDetail($deptId){
        $sql="select * from xsrb_department where id=$deptId";
        $data=M()->query($sql);
        $this->response(retmsg(0,$data[0]),'json');
    }
    
    public function getDeptProducts($deptId,$page=1,$pageSize=10,$orderBy='id',$orderType='desc'){
        $today=date('Y-m-d');
        $month=date('Y-m');
        $firstDay=date('Y-m-01');
        $offset=($page-1)*$pageSize;
        //查询总产品条数
        $sql="select count(*) as total  
        from spzmxqc t1 where dept=$deptId and date='$month' and type=1 and (qichusl+ifnull(
					(select (sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc))
						from spzmx where zhizaobm=t1.zhizaobm and  dalei=t1.dalei and feibiao=t1.feibiao and banhou=t1.banhou
						and guige=t1.guige and biaomianyq=t1.biaomianyq and menkuang=t1.menkuang and huase=t1.huase and suoju=t1.suoju 
						and kaixiang=t1.kaixiang and qita=t1.qita and dept=$deptId and date between '$firstDay' and '$today' and type=1)
				,0))>=0";
        $total=M()->query($sql);
        $total=$total[0]['total']; 
        
        $sql="select zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,
        qita,benyuedj as price,qichusl+ifnull(
					(select (sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc))
						from spzmx where zhizaobm=t1.zhizaobm and  dalei=t1.dalei and feibiao=t1.feibiao and banhou=t1.banhou
						and guige=t1.guige and biaomianyq=t1.biaomianyq and menkuang=t1.menkuang and huase=t1.huase and suoju=t1.suoju 
						and kaixiang=t1.kaixiang and qita=t1.qita and dept=$deptId and date between '$firstDay' and '$today' and type=1)
				,0) as storage  
        from spzmxqc t1 where dept=$deptId and date='$month' and type=1 and (qichusl+ifnull(
					(select (sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc))
						from spzmx where zhizaobm=t1.zhizaobm and  dalei=t1.dalei and feibiao=t1.feibiao and banhou=t1.banhou
						and guige=t1.guige and biaomianyq=t1.biaomianyq and menkuang=t1.menkuang and huase=t1.huase and suoju=t1.suoju 
						and kaixiang=t1.kaixiang and qita=t1.qita and dept=$deptId and date between '$firstDay' and '$today' and type=1)
				,0))>=0  ";
        $sql.="order by $orderBy $orderType ,id ";
        $sql.=" limit $offset,$pageSize";
        $qc=M()->query($sql);
        $this->response(retmsg(0,array("total"=>$total,"list"=>$qc)),'json');
    }
    
    //操蛋的php操蛋的慢
    private function order(&$array,$sortBy,$sortType){
        $count=count($array);
        for($i=0;$i<$count;$i++){
            for($j=$count-1;$j>$i;$j--){
                if($array[$j][$sortBy]>$array[$j-1][$sortBy]&&$sortType=='desc'){
                     $temp=$array[$j];
                    $array[$j]=$array[$j-1];
                    $array[$j-1]=$temp; 
                }
            }
        }
    }
    
    public function getProductStorage($productId){
        $today=date('Y-m-d');
        $firstDay=date('Y-m-01');
        $sql="select zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,dept,qichusl+
            ifnull(
                (   
                 select (sum(dbsr+zcsr+phsr+shsh+qtsr-zfxszc-kfxszc-dbzc-zczc-phzc-shzc-qtzc)) as jiecun 
                 from spzmx where dept=t1.dept  and dalei=t1.dalei and feibiao=t1.feibiao and 
                 banhou=t1.banhou and guige=t1.guige and biaomianyq=t1.biaomianyq and huase=t1.huase 
                 and suoju=t1.suoju and kaixiang=t1.kaixiang and qita=t1.qita and type=1 and date between '$firstDay' and '$today' 
                )
            ,0) as storage from spzmxqc t1 where id=$productId";
        $storage=M()->query($sql);
        $this->response(retmsg(0,array("storage"=>$storage[0]['storage'])),'json');
    }
    
    public function getProductDetail($productId){
        $sql="select zhizaobm,dalei,feibiao,banhou,guige,biaomianyq,menkuang,huase,suoju,kaixiang,qita,benyuedj as price,dname,office_name,dept as dept_id from spzmxqc t1,xsrb_department t2 where t1.dept=t2.id and t1.id=$productId ";
        $ret=M()->query($sql);
        $this->response(retmsg(0,$ret),'json');
    }
    
}


?>