<?php
	
	$dbFileUrl = dirname(__FILE__)."/../Metro.sqlite";
	$FileName = '食品TOP800后台表格(1).xls';
	$shopFileUrl = dirname(__FILE__)."/{$FileName}GeoCodingResult/successPart.json";
	$stationArray = [];
	$shopArray = [];
	$map = [];

	function connectToSqlite($dbFileUrl){
		$db = new SQLite3($dbFileUrl);
		if ($db) {
			# code...
			return $db;
		}else{
			die("failed to connect DataBase");
		};
	}

	function getStations(){
		global $dbFileUrl;
		$db = connectToSqlite($dbFileUrl);
		$sql = 'select stationName, stationLat, stationLong from stations';
		$results = $db->query($sql);
		global $stationArray;
		while ( $result = $results->fetchArray(SQLITE3_ASSOC)) {
			# code...
			$stationArray [] = $result;
		}
		$db->close();
	}

	function distanceBetweenTwoPoints($P1lat, $P1lon, $P2lat, $P2lon){
		$sideA = abs($P1lon - $P2lon);
		$sideB = abs($P1lat - $P2lat);
		return sqrt(($sideA * $sideA) + ($sideB * $sideB));
	}

	function getShops($shopFileUrl){
		global $shopArray;
		$json_string = file_get_contents($shopFileUrl);
		$shopArray = json_decode($json_string, true);
	}

	getStations();
	getShops($shopFileUrl);
	foreach ($shopArray as $key => $shop) {
		# code...
		$shopName = $shop['ourName'];
		$shopLat = $shop['lat'];
		$shopLng = $shop['lng'];
		$distance = PHP_INT_MAX;
		$nearestStation;
		foreach ($stationArray as $key => $station) {
					# code...
			$stationLat = $station['stationLat'];
			$stationLng = $station['stationLong'];
			$calculatedDistance = distanceBetweenTwoPoints($shopLat,$shopLng,$stationLat,$stationLng);
			if ($distance >= $calculatedDistance) {
				# code...
				$distance = $calculatedDistance;
				$nearestStation = $station;
				// echo $distance.PHP_EOL;
			}
		}
		$shop['station'] = $nearestStation;
		$shop['distance'] = $distance;
		$map [] = $shop;
	}

	require_once 'writeFileToDisk.php';
	$resultFileUrl = dirname(__FILE__)."/{$FileName}NearestStaion/successPart.json";
	writeToFile($resultFileUrl,json_encode($map,JSON_UNESCAPED_UNICODE));
?>