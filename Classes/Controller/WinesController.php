<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\ApiConnector\FileHandler\Pdf;
use \Vinou\Translations\Utilities\Translation;
use \Vinou\VinouConnector\Utility\Helper;

class WinesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
   * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
   */
  protected $configurationManager;

	/**
	 * @var Api Api Endpoint.
	 */
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
				if (isset($this->settings['sortBy']) && !empty($this->settings['sortBy']))
					$postData['sortBy'] = $this->settings['sortBy'];

				if (isset($this->settings['sortDirection']) && !empty($this->settings['sortDirection']))
					$postData['sortDirection'] = $this->settings['sortDirection'];

				if (isset($this->settings['groupBy']) && !empty($this->settings['groupBy'])) {
					$postData['groupBy'] = $this->settings['groupBy'];
					$groups = $this->api->getWinesByCategory($postData);
					foreach ($groups as $groupIndex => $group) {
						foreach ($group as $index => $wine) {
							$groups[$groupIndex][$index] = $wine;
						}
					}
				}
				else
					$wines = $this->api->getWinesByCategory($postData);

				$this->view->assign('category',$this->api->getCategory($this->settings['category']));
				break;

			case 'type':
				$wines = $this->api->getWinesByType($this->settings['type']);
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

		if ($this->request->hasArgument('wine'))
			$identifier = $this->request->getArgument('wine');

		if ($this->request->hasArgument('path_segment'))
			$identifier = $this->request->getArgument('path_segment');

		$this->view->assign('wine', $this->api->getWine($identifier));

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

}