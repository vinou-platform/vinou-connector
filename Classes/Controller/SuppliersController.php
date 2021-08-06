<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use Vinou\ApiConnector\Api;
use \Vinou\VinouConnector\Utility\Helper;

class SuppliersController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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

		$suppliers = [];

		$wineries = $this->api->getWineriesAll([
            // 'filter' => [
            //     'id' => $ids['winery']
            // ],
            'addresses' => 1
        ]);

		if ($wineries) {
				foreach ($wineries as &$winery) {
						$winery['type'] = 'winery';
						$suppliers['winery_' . $winery['id']] = $winery;
				}
		}

		$merchants = $this->api->getMerchantsAll([
				// 'filter' => [
				//     'id' => $ids['merchant']
				// ],
				'addresses' => 1
		]);

		if ($merchants){
				foreach ($merchants as &$merchant) {
						$merchant['type'] = 'merchant';
						$suppliers['merchant_' . $merchant['id']] = $merchant;
				}
		}


		$resetFilter = false;
		if($this->request->hasArgument('reset'))
			if($this->request->getArgument('reset'))
				$resetFilter = true;

		$filters = [
			'sortBy' => ['company','city','type','zip'],
			'sortDirection' => ['ASC','DESC']
		];
		if($resetFilter) {
			foreach($filters as $name => $values){
				$GLOBALS['TSFE']->fe_user->setAndSaveSessionData('suppliersList'.ucfirst($name), null);
			}
		} else {

			foreach($filters as $name => $values){
				$sessionVar = $GLOBALS['TSFE']->fe_user->getKey('ses', 'suppliersList'.ucfirst($name));
				if(in_array($sessionVar,$values)) $this->settings[$name] = $sessionVar;

				if ($this->request->hasArgument($name)) {
					$requestVar = $this->request->getArgument($name);
					if(in_array($requestVar,$values)) {
						$this->settings[$name] = $requestVar;
						$GLOBALS['TSFE']->fe_user->setKey('ses', 'suppliersList'.ucfirst($name), $this->settings[$name]);
					}
				}
			}

		}

		$this->view->assign('settings', $this->settings);


		$sortProperty = strlen($this->settings['sortBy']) > 0 ? $this->settings['sortBy'] : reset($filters['sortBy']);
		$sortDirection = $this->settings['sortDirection'];


		usort($suppliers, function($a, $b) use ($sortProperty, $sortDirection) {
			if (strcmp($sortDirection, 'ASC') == 0)
				return $a[$sortProperty] > $b[$sortProperty];
			else
				return $a[$sortProperty] < $b[$sortProperty];
		});


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

		$supplierId = null;
		if ($this->request->hasArgument('id'))
			$supplierId = $this->request->getArgument('id');
		if ($this->request->hasArgument('path_segment'))
			$supplierId = $this->request->getArgument('path_segment');
		if (is_null($supplierId))
			return;

		switch ($this->request->getArgument('type')) {
			case 'merchant':
				$supplier = $this->api->getMerchant($supplierId);
				break;

			default:
				$supplier = $this->api->getWinery($supplierId);
				break;
		}

		$this->view->assign('supplier', $supplier);
		$this->view->assign('type', $this->request->getArgument('type'));

		if ($supplier['files'] && count($supplier['files']) > 0) {
			$this->view->assign('images', array_filter($supplier['files'], function($file) {
			    return $file['type'] == 'image';
			}));

			$this->view->assign('files', array_filter($supplier['files'], function($file) {
			    return $file['type'] != 'image';
			}));
		}

		$items = [];

		$postData = [
			'filter' => [
				'winery_id' => $supplier['id'],
				'shop' => 1
			],
			'orderBy' => 'sorting ASC',
		];
		$data = $this->api->getWinesAll($postData);
		$wines = isset($data['wines']) ? $data['wines'] : $data['data'];
		if($wines)
			foreach ($wines as &$wine) {
				$wine['object_type'] = 'wine';
				$items[] = $wine;
			}

		// $products = $this->api->getProductsAll($postData);
		// if($products)
		// 	foreach ($products as &$product) {
		// 		$product['object_type'] = 'product';
		// 		array_push($items, $product);
		// 	}

		// $bundles = $this->api->getBundlesAll($postData);
		// if($bundles)
		// 	foreach ($bundles['data'] as &$bundle) {
		// 		$bundle['object_type'] = 'bundle';
		// 		array_push($items, $bundle);
		// 	}

		$sortProperty = 'sorting';
		$sortDirection = 'ASC';

		usort($items, function($a, $b) use ($sortProperty, $sortDirection) {
			if (strcmp($sortDirection, 'ASC') == 0)
				return $a[$sortProperty] > $b[$sortProperty];
			else
				return $a[$sortProperty] < $b[$sortProperty];
		});

		$this->view->assign('items',$items);


		$textIdentifier = null;
		if ($this->request->hasArgument('identifier'))
			$textIdentifier = $this->request->getArgument('identifier');

		$textIdentifiers = ['conditionsofservice','legalnotice','privacypolicy','revocation','legalnotice','shippingpolicy'];

		$legalTexts = [];
		$texts = $this->api->getTextsAll(['winery_id' => $supplier['id']]);
		foreach( $texts as $text) {
			if(in_array($text['identifier'], $textIdentifiers)) {
				$text['active'] = false;

				if($text['identifier'] == $textIdentifier) {
					$text['active'] = true;
					$this->view->assign('text',$text);
				}
				$legalTexts[$text['identifier']] = $text;
			}
		}
		$this->view->assign('legalTexts',$legalTexts);

	}

}