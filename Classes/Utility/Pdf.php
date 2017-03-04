<?php
namespace Interfrog\Vinou\Utility;

class Pdf {

	CONST APIURL = 'https://api.vinou.de';

	public static function getExternalPDF($src,$target) {       
	    set_time_limit(0);
		$fp = fopen ($target, 'w+');
		$process = curl_init(rawurlencode($src));
		curl_setopt($process, CURLOPT_TIMEOUT, 50);
		curl_setopt($process, CURLOPT_FILE, $fp);
		curl_setopt($process, CURLOPT_FOLLOWLOCATION, true);
		$return = curl_exec($process);
		curl_close($process);
		return $return;
	}

	public static function storeApiPDF($src,$localFolder,$prefix = '',$chstamp = NULL) {
		$fileName = array_values(array_slice(explode('/',$src), -1))[0];
		$convertedFileName = self::convertFileName($prefix.$fileName);
		$localFile = $localFolder.$convertedFileName;

		$chdate = new \DateTime($chstamp);
		$changeStamp = $chdate->getTimestamp();

		if(!file_exists($localFile)){
			$result = self::getExternalPDF(self::APIURL.$src,$localFile);
		} else if (!is_null($chstamp) && $changeStamp > filemtime($localFile)) {
			$result = self::getExternalPDF(self::APIURL.$src,$localFile);
		}
		return $convertedFileName;
	}

	public static function convertFileName($fileName) {
		$fileName = strtolower($fileName);
		$fileName = str_replace(' ', '_', $fileName);
		$fileName = str_replace("ä", "ae", $fileName);
		$fileName = str_replace("ü", "ue", $fileName);
		$fileName = str_replace("ö", "oe", $fileName);
		$fileName = str_replace("Ä", "Ae", $fileName);
		$fileName = str_replace("Ü", "Ue", $fileName);
		$fileName = str_replace("Ö", "Oe", $fileName);
		$fileName = str_replace("ß", "ss", $fileName);
		$fileName = str_replace("´", "", $fileName);
		return $fileName;
	}

}