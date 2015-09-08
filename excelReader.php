<?php
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
require_once 'Classes/PHPExcel/Reader/Excel5.php';

//	修改需要读的列。
$rowToReadDict = array('R' => 'S', 'V' => 'W','Z' => 'AA');
// 读取的行数范围
$rowRange = 42; //$objPHPExcel->getSheet(0)->getHighestRow();

function phpReader($file_url){
	$objReader=PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
	$objPHPExcel=$objReader->load($file_url);//$file_url即Excel文件的路径
	$sheet=$objPHPExcel->getSheet(0);//获取第一个工作表
	$highestRow=$rowRange;//$sheet->getHighestRow();//取得总行数
	$highestColumn=$sheet->getHighestColumn(); //取得总列数
	//循环读取excel文件,读取一条,插入一条
	// for($j=2;$j<=$highestRow;$j++){//从第一行开始读取数据
	//  $str='';
	//  for($k='A';$k<=$highestColumn;$k++){            //从A列读取数据
	//  //这种方法简单，但有不妥，以'\\'合并为数组，再分割\\为字段值插入到数据库,实测在excel中，如果某单元格的值包含了\\导入的数据会为空        
	//   $str.=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'\\';//读取单元格
	//  }
	//  //explode:函数把字符串分割为数组。
	//  $strs=explode("\\",$str);
	$result = [];
	for ($row=2; $row < $highestRow; $row++) { 
		# code...
		foreach ($rowToReadDict as $name => $address) {
			# code...
			$rawShopName = $objPHPExcel->getActiveSheet()->getCell("$name$row")->getValue();
			$rawShopAddress = $objPHPExcel->getActiveSheet()->getCell("$address$row")->getValue();
			$shopName = ($rawShopName instanceof PHPExcel_RichText)? $rawShopName->getPlainText() : $rawShopName;
			$shopAddress = ($rawShopAddress instanceof PHPExcel_RichText)? $rawShopAddress->getPlainText() : $rawShopAddress;;
			$dict ['content'] = array($shopName => $shopAddress); 
			$dict ['locaionInExcel'] = $name.":".$row;
			$result [] = $dict;
			unset($dict);
		}
	}
	// unlink($file_url); //删除excel文件

	if (!empty($result)) {
		# code...
		return $result;
	}else{
		exit("no result!!!");
	}
}

// $file_url = dirname(__FILE__)."/新宿top50 商店汇总.xls";

// print_r(phpReader($file_url));


?>