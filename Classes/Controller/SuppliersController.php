<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;

class SuppliersController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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

		$suppliers = [];

		$wineries = $this->api->getWineriesAll([
            'filter' => [
                'id' => $ids['winery']
            ],
            'addresses' => 1
        ]);
        if ($wineries) {
            foreach ($wineries as &$winery) {
                $winery['type'] = 'winery';
                $suppliers['winery_' . $winery['id']] = $winery;
            }
        }

        $merchants = $this->api->getMerchantsAll([
            'filter' => [
                'id' => $ids['merchant']
            ],
            'addresses' => 1
        ]);

        if ($merchants){
            foreach ($merchants as &$merchant) {
                $merchant['type'] = 'merchant';
                $suppliers['merchant_' . $merchant['id']] = $merchant;
            }
        }

		$this->view->assign('suppliers', $suppliers);
	}

	/**
	 * action detail
	 *
	 * @return void
	 */
	public function detailAction() {
		$this->initialize();

		if (!$this->request->hasArgument('type'))
			return;
		
		$identifier = null;
		if ($this->request->hasArgument('id'))
			$identifier = $this->request->getArgument('id');
		if ($this->request->hasArgument('path_segment'))
			$identifier = $this->request->getArgument('path_segment');
		if (is_null($identifier))
			return;

		switch ($this->request->getArgument('type')) {
			case 'merchant':
				$supplier = $this->api->getMerchant($identifier);
				break;

			default:
				$supplier = $this->api->getWinery($identifier);
				break;
		}

		$this->view->assign('supplier', $supplier);
		if ($supplier['files'] && count($supplier['files']) > 0) {
			$this->view->assign('images', array_filter($supplier['files'], function($file) {
			    return $file['type'] == 'image';
			}));

			$this->view->assign('files', array_filter($supplier['files'], function($file) {
			    return $file['type'] != 'image';
			}));
		}
	}

}