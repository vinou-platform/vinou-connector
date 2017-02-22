<?php
namespace Interfrog\Vinou\Utility;

class Images {

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

	public static function cacheExternalImage($imagesrc,$width = 300) {
		$fileName = array_values(array_slice(explode('/',$imagesrc), -1))[0];
		$localsrc = $_SERVER["DOCUMENT_ROOT"].'/Cache/ApiImages/Source/'.$fileName;
		$resizesrc = $_SERVER["DOCUMENT_ROOT"].'/Cache/ApiImages/Resized/'.$width.'/'.$fileName;
		if(!file_exists('./'.$localsrc)){
			$image = self::getExternalImage('https://api.vinou.de'.$imagesrc); 
			file_put_contents('./'.$localsrc,$image);
		}
		if(!file_exists('./'.$resizesrc)){
			$image = new \Eventviva\ImageResize('./'.$localsrc);
			$image->resizeToWidth($width);
			$image->save('./'.$resizesrc);
		}
		return $resizesrc;
	}

}