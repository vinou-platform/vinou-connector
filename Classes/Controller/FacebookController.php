<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class FacebookController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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

	protected $llPath = 'Resources/Private/Language/';
	protected $fbApp;
	protected $fbConnection;
	protected $graphApiVersion = 'v3.2';
	protected $settings;

	public function initialize() {
		$this->extKey = $this->request->getControllerExtensionKey();
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->llPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey).$this->llPath;

		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		$this->initFB();
	}

	private function initFB() {
		$this->fbApp = new \Facebook\FacebookApp(
			$this->extConf['appId'],
			$this->extConf['appSecret']
		);
		$token = $this->fbApp->getAccessToken();
		$this->fbConnection = new \Facebook\Facebook([
		  'app_id' => $this->extConf['appId'],
		  'app_secret' => $this->extConf['appSecret'],
		  'default_graph_version' => 'v3.2',
		  'default_access_token' => $token
		]);
	}


	private function getFBContent($object, $fields = [], $selector = 'data') {
		if (is_null($this->settings['pageId']) || $this->settings['pageId'] == '') {
			throw new \Exception('No pageId given');
		}

		$url = '/'. $this->settings['pageId'] .'/'.$object.'?limit='.$this->settings['limit'];

		if (!empty($fields))
			$url .= '&fields='.implode(',', $fields);

		$result = $this->fbConnection->get($url);
		$content = json_decode($result->getBody(), true);

		return $content[$selector];
	}

	/**
	 * action events
	 *
	 * @return void
	 */
	public function eventsAction() {
		$this->initialize();

		$content = json_decode(file_get_contents(
		    \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('events.json')
		), true);


		$rawEvents = array_reverse($content['data']);
		$events = [];
		$now = time();
		$i = 1;
		foreach ($rawEvents as $event) {
			if (strtotime($event['start_time']) > $now) {
				$events[] = $event;
				$i++;
				if ($i > $this->settings['limit'])
					break;
			}
		}

		$this->view->assign('events', $events);

		//$this->view->assign('events', $this->getFBContent('events'));

		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action feed
	 *
	 * @return void
	 */
	public function feedAction() {
		$this->initialize();

		$this->view->assign('feed', $this->getFBContent('feed', ['link','message','full_picture','created_time']));

		$this->view->assign('settings', $this->settings);
	}

}