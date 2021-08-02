<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;

class MerchantsController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
		$this->view->assign('merchants', $this->api->getMerchantsAll());
	}

	/**
	 * action detail
	 *
	 * @param int $merchant
	 * @return void
	 */
	public function detailAction($merchant = NULL) {
		$this->initialize();
		$identifier = null;

		if ($this->request->hasArgument('merchant'))
			$identifier = $this->request->getArgument('merchant');
		if ($this->request->hasArgument('path_segment'))
			$identifier = $this->request->getArgument('path_segment');

		if (is_null($identifier))
			return;

		$merchant = $this->api->getMerchant($identifier);
		$this->view->assign('merchant', $merchant);
		if ($merchant['files'] && count($merchant['files']) > 0) {
			$this->view->assign('images', array_filter($merchant['files'], function($file) {
			    return $file['type'] == 'image';
			}));

			$this->view->assign('files', array_filter($merchant['files'], function($file) {
			    return $file['type'] != 'image';
			}));
		}
	}

}