<?php
namespace Vinou\VinouConnector\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Core\Bootstrap;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Frontend\Utility\EidUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\ApiConnector\Api;
use \Vinou\VinouConnector\Utility\Render;
use \Vinou\VinouConnector\Utility\Shop;
use \Vinou\VinouConnector\Utility\TypoScriptHelper;

/**
 * This class could called with AJAX via eID
 */
class AjaxActions {

    /**
     * extConf
     * @var array
     */
    protected $api = null;
    protected $errors = [];
    protected $result = false;
    protected $request = [];
    protected $extConf = null;
    protected $settings = [];

    public function __construct($TYPO3_CONF_VARS) {

        $this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['vinou_connector']);

        $this->request = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));

        $this->initTYPO3Frontend();
        $this->initVinou();
        $this->settings = TypoScriptHelper::extractSettings('tx_vinouconnector_shop');
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

    public function run() {

        if (empty($this->request) || !isset($this->request['action']))
            $this->sendResult(false, 'no action defined');

        $action = $this->request['action'];
        unset($this->request['action']);
        switch ($action) {
            case 'init':
                $this->sendResult($this->api->initBasket($this->request), 'basket could not be initialized');
                break;

            case 'get':
                $result = $this->api->getBasket();
                if (!$result)
                    $this->sendResult(false, 'basket not found');
                else {
                    $result['quantity'] = Shop::calcCardQuantity($result['basketItems']);
                    $result['valid'] = Shop::quantityIsAllowed($result['quantity'], $this->settings, true);
                }

                $this->sendResult($result);
                break;

            case 'addItem':
                $this->sendResult($this->api->addItemToBasket($this->request), 'item could not be added');
                break;

            case 'editItem':
                $this->sendResult($this->api->editItemInBasket($this->request), 'item could not be updated');
                break;

            case 'deleteItem':
                $this->sendResult($this->api->deleteItemFromBasket($this->request['id']), 'item could not be deleted');
                break;

            case 'findPackage':
                $this->sendResult($this->api->getBasketPackage());
                break;

            case 'findCampaign':
                $result = $this->api->findCampaign($this->request);
                $campaign = Session::getValue('campaign');
                if ($this->result && $campaign && $this->result['uuid'] == $campaign['uuid'])
                    $this->sendResult(false, 'campaign already activated');
                else
                    $this->sendResult($result, 'campaign could not be resolved');
                break;

            case 'loadCampaign':
                $result = $this->api->findCampaign($this->request);
                if ($result) {
                    Session::setValue('campaign', $result);
                    $this->sendResult($result);
                }
                else
                    $this->sendResult(false, 'campaign could not be resolved');
                break;

            case 'removeCampaign':
                $this->sendResult(Session::deleteValue('campaign'), 'campaign could not be deleted');
                break;

            case 'campaignDiscount':
                $processor = new Shop($this->api);
                $this->sendResult($processor->campaignDiscount(), 'discount could not be fetched');
                break;

            default:
                $this->sendResult(false, 'action could not be resolved');
                break;
        }

    }

    private function sendResult($result, $errorMessage = null) {

        if (!$result) {
            if (is_null($errorMessage))
                $result = ['no result created'];
            else
                array_push($this->errors, $errorMessage);
        }

        if (count($this->errors) > 0)
            Render::sendJSON([
                'info' => 'error',
                'errors' => $this->errors,
                'request' => $this->request
            ], 'error');
        else
            Render::sendJSON($result);

        exit();
    }
}

//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
global $TYPO3_CONF_VARS;

$eid = GeneralUtility::makeInstance(AjaxActions::class, $TYPO3_CONF_VARS);
echo $eid->run();
