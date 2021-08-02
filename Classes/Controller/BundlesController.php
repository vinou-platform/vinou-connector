<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;

class BundlesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
		if ($this->request->hasArgument('backPid')) {
			$this->backPid = $this->request->getArgument('backPid');
		}
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;

	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		$this->initialize();

		$result = $this->api->getBundlesAll();
		$bundles = array_key_exists('data', $result) ? $result['data'] : $result;

		$this->view->assign('bundles', $bundles);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action detail
	 *
	 * @param int $bundle
	 * @return void
	 */
	public function detailAction($bundle = NULL) {
		$this->initialize();

		if ($this->request->hasArgument('bundle')) {
			$bundleId = $this->request->getArgument('bundle');
			$bundle = $this->api->getBundle($bundleId);
		} else if ($this->request->hasArgument('path_segment')) {
			$pathSegment = $this->request->getArgument('path_segment');
			$bundle = $this->api->getBundle($pathSegment);
		}

		$this->view->assign('bundle', $bundle);
		$this->view->assign('backPid', $this->backPid);
		$this->view->assign('settings', $this->settings);
	}

}