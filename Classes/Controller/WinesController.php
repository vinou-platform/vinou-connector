<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class WinesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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

	    $this->api = new \Vinou\VinouConnector\Utility\Api(
	      $this->extConf['token'],
	      $this->extConf['authId'],
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

		switch ($this->settings['listMode']) {
			case 'category':
				$postData = [
					'id' => $this->settings['category']
				];
				if (isset($this->settings['sortBy']) && !empty($this->settings['sortBy'])) {
					$postData['sortBy'] = $this->settings['sortBy'];
				}
				if (isset($this->settings['sortDirection']) && !empty($this->settings['sortDirection'])) {
					$postData['sortDirection'] = $this->settings['sortDirection'];
				}
				if (isset($this->settings['groupBy']) && !empty($this->settings['groupBy'])) {
					$postData['groupBy'] = $this->settings['groupBy'];
					$groups = $this->api->getWinesByCategory($postData);
					foreach ($groups as $groupIndex => $group) {
						foreach ($group as $index => $wine) {
							$groups[$groupIndex][$index] = $this->localizeWine($wine);
						}
					}
				} else {
					$wines = $this->api->getWinesByCategory($postData);
					foreach ($wines as $index => $wine) {
						$wines[$index] = $this->localizeWine($wine);
					}
				}
				$this->view->assign('category',$this->api->getCategory($this->settings['category']));
				break;

			case 'type':
				$wines = $this->api->getWinesByType($this->settings['type']);
				foreach ($wines as $index => $wine) {
					$wines[$index] = $this->localizeWine($wine);
				}
				break;
		}


		$this->view->assign('wines', $wines);
		empty($groups) ?: $this->view->assign('groups', $groups);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action detail
	 *
	 * @param int $wine
	 * @return void
	 */
	public function detailAction($wine = NULL) {
		$this->initialize();
		if ($this->request->hasArgument('wine')) {
			$wineId = $this->request->getArgument('wine');
			$wine = $this->api->getWine($wineId);

			$this->view->assign('wine', $this->localizeWine($wine));

			$this->view->assign('expertise', $expertise);
		}

		$this->view->assign('backPid', $this->backPid);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 *
	 * localize wine
	 *
	 * @param array $wine
	 * @return string
	 */
	private function detectExpertise($wine) {
		$src = "https://api.vinou.de".$wine['expertisePDF'];

		if ($this->settings['cacheExpertise']) {
			if ($wine['expertiseStatus']=='OK') {
				$expertiseFile = $fileName = array_values(array_slice(explode('/',$wine['expertisePDF']), -1))[0];
				$convertedFileName = \Vinou\VinouConnector\Utility\Pdf::convertFileName($wine['id'].'-'.$fileName);
				$localFile = $this->absLocalDir .$convertedFileName;

				$dateTimeZone = new \DateTimeZone(date_default_timezone_get());
				$timeOffset = $dateTimeZone->getOffset(new \DateTime("now",$dateTimeZone));

				if(!file_exists($localFile) || strtotime($wine['chstamp'] . ' + ' . $timeOffset / 3600 .' hours') > filemtime($localFile)){
					$src = '/?eID=cacheExpertise&wineID='.$wine['id'];
				} else {
					$src = '/'. $this->localDir . $convertedFileName. '?' .time();
				}
			} else {
				$src = '/?eID=cacheExpertise&wineID='.$wine['id'];
			}
		}

		return $src;
	}

	/**
	 *
	 * localize wine
	 *
	 * @param array $wine
	 * @return array
	 */
	private function localizeWine($wine = NULL) {
		foreach ($wine as $property => $value) {
			switch ($property) {
				case 'grapetypes':
					$grapetypes = [];
					foreach ($value as $grapetype) {
						$grapetypes[$grapetype] = $this->translations->grapetypes[$grapetype];
					}
					$wine[$property] = $grapetypes;
					break;
				case 'type':
					$wine['winetype'] = $value;
					$wine[$property] = $this->translations->winetypes[$value];
					break;
				case 'tastes_id':
					$wine[$property] = $this->translations->tastes[$value];
					break;
				case 'region':
					$wine[$property] = $this->translations->regions[$value];
					break;
				default:
					$wine[$property] = $value;
			}
		}
		$wine['expertiseFile'] = $this->detectExpertise($wine);
		return $wine;
	}

}