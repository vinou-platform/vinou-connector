<?php
namespace Interfrog\Vinou\Utility;


/**
* Translation
*/
class Translation {

	public static function getRegion($id,$llPath,$countryCode = 'de'){
		$wineregions = (array)json_decode(file_get_contents($llPath.'wineregions.json'));
		return $wineregions[$countryCode][$id];
	}

	public static function getType($llPath){
		$winetypes = json_decode(file_get_contents($llPath.'winetypes.json'));
	}
	
	public static function getGrapeType($llPath){
		$grapetypes = array();
		foreach (json_decode(file_get_contents($llPath.'grapetypes.json')) as $id => $grapetype) {
			$grapetypes[$id] = $grapetype->name;
		}
	}
}