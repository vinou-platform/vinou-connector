<?php
namespace Interfrog\Vinou\Utility;

class Pdf {

	public static function getExternalPDF($url) {         
	    $headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';              
	    $headers[] = 'Connection: Keep-Alive';         
	    $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';         
	    $user_agent = 'php';         
	    $process = curl_init($url);         
	    curl_setopt($process, CURLOPT_HTTPHEADER, $headers);         
	    curl_setopt($process, CURLOPT_HEADER, 0);         
	    curl_setopt($process, CURLOPT_USERAGENT, $user_agent); //check here         
	    curl_setopt($process, CURLOPT_TIMEOUT, 30);         
	    curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);         
	    curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);         
	    $return = curl_exec($process);         
	    curl_close($process);         
	    return $return;     
	}

	public static function storeApiPDF($src,$localFolder,$prefix = '') {
		$fileName = array_values(array_slice(explode('/',$src), -1))[0];
		$convertedFileName = self::convertFileName($prefix.$fileName);
		$localFile = $localFolder.$convertedFileName;
		if(!file_exists($localFile)){
			$pdf = self::getExternalPDF('https://api.vinou.de'.$src); 
			file_put_contents($localFile,$pdf);
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