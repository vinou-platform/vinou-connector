<?php
namespace Interfrog\Vinou\Utility;

/**
* Api
*/

class Api {

	protected $authData = [];
	protected $apiUrl = "https://api.vinou.de";

	public function __construct($token = '',$authid = '',$dev = false) {
		$this->authData['token'] = $token;
		if ($dev) {
			$this->authData['authid'] = $authid;
		}
	}

	private function curlApiRoute($route, $data = []) {
		$header = array('Origin: '.$_SERVER['SERVER_NAME']);
        $data_string = json_encode(array_merge($this->authData,$data));                                                                                                                                                    
        $ch = curl_init($this->apiUrl.$route);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data_string),
            'Origin: '.$_SERVER['SERVER_NAME']                                                                       
        ));                                                                                                                   
        $result = curl_exec($ch);
    	return json_decode($result,true);
	}

	public function getWine($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('/service/wines/get',$postData);
		return $result;
	}

	public function getWinesByCategory($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('/service/wines/getByCategory',$postData);
		return $result;
	}

	public function getWinesByType($type) {
		$postData = ['type' => $type];
		$result = $this->curlApiRoute('/service/wines/getByType',$postData);
		return $result['wines'];
	}

	public function getWinesAll() {
		$result = $this->curlApiRoute('/service/wines/getAll');
		return $result['wines'];
	}

	public function getCategory($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('/service/categories/get',$postData);
		return $result;
	}

	public function getCategoryWithWines($id) {
		$postData = ['id' => $id];
		$result = $this->curlApiRoute('/service/categories/getWithWines',$postData);
		return $result;
	}

	public function getCategoriesAll() {
		$result = $this->curlApiRoute('/service/categories/getAll');
		return $result['categories'];
	}
}