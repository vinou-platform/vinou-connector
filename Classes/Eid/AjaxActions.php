<?php
namespace Vinou\VinouConnector\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Core\Bootstrap;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Frontend\Utility\EidUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\ApiConnector\Api;

/**
 * This class could called with AJAX via eID
 */
class AjaxActions {

    /**
     * extConf
     * @var array
     */
    protected $extConf;
    protected $api = null;
    protected $errors = [];
    protected $output = false;
    protected $data = [];

    public function __construct($TYPO3_CONF_VARS) {

        $this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['vinou_connector']);

        $this->setHeader();
        $this->initTYPO3Frontend();
        $this->initVinou();

    }

    public function run() {
        $this->loadInput();
        $this->handleActions();
        $this->renderOutput();
    }

    private function setHeader() {
        header('Content-type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    }

    private function initTYPO3Frontend() {
        $userObj = EidUtility::initFeUser();
        $pid = (GeneralUtility::_GET('id') ? GeneralUtility::_GET('id') : 1);
        $GLOBALS['TSFE'] = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $TYPO3_CONF_VARS,
            $pid,
            0,
            true
        );
        $GLOBALS['TSFE']->connectToDB();
        $GLOBALS['TSFE']->fe_user = $userObj;
        $GLOBALS['TSFE']->id = $pid;
        $GLOBALS['TSFE']->determineId();
        $GLOBALS['TSFE']->initTemplate();
        $GLOBALS['TSFE']->getConfigArray();
    }

    private function initVinou() {
        $dev = false;
        if ($this->extConf['vinouMode'] == 'dev') {
            $dev = true;
        }

        $this->api = new Api (
            $this->extConf['token'],
            $this->extConf['authId'],
            true,
            $dev
        );
    }

    private function loadInput() {
        $this->data = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    }

    private function handleActions() {
        if (empty($this->data) || !isset($this->data['action'])) {
            array_push($this->errors, 'no action defined');
            return false;
        }

        $action = $this->data['action'];
        unset($this->data['action']);
        switch ($action) {
            case 'init':
                $this->output = $this->api->initBasket();
                break;

            case 'get':
                $this->output = $this->api->getBasket();
                break;

            case 'addItem':
                $this->output = $this->api->addItemToBasket($this->data);
                break;

            case 'editItem':
                $this->output = $this->api->editItemInBasket($this->data);
                break;

            case 'deleteItem':
                $this->output = $this->api->deleteItemFromBasket($this->data['id']);
                break;

            case 'findPackage':
                $this->output = $this->api->getBasketPackage();
                break;

            default:
                array_push($this->errors, 'action could not be resolved');
                break;
        }

        if (!$this->output)
            array_push($this->errors, 'no result created');
    }

    private function renderOutput() {
        if (count($this->errors) > 0)
            $this->sendError($this->errors);
        else
            $this->sendResult();

    }

    private function sendError($data) {
        header('HTTP/1.0 400 Bad Request');
        echo json_encode($data);
        exit();
    }

    private function sendResult() {
        header('HTTP/1.1 200 OK');
        echo json_encode($this->output);
        exit();
    }
}

//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
global $TYPO3_CONF_VARS;

$eid = GeneralUtility::makeInstance(AjaxActions::class, $TYPO3_CONF_VARS);
echo $eid->run();
