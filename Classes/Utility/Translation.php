<?php
namespace Interfrog\Vinou\Utility;


/**
* Translation
*/
class Translation {

	public $regions = [];
	public $winetypes = [];
	public $tastes = [];
	public $grapetypes = [];
	protected $countryCode = 'de';
	protected $llPath = 'Resources/Private/Language/';
	protected $extKey = 'vinou';

	public function __construct($countryCode = NULL) {
		$this->llPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey).$this->llPath;
		is_null($countryCode) ? $this->init($this->countryCode) : $this->init($countryCode);
	}

	private function init($countryCode) {
		$allwineregions = json_decode(file_get_contents($this->llPath.'wineregions.json'),true);
		$this->regions = $allwineregions[$countryCode];
		$allwinetypes = json_decode(file_get_contents($this->llPath.'winetypes.json'),true);
		$this->winetypes = $allwinetypes[$countryCode];
		$alltastes = json_decode(file_get_contents($this->llPath.'tastes.json'),true);
		$this->tastes = $alltastes[$countryCode];

		$grapetypes = array();
		foreach (json_decode(file_get_contents($this->llPath.'grapetypes.json'),true) as $id => $grapetype) {
			$grapetypes[$id] = $grapetype['name'];
		}
		$this->grapetypes = $grapetypes;
		
	}

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