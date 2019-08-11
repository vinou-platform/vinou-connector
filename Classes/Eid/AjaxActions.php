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
    protected $api;

    /**
     * Generates the output
     *
     * @return string rendered action
     */
    public function run() {

        $input = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));

        $errors = [];
        $result = false;

        if (!empty($input) && isset($input['action'])) {
            $action = $input['action'];
            unset($input['action']);
            switch ($action) {

                case 'init':
                    $result = $this->api->initBasket();
                    break;

                case 'get':
                    $result = $this->api->getBasket();
                    break;

                case 'addItem':
                    $result = $this->api->addItemToBasket($input);
                    break;

                case 'editItem':
                    $result = $this->api->editItemInBasket($input);
                    break;

                case 'deleteItem':
                    $result = $this->api->deleteItemFromBasket($input['id']);
                    break;

                case 'findPackage':
                    $result = $this->api->findPackage($input['type'],$input['quantity']);
                    break;

                default:
                    array_push($errors, 'action could not be resolved');
                    break;
            }
        } else {
            array_push($errors, 'no action defined');
        }

        if (count($errors) > 0) {
            header('HTTP/1.0 400 Bad Request');
            echo json_encode($errors);
            exit();
        }

        header('HTTP/1.1 200 OK');
        echo json_encode($result);


    }

    /**
     * Initialize Extbase
     *
     * @param array $TYPO3_CONF_VARS The global array. Will be set internally
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct($TYPO3_CONF_VARS)
    {

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

        $this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['vinou_connector']);

        header('Content-type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        $this->initVinou();
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
}
//error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
global $TYPO3_CONF_VARS;

$eid = GeneralUtility::makeInstance(AjaxActions::class, $TYPO3_CONF_VARS);
echo $eid->run();
