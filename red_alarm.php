<?php
set_time_limit(0);
date_default_timezone_set('Asia/Jerusalem');


function fetchJSON()
{
	$JSON = file_get_contents('http://www.oref.org.il/WarningMessages/alerts.json');
	return mb_convert_encoding($JSON , 'UTF-8' , 'UTF-16');
}

// prints alarms of rocket attacks on Israel using php
function CheckOrefData()
{
	$sOutFrmt = 'מרחב: %s, קוד מרחב: %s';


	// Example output:
	//$JSON = '{"id" : "1405853194651", "title" : "פיקוד העורף התרעה במרחב ", "data" : ["שפלה 175","שפלה 174", " עוטף עזה 666"]}';

	while (true) {
		$JSON = fetchJSON();
		if (empty($JSON)) continue;

		$arr = json_decode($JSON, true /* returned objects will be converted into associative arrays */);

		if (!empty($arr['data'])) {

			echo date('r') . "\n";

			$iTotal = count($arr['data']);
			for ($i=0; $i<$iTotal; $i++) {
				preg_match('/([\p{Hebrew}|\s]+).*?([\d]+)$/u', $arr['data'][$i], $arrMatches);
				if (!empty($arrMatches) && (count($arrMatches) === 3)) { 
					echo "\t" . sprintf($sOutFrmt ,trim($arrMatches[1]),$arrMatches[2])."\n";
				}
			}
			echo "\n";
			flush();
			
		}
		sleep(5);
	}

}

CheckOrefData();