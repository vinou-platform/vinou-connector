<?php
namespace Vinou\VinouConnector\Utility;

class Images {

	CONST APIURL = 'https://api.vinou.de';

	public static function getExternalImage($url,$targetFile) {         
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
	    $rawImage = curl_exec($process);
	    $httpStatus = curl_getinfo($process, CURLINFO_HTTP_CODE);
	    curl_close($process);  
	    file_put_contents($targetFile,$rawImage);       
	    return $httpStatus;     
	}

	public static function storeApiImage($imagesrc,$localFolder,$chstamp = NULL) {
		$fileName = array_values(array_slice(explode('/',$imagesrc), -1))[0];
		$localFile = $localFolder.$fileName;
		$exists = TRUE;

		$chdate = new \DateTime($chstamp);
		$changeStamp = $chdate->getTimestamp();
		$returnArr = [
			'fileName' => $fileName,
			'fileFetched' => FALSE,
			'requestStatus' => 'no request done'
		];

		if(!file_exists($localFile)){
			$returnArr['requestStatus'] = self::getExternalImage(self::APIURL.$imagesrc,$localFile);
			$returnArr['fileFetched'] = TRUE;
		} else if (!is_null($chstamp) && $changeStamp > filemtime($localFile)) {
			$returnArr['requestStatus'] = self::getExternalImage(self::APIURL.$imagesrc,$localFile);
			$returnArr['fileFetched'] = TRUE;
		}

		return $returnArr;
	}

}