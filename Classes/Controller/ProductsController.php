<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\ApiConnector\Api;

class ProductsController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * extKey
	 * @var string
	 */
	protected $extKey;

	/**
	 * extConf
	 * @var array
	 */
	protected $extConf;

	/**
	 * objectManager
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager; 

	/**
	 * configurationManager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

	protected $api;
	protected $llPath = 'Resources/Private/Language/';
	protected $localDir = 'typo3temp/vinou/';
	protected $absLocalDir = '';
	protected $translations;

	protected $errors = [];
	protected $messages = [];

	protected $detailPid;
	protected $backPid;


	public function initialize() {
		$this->extKey = $this->request->getControllerExtensionKey();
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->llPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey).$this->llPath;

		$settings = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		$this->settings = $settings;
		isset($this->settings['detailPid']) ? $this->detailPid = $this->settings['detailPid'] : $this->detailPid = NULL;
		isset($this->settings['backPid']) ? $this->backPid = $this->settings['backPid'] : $this->backPid = NULL;
		if ($this->request->hasArgument('backPid')) {
			$this->backPid = $this->request->getArgument('backPid');
		}
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;
		$this->settings['cacheExpertise'] = (bool)$this->extConf['cacheExpertise'];

		$dev = false;
	    if ($this->extConf['vinouMode'] == 'dev') {
	      $dev = true;
	    }

	    $this->api = new Api(
	      $this->extConf['token'],
	      $this->extConf['authId'],
	      true,
	      $dev
	    );

	    if (isset($this->extConf['cachingFolder'])) {
	    	$this->localDir = $this->extConf['cachingFolder'];
            if (substr($this->localDir, -1) != '/') {
                $this->localDir = $this->localDir . '/';
            }
	    }
		$this->absLocalDir = GeneralUtility::getFileAbsFileName($this->localDir);


		if(!is_dir($this->absLocalDir)){
			mkdir($this->absLocalDir, 0777, true);
		}
	    $this->translations = new \Vinou\VinouConnector\Utility\Translation();

	    $loggedIn = FALSE;
		if($GLOBALS['TSFE']->loginUser) {
			$loggedIn = TRUE;
		}
		$this->view->assign('loggedIn', $loggedIn);

	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$this->initialize();

		$this->view->assign('products', $this->api->getProductsAll());
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action detail
	 *
	 * @param int $product
	 * @return void
	 */
	public function detailAction($product = NULL) {
		$this->initialize();
		if ($this->request->hasArgument('product')) {
			$productId = $this->request->getArgument('product');
			$product = $this->api->getProduct($productId);

			$this->view->assign('product', $product);
		}

		$this->view->assign('backPid', $this->backPid);
		$this->view->assign('settings', $this->settings);
	}

}