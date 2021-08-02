<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;

class WineriesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
   * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
   */
  protected $configurationManager;

	/**
	 * @var Api Api Endpoint.
	 */
	protected $api;

	protected $detailPid;
	protected $backPid;


	public function initialize() {

		$this->api = Helper::initApi();

		$this->settings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		isset($this->settings['detailPid']) ? $this->detailPid = $this->settings['detailPid'] : $this->detailPid = NULL;
		isset($this->settings['backPid']) ? $this->backPid = $this->settings['backPid'] : $this->backPid = NULL;
		if ($this->request->hasArgument('backPid'))
			$this->backPid = $this->request->getArgument('backPid');

		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;

		$this->view->assign('backPid', $this->backPid);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$this->initialize();
		$this->view->assign('wineries', $this->api->getWineriesAll());
	}

	/**
	 * action detail
	 *
	 * @param int $winery
	 * @return void
	 */
	public function detailAction($winery = NULL) {
		$this->initialize();
		$identifier = null;

		if ($this->request->hasArgument('winery'))
			$identifier = $this->request->getArgument('winery');
		if ($this->request->hasArgument('path_segment'))
			$identifier = $this->request->getArgument('path_segment');

		if (is_null($identifier))
			return;

		$winery = $this->api->getWinery($identifier);
		$this->view->assign('winery', $winery);

		if ($winery['files'] && count($winery['files']) > 0) {
			$this->view->assign('images', array_filter($winery['files'], function($file) {
			    return $file['type'] == 'image';
			}));

			$this->view->assign('files', array_filter($winery['files'], function($file) {
			    return $file['type'] != 'image';
			}));
		}
	}

}