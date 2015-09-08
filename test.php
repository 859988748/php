<?php
//请求GeoCoding接口，将地址名转换为经纬度。
//需要填写文件名变量 $ExcelFile_Name.
//需要修改excelReader读取行号和列号。
require_once 'excelReader.php';
require_once 'request.php';

$ExcelFile_Name = '食品TOP800后台表格(1).xls';
$file_url  = dirname(__FILE__)."/".$ExcelFile_Name;
$shopArray = phpReader($file_url);

$method = "get";
$nameNgeoCodeArrya = [];
$failedPartArray = [];
$mutiPartArray = [];
foreach ($shopArray as $key => $value) {
	# code...
	$requesturl = "https://maps.googleapis.com/maps/api/geocode/json?address=";
	$dictContent = $value ['content'];
	foreach ($dictContent as $shopName => $shopAddress) {
		# code...
		$requesturl .= urlencode($shopAddress);
	}
	$requesturl .= "&key=AIzaSyBsX6BtfpaX2VhQI31QTDOlGFn0H9nuZxQ&language=ja";
	$response = request($requesturl,$method);
	if (!empty($response)) {
		# code...
		$result = json_decode($response,true);
		$status = $result['status'];
		if ($status == 'OK') {
			# code...
			if (count($result['results']) == 1) {
				# code...
			$detailResult = $result['results'][0];
			$formatted_address = $detailResult['formatted_address'];
			$lat = $detailResult['geometry']['location']['lat'];
			$lng = $detailResult['geometry']['location']['lng'];
			$dict ['formatted_address'] = $formatted_address;
			$dict ['lat'] = $lat;
			$dict ['lng'] = $lng;
			$dict ['ourName'] = $shopName;
			$dict ['ourAddress'] = $shopAddress;
			$dict ['locaionInExcel'] = $value ['locaionInExcel'];
			$nameNgeoCodeArrya[] = $dict;
			unset($dict); 
			}else{
				$mutiResultsForOnePlace = [];
				foreach ($result['results'] as $index => $mutiResults) {
					# code...
					$multiFormatted_address = $mutiResults['formatted_address'];
					$multiLat = $mutiResults['geometry']['location']['lat'];
					$multiLng = $mutiResults['geometry']['location']['lng'];
					$multiDict ['formatted_address'] = $multiFormatted_address;
					$multiDict ['lat'] = $multiLat;
					$multiDict ['lng'] = $multiLng;
					$multiDict ['ourName'] = $shopName;
					$multiDict ['ourAddress'] = $shopAddress;
					$mutiResultsForOnePlace [] = $multiDict;
					unset($multiDict);
				}
				$tempDict ['ourName'] = $shopName;
				$tempDict ['ourAddress'] = $shopAddress;
				$tempDict ['results'] = $mutiResultsForOnePlace;
				$tempDict ['locaionInExcel'] = $value['locaionInExcel'];
				$mutiPartArray [] = $tempDict;
				unset($tempDict);
			}
 		}else{
 			$failedDict ['status'] = $status;
 			$failedDict ['ourName'] = $shopName;
 			$failedDict ['ourAddress'] = $shopAddress;
 			$failedDict ['locaionInExcel'] = $value['locaionInExcel'];
 			$failedPartArray [] = $failedDict;
 			unset($failedDict);
		}
	}else{
		// exit("fail exit");
		echo "\nno response\n".$shopName."--loctionInExcel:".$value['locaionInExcel'];
		echo "\n-------------------------------------\n";
	}
}

$jsonToSuccessFile = json_encode($nameNgeoCodeArrya,JSON_UNESCAPED_UNICODE);
$jsonToFailFile = json_encode($failedPartArray,JSON_UNESCAPED_UNICODE);
$jsonToMutiResultsFile = json_encode($mutiPartArray,JSON_UNESCAPED_UNICODE);

$successFileUrl = dirname(__FILE__)."/{$ExcelFile_Name}GeoCodingResult/successPart.json";
$failedFileUrl = dirname(__FILE__)."/{$ExcelFile_Name}GeoCodingResult/FailedPart.json";
$mutiPartFileUrl = dirname(__FILE__)."/${ExcelFile_Name}GeoCodingResult/multiResultsPart.json";

require_once 'writeFileToDisk.php';

writeToFile($successFileUrl,$jsonToSuccessFile);
writeToFile($failedFileUrl,$jsonToFailFile);
writeToFile($mutiPartFileUrl,$jsonToMutiResultsFile);

?>