<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;

class ProductsController extends ActionController {

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
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

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
		$this->view->assign('products', $this->api->getProductsAll());
	}

	/**
	 * action detail
	 *
	 * @param int $product
	 * @return void
	 */
	public function detailAction($product = NULL) {
		$this->initialize();
		$identifier = null;

		if ($this->request->hasArgument('product'))
			$identifier = $this->request->getArgument('product');
		if ($this->request->hasArgument('path_segment'))
			$identifier = $this->request->getArgument('path_segment');

		if (is_null($identifier))
			return;

		$this->view->assign('product', $this->api->getProduct($identifier));
	}

}