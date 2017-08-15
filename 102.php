<?php 
echo 66666;
return;
//引入PHPExcel库文件（路径根据自己情况）
require_once(__DIR__ .'/Application/Common/Classes/PHPExcel.php') ;
require_once(__DIR__ . '/Application/Common/Classes/PHPExcel/Writer/Excel2007.php');
//echo __DIR__ .'\excel';return;
//创建对象
$excel = new \PHPExcel();

//Excel表格式,这里简略写了8列
$letter = array('B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R');
//表头数组
$tableheader = array('分类','位置','材料类型','项目名称','单位','计价方式','标配工程量','实际工程量','增加工程量','单价','增加单价','减少单价','升级单价','增加金额=增加工程量*单价','主材说明及工艺备注');
//填充表头信息
for($i = 0;$i < count($tableheader);$i++) {
//echo $tableheader[$i];
$excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
//水平居中===垂直居中  
$excel->setActiveSheetIndex(0)->getStyle("$letter[$i]1","$tableheader[$i]")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
$excel->setActiveSheetIndex(0)->getStyle("$letter[$i]1","$tableheader[$i]")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); 
}
$excel->getActiveSheet()->freezePane('A2');
//表格数组
$data = array(
array('1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','入户门门套基层修正'),
array('1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','入户门门套基层修正'),
array('1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','入户门门套基层修正'),
array('1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','100','1','入户门门套基层修正','男','20','测试门门套基层修正'));
//填充表格信;
//count($data)
for ($i = 0;$i <= count($data)+1;$i++) {
$j = 0;
foreach ($data[$i - 2] as $key=>$value) {
$excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
$excel->setActiveSheetIndex(0)->getStyle("$letter[$j]$i","$value")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);  
$excel->setActiveSheetIndex(0)->getStyle("$letter[$j]$i","$value")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); 
$j++;
}
}
ob_clean();//关键
//flush();//关键
//创建Excel输入对象
$write = new \PHPExcel_Writer_Excel2007($excel);
//echo __DIR__ .'/excel\rand.xls'; return;
header("Pragma: public");
header("Expires: 0");
header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
header("Content-Type:application/force-download");
header("Content-Type:application/vnd.ms-execl;charset=UTF-8");
header("Content-Type:application/octet-stream");
header("Content-Type:application/download");;
header('Content-Disposition:attachment;filename="装饰设计预算表.xls"');
header("Content-Transfer-Encoding:binary"); 
//echo dirname($_FILES['userfile']['tmp_name']);
//echo __DIR__ .'\excel';return;

$write->save(__DIR__ .'/excel/save1111.xls');
//$write->save('E:\\sxrb/装饰设计预算表.xls');
?>
