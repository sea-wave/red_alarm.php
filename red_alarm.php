<?php
set_time_limit(0);
date_default_timezone_set('Asia/Jerusalem');

$GLOBALS['cities'] = array();
include_once('cities.inc.php');


function getCityName($AreaCode='')
{
	if (empty($GLOBALS['cities'][$AreaCode])) {
		return "\n";
	}
	else {
		return ', ערים:'."\n\t" . implode("\n\t", $GLOBALS['cities'][$AreaCode]) . "\n\n";
	}
}


function displayNotification($msg='')
{
	if (empty($msg)) return;

	$title = 'אזעקה';
	$cmd = '/usr/bin/notify-send ' . $title . ' '.escapeshellarg($msg) . ' -t 15000 2>&1';
	$fp = popen($cmd, "r");
/*
	while(!feof($fp)) {
		echo fread($fp, 1024);
		flush();
	}
*/
	fclose($fp);
}


function fetchJSON()
{
	$JSON = file_get_contents('http://www.oref.org.il/WarningMessages/alerts.json');
	return mb_convert_encoding($JSON , 'UTF-8' , 'UTF-16');
}

// prints alarms of rocket attacks on Israel using php
function CheckOrefData()
{
	$sOutFrmt = 'מרחב: %s, קוד מרחב: %s %s';
	$arrOptions = getopt("f:n");
	$bDispNotifications = (isset($arrOptions['n']));

	while (true) {
		$JSON = fetchJSON();

		// Example output:
		//$JSON = '{"id" : "1405853194651", "title" : "פיקוד העורף התרעה במרחב ", "data" : ["שפלה 175","שפלה 119", "עוטף עזה 230"]}';

		if (empty($JSON)) continue;

		$arr = json_decode($JSON, true /* returned objects will be converted into associative arrays */);

		if (!empty($arr['data'])) {

			$buf = date('r') . "\n";

			$iTotal = count($arr['data']);
			for ($i=0; $i<$iTotal; $i++) {
				preg_match('/([\p{Hebrew}|\s]+).*?([\d]+)$/u', $arr['data'][$i], $arrMatches);
				if (!empty($arrMatches) && (count($arrMatches) === 3)) {
					$AreaCode = $arrMatches[2];
					$city = getCityName($AreaCode);
					$buf .= "\t" . sprintf($sOutFrmt ,trim($arrMatches[1]),$AreaCode, $city)."\n";
				}
			}
			$buf .= "\n";
			echo $buf;
			flush();
			if ($bDispNotifications) {
				displayNotification($buf);
			}
		}

		sleep(5);
	}

}

CheckOrefData();
