<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\ApiConnector\FileHandler\Pdf;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\VinouConnector\Utility\Translation;

class WinesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

	protected $api;
	protected $absLocalDir = '';
	protected $translations;

	protected $detailPid;
	protected $backPid;


	public function initialize() {

		$this->api = Helper::initApi();
		$this->absLocalDir = Helper::ensureCacheDir();
	    $this->translations = new Translation();

		$this->settings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		isset($this->settings['detailPid']) ? $this->detailPid = $this->settings['detailPid'] : $this->detailPid = NULL;
		isset($this->settings['backPid']) ? $this->backPid = $this->settings['backPid'] : $this->backPid = NULL;
		if ($this->request->hasArgument('backPid')) {
			$this->backPid = $this->request->getArgument('backPid');
		}
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;
		$this->settings['cacheExpertise'] = Helper::getExtConfValue('cacheExpertise');

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
		} else if ($this->request->hasArgument('path_segment')) {
			$pathSegment = $this->request->getArgument('path_segment');
			$wine = $this->api->getWine($pathSegment);
		}
		$this->view->assign('wine', $this->localizeWine($wine));

		$this->view->assign('backPid', $this->backPid);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action detail
	 *
	 * @return void
	 */
	public function topsellerAction() {

		$postData = [];

		if (isset($this->settings['sortBy']) && !empty($this->settings['sortBy'])) {
			$postData['sortBy'] = $this->settings['sortBy'];
		}
		if (isset($this->settings['sortDirection']) && !empty($this->settings['sortDirection'])) {
			$postData['sortDirection'] = $this->settings['sortDirection'];
		}


		$wines = $this->api->getWinesAll($postData);
		$topseller = array_filter($wines, function($item) {
			return $item['topseller'] == 1;
		});

		$this->view->assign('topseller', $topseller);

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
				$convertedFileName = Pdf::convertFileName($wine['id'].'-'.$fileName);
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
		if ($wine['language'] != $GLOBALS['TSFE']->sys_language_isocode)
			return null;

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