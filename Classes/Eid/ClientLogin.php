<?php
namespace Vinou\VinouConnector\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\VinouConnector\Utility\Helper;

/**
 * This class could called with AJAX via eID
 *
 * @author  Christian Händel <christian@vinou.de>, Vinou.
 * @package TYPO3
 * @subpackage  ClientLogin
 */
class ClientLogin {

    protected $api;

    public function __construct() {

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        $this->api = Helper::initApi();
    }

    public function run() {

        $data = $this->api->getClientLogin();
        if (!$data) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;

    }

}

error_reporting(E_ALL);
ini_set("display_errors", 1);
define('VINOU_MODE','Ajax');

$eid = GeneralUtility::makeInstance(ClientLogin::class);
echo $eid->run();
?>