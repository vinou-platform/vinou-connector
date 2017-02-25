<?php
namespace Interfrog\Vinou\Utility;

class Images {

	CONST APIURL = 'https://api.vinou.de';

	public static function getExternalImage($url) {         
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

	public static function storeApiImage($imagesrc,$localFolder,$chstamp = NULL) {
		$fileName = array_values(array_slice(explode('/',$imagesrc), -1))[0];
		$localFile = $localFolder.$fileName;

		$chdate = new \DateTime($chstamp);
		$changeStamp = $chdate->getTimestamp();

		if(!file_exists($localFile)){
			$image = self::getExternalImage(self::APIURL.$imagesrc); 
			file_put_contents($localFile,$image);
		} else if (!is_null($chstamp) && $changeStamp > filemtime($localFile)) {
			$image = self::getExternalImage(self::APIURL.$imagesrc); 
			file_put_contents($localFile,$image);
		}
		return $fileName;
	}

}