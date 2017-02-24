<?php
namespace Interfrog\Vinou\Utility;


/**
* Translation
*/
class Translation {

	public static function getRegion($id,$llPath,$countryCode = 'de'){
		$wineregions = json_decode(file_get_contents($llPath.'wineregions.json'),true);
		return $wineregions[$countryCode][$id];
	}

	public static function getType($type,$llPath,$countryCode = 'de'){
		$winetypes = json_decode(file_get_contents($llPath.'winetypes.json'),true);
		return $winetypes[$countryCode][$type];
	}

	public static function getTaste($id,$llPath,$countryCode = 'de'){
		$tastes = json_decode(file_get_contents($llPath.'tastes.json'),true);
		return $tastes[$countryCode][$id];
	}
	
	public static function getGrapeType($id,$llPath){
		$grapetypes = array();
		foreach (json_decode(file_get_contents($llPath.'grapetypes.json'),true) as $number => $grapetype) {
			$grapetypes[$number] = $grapetype['name'];
		}
		return $grapetypes[$id];
	}
}