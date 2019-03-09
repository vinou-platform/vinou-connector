<?php
namespace Vinou\VinouConnector\Utility;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

/**
* Api
*/

class Api {

	protected $authData = [];
	protected $apiUrl = "https://api.vinou.de/service/";
	public $logindata = [];
	public $log = [];

	public function __construct($token = '',$authid = '',$dev = false) {
		$this->authData['token'] = $token;
		if ($dev)
			$this->authData['authid'] = $authid;

		$this->logindata = $this->login(false);
		// if (isset($GLOBALS['TSFE'])) {
		// 	$this->logindata = $this->readSessionData('vinouAuth');
		// 	$this->validateLogin();
		// } else {
		// 	$this->logindata = $this->login(false);
		// }
	}


	public function validateLogin(){

		if(!isset($this->logindata['token']) && !isset($this->logindata['refreshToken']))  {
			$this->login();
		} else {
			$ch = curl_init($this->apiUrl.'check/login');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER,
				[
					'Content-Type: application/json',
					'Origin: '.$_SERVER['SERVER_NAME'],
					'Authorization: Bearer '.$this->logindata['token']
				]
			);
			$result = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$requestinfo = curl_getinfo($ch);
			array_push($this->log,'validate existing token');
			if($httpCode != 200) {
				array_push($this->log,[
					'validateresult' => $result,
					'token expired'
				]);
				$this->login();
			}
			return true;
		}
    }

	//request a fresh token based on authid and authtoken
	public function login($cached = true)
	{
		$data_string = json_encode($this->authData);
        $ch = curl_init($this->apiUrl.'login');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
				'Origin: '.$_SERVER['SERVER_NAME']
			]
		);

		$result = json_decode(curl_exec($ch),true);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		array_push($this->log,'login and write session');
		if(curl_errno($ch) == 0 && isset($result['token'], $result['refreshToken']))
		{
			curl_close($ch);
			if ($cached) {
				$this->writeSessionData('vinouAuth',$result);
			}
			return $result;
		}
		return false;
	}

	private function curlApiRoute($route, $data = [])
	{
		$data_string = json_encode($data);
		$ch = curl_init($this->apiUrl.$route);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
				'Origin: '.$_SERVER['SERVER_NAME'],
				'Authorization: Bearer '.$this->logindata['token']
			]
		);
		$result = curl_exec($ch);
		$requestinfo = curl_getinfo($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		switch ($httpCode) {
			case 200:
				curl_close($ch);
				return json_decode($result, true);
				break;
			case 401:
				//Debug::var_dump('unauthorized');
				break;
			default:
				//Debug::var_dump('recreate token');
				break;
		}
		return false;
	}

	public function getWine($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('wines/get',$postData);
		return $result;
	}

	public function getWinesByCategory($postData) {
		$result = $this->curlApiRoute('wines/getByCategory',$postData);
		return $result;
	}

	public function getWinesByType($type) {
		$postData = ['type' => $type];
		$result = $this->curlApiRoute('wines/getByType',$postData);
		return $result['wines'];
	}

	public function getWinesAll($postData = NULL) {
		$postData['language'] = $GLOBALS['TSFE']->sys_language_isocode;
		$result = $this->curlApiRoute('wines/getAll',$postData);
		return $result;
	}

	public function getExpertise($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('wines/getExpertise',$postData);
		return $result['pdf'];
	}

	public function getCategory($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('categories/get',$postData);
		return $result;
	}

	public function getCategoryWithWines($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('categories/getWithWines',$postData);
		return $result;
	}

	public function getCategoriesAll() {
		$result = $this->curlApiRoute('categories/getAll');
		return $result['categories'];
	}

	public function getProductsAll($postData = NULL) {
		$result = $this->curlApiRoute('products/getAll',$postData);
		return $result;
	}

	public function getProduct($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('products/get',$postData);
		return $result;
	}

	public function getClientLogin() {
		$postData = [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'userAgent' => $_SERVER['HTTP_USER_AGENT']
        ];

        if (self::isDev())
        	$postData['ip'] = $this->fetchLokalIP();

		$result = $this->curlApiRoute('clients/login',$postData);
		if (isset($result['token']) && isset($result['refreshToken'])) {
			unset($result['id']);
			unset($result['info']);
			return $result;
		}
		return false;
	}

	public function getBasket($uuid) {
		$postData = ['uuid' => $uuid];
		$result = $this->curlApiRoute('baskets/get',$postData);
		return isset($result['data']) ? $result['data'] : false;
	}

	public function addOrder($order) {
		$postData = ['data' => $order];
		$result = $this->curlApiRoute('orders/add',$postData);
		return $result;
	}

	public function findPackage($type,$count) {
		$postData = [
			'type' => $type,
			$type => $count
		];
		$result = $this->curlApiRoute('packaging/find',$postData);
		return $result['data'];
	}

	public function fetchLokalIP(){
		$result = $this->curlApiRoute('check/userinfo');
		return $result['ip'];
	}

	public function readSessionData($key) {
		if ($GLOBALS['TSFE']->loginUser) {
		    return $GLOBALS['TSFE']->fe_user->getKey('user', $key);
		} else {
		    return $GLOBALS['TSFE']->fe_user->getKey('ses', $key);
		}
	}

	public function writeSessionData($key,$data) {
		if ($GLOBALS['TSFE']->loginUser) {
			$GLOBALS['TSFE']->fe_user->setKey('user', $key, $data);
		} else {
			$GLOBALS['TSFE']->fe_user->setKey('ses', $key, $data);
		}
		return $GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	public function removeSessionData($key) {
		unset($GLOBALS['TSFE']->fe_user->sesData[$key]);
		return $GLOBALS['TSFE']->fe_user->storeSessionData();
	}

	public static function isDev() {
		return substr($_SERVER["SERVER_NAME"],-5) === '.frog';
	}
}