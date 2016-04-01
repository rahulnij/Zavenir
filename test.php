<?php
include 'PHPExcel/Classes/PHPExcel.php';
$objPHPExcel = new PHPExcel();

$objPHPExcel->setActiveSheetIndex(0); 
$rowCount = 1; 
$table = 'ibdclients';

$conn = mysql_connect('127.0.0.1', 'root', '');
mysql_select_db("ValueCallz",$conn);

$result = mysql_query("select * from $table limit 10",$conn);
while($row = mysql_fetch_array($result)){ 
    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $row['id']); 
    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $row['clientname']); 
    $rowCount++; 
} 

$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
$objWriter->save('MyExcelFile.xlsx'); 

exit;
?>
