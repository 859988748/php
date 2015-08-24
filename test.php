<?php
//请求GeoCoding接口，将地址名转换为经纬度。
require_once 'excelReader.php';
require_once 'request.php';

$file_url  = dirname(__FILE__)."/新宿top50 商店汇总.xls";
$shopArray = phpReader($file_url);

$method = "get";
$nameNgeoCodeArrya = [];
$failedPartArray = [];
$mutiPartArray = [];
foreach ($shopArray as $key => $value) {
	# code...
	$requesturl = "https://maps.googleapis.com/maps/api/geocode/json?address=";
	foreach ($value as $shopName => $shopAddress) {
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
				$mutiPartArray [] = $tempDict;
				unset($tempDict);
			}
 		}else{
 			$failedDict ['status'] = $status;
 			$failedDict ['ourName'] = $shopName;
 			$failedDict ['ourAddress'] = $shopAddress;
 			$failedPartArray [] = $failedDict;
 			unset($failedDict);
		}
	}else{
		// exit("fail exit");
		echo "\nno response\n".$shopName;
		echo "\n-------------------------------------\n";
	}
}

$jsonToSuccessFile = json_encode($nameNgeoCodeArrya,JSON_UNESCAPED_UNICODE);
$jsonToFailFile = json_encode($failedPartArray,JSON_UNESCAPED_UNICODE);
$jsonToMutiResultsFile = json_encode($mutiPartArray,JSON_UNESCAPED_UNICODE);

$successFileUrl = dirname(__FILE__)."/GeoCodingResult/successPart.json";
$failedFileUrl = dirname(__FILE__)."/GeoCodingResult/FailedPart.json";
$mutiPartFileUrl = dirname(__FILE__)."/GeoCodingResult/multiResultsPart.json";

function writeToFile($newFileName,$newFileContent){
	$folder = dirname($newFileName);
	if (!file_exists($folder)) {
		# code...
		mkdir($folder,0777,true);
	}
	if(file_put_contents($newFileName,$newFileContent)!=false){
    	echo "File created (".basename($newFileName).")";
	}else{
    	echo "Cannot create file (".basename($newFileName).")";
	}

}

writeToFile($successFileUrl,$jsonToSuccessFile);
writeToFile($failedFileUrl,$jsonToFailFile);
writeToFile($mutiPartFileUrl,$jsonToMutiResultsFile);

?>