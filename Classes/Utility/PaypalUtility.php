<?php

namespace Interfrog\Vinou\Utility;

class PaypalUtility implements \TYPO3\CMS\Core\SingletonInterface {

	public static $endpoint = [
		'sandbox' => 'https://api.sandbox.paypal.com',
		'live' => 'https://api.paypal.com'
	];

	/**
	* @param array $fields array to be converted into post string
	* @return string
	*/
	public static function createPostString($fields = array()) {
		$fields_string = '';
		foreach($fields as $key=>$value) { 
			$fields_string .= $key.'='.urlencode($value).'&';
		}
		return rtrim($fields_string,'&');
	}

	/**
	* @param string $clientId clientId of Paypal App
	* @param string $secret secret of Paypal App
	* @param string $mode mode of Paypal App
	* @return string
	*/
	public static function getPaypalToken($clientId,$secret,$mode = 'sandbox') {
		$header = [
			'Accept: application/json',
			'Accept-Language: en_US'
		];
		$postParams = [
			'grant_type' => 'client_credentials'
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$endpoint[$mode].'/v1/oauth2/token');
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $clientId.':'.$secret);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_POST,count($postParams));
		curl_setopt($ch,CURLOPT_POSTFIELDS,self::createPostString($postParams));
		$response = json_decode(curl_exec($ch));
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $response->access_token;
	}

	/**
	* @param array $params order details defined in paypal api
	* @param string $AuthToken paypal Token
	* @param string $mode mode of Paypal App
	* @return array
	*/
	public static function createPayment($params,$AuthToken,$mode = 'sandbox') {
		$header = [
			'Content-Type: application/json',
			'Authorization: Bearer '.$AuthToken
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$endpoint[$mode].'/v1/payments/payment');
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($params));
		$response = json_decode(curl_exec($ch));
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $response;
	}

	/**
	* @param string $payerId id of payer delivered in url
	* @param string $paymentId id of payment delivered in url
	* @param string $AuthToken paypal Token
	* @param string $mode mode of Paypal App
	* @return array
	*/
	public static function executePayment($payerId,$paymentId,$AuthToken,$mode = 'sandbox') {
		$header = [
			'Content-Type: application/json',
			'Authorization: Bearer '.$AuthToken
		];
		$jsonParams = [
			'payer_id' => $payerId
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$endpoint[$mode].'/v1/payments/payment/'.$paymentId.'/execute/');
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($jsonParams));
		$response = json_decode(curl_exec($ch));
		$info = curl_getinfo($ch);
		curl_close($ch);
		return $response;
	}
}