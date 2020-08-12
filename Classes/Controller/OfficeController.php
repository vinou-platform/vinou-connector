<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\ApiConnector\Api;
use \Vinou\ApiConnector\PublicApi;
use \Vinou\ApiConnector\Session\Session;

class OfficeController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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

	protected $api = null;
	protected $llPath = 'Resources/Private/Language/';
	protected $localDir = 'typo3temp/vinou/';
	protected $registrationDir = 'vinou/registrations';
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
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;

		$this->checkFolders();

		$dev = false;
	    if ($this->extConf['vinouMode'] == 'dev') {
	      $dev = true;
	    }

	    if (!empty($this->extConf['token']) && !empty($this->extConf['authId'])) {
	    	$this->api = new Api(
			  $this->extConf['token'],
			  $this->extConf['authId'],
			  true,
			  $dev
			);
	    }

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

	private function checkFolders() {
		$registrationDir = PATH_site . $this->registrationDir;

		if (!is_dir($registrationDir))
			mkdir($registrationDir, 0777, true);

		$htaccess = $registrationDir .'/.htaccess';
		if (!is_file($htaccess)) {
			$content = 'Deny from all';
			file_put_contents($htaccess, $content);
		}
	}

	/**
	 * action register
	 *
	 * @return void
	 */
	public function registerAction() {
		$this->initialize();
		$required = $this->switchFieldValues(explode(',', $this->settings['register']['required']));
		$required['mandatorySign'] = $this->settings['mandatorySign'];
		$this->view->assign('required', $required);
		$this->settings['source'] = $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'];

		$arguments = $this->request->getArguments();
		if ($this->request->getMethod() == 'POST' && count($arguments)) {
			file_put_contents($this->registrationDir .'/registration-'.time().'.json', json_encode($customer));

			$api = new PublicApi();
			$registration = $api->register($arguments['customer']);

			if ($registration) {
				$this->view->assign('registration', TRUE);
				$finishPid = (int)$this->settings['finishPID'];
				if ($finishPid > 1) {
					$uriBuilder = $this->uriBuilder;
					$uri = $uriBuilder
					  ->setTargetPageUid($finishPid)
					  ->build();
					$this->redirectToUri($uri, 0, 404);
				}
			}
			else {
				$this->Alert('error','registrationError',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$this->view->assign('customer', $arguments['customer']);
			}

		}

		$this->view->assign('settings', $this->settings);

	}

	private function switchFieldValues($fields){
		foreach ($fields as $key => $field) {
			$fields[$field] = true;
			unset($fields[$key]);
		}
		return $fields;
	}

	protected function Alert($titlecode, $messagecode, $type){

		$messageTitle = LocalizationUtility::translate( 'message.title.'.$titlecode , $this->extKey);
		$messageContent = LocalizationUtility::translate( 'message.content.'.$messagecode , $this->extKey);

		if (substr($_SERVER['HTTP_HOST'],-4,4) == '.dev') {
			$messageContent .= ' '.$messagecode;
		}

		$msg = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
			$messageContent,
			$messageTitle,
			$type,
			TRUE
		);
		$this->controllerContext->getFlashMessageQueue()->enqueue($msg);
	}

}