<?php
namespace Interfrog\Vinou\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \Interfrog\Vinou\Utility\PaypalUtility;

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

	protected $paypalToken = '';

	protected $paymentType = 'prepaid';
	protected $payments = [];
	protected $paymentView = TRUE;

	protected $sender = [];
	protected $admin = [];

	protected $detailPid;
	protected $backPid;
	protected $basketPid;
	protected $orderPid;
	protected $finishPid;


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
		isset($this->settings['basketPid']) ? $this->basketPid = $this->settings['basketPid'] : $this->basketPid = NULL;
		isset($this->settings['orderPid']) ? $this->orderPid = $this->settings['orderPid'] : $this->orderPid = $GLOBALS['TSFE']->id;
		isset($this->settings['finishPid']) ? $this->finishPid = $this->settings['finishPid'] : $this->finishPid = $GLOBALS['TSFE']->id;
		$this->settings['cacheExpertise'] = (bool)$this->extConf['cacheExpertise'];

		$this->sender = [
			$this->settings['mail']['senderEmail'] => $this->settings['mail']['senderName']
		];
		$this->admin = [
			$this->settings['mail']['adminEmail'] => $this->settings['mail']['adminName']
		];

		$this->getPaymentMethods();
		$this->paypalToken = PaypalUtility::getPaypalToken($this->extConf['clientId'],$this->extConf['secret'],$this->extConf['mode']);

		$dev = false;
	    if ($this->extConf['vinouMode'] == 'dev') {
	      $dev = true;
	    }

	    $this->api = new \Interfrog\Vinou\Utility\Api(
	      $this->extConf['token'],
	      $this->extConf['authId'],
	      $dev
	    );

		$this->absLocalDir = GeneralUtility::getFileAbsFileName($this->localDir);
		if(!is_dir($this->absLocalDir)){
			mkdir($this->absLocalDir, 0777, true);
		}
	    $this->translations = new \Interfrog\Vinou\Utility\Translation();

	}

	/**
	 * create payment array
	 *
	 * @return void
	 */
	public function getPaymentMethods() {
		$this->storeArgumentInSession('paymentMethod');
		$selectedMethod = $this->getArgumentInSession('paymentMethod');
		$availableMethods = explode(',',$this->settings['payment']['methods']);

		if (!$selectedMethod) {
			$selectedMethod = $this->settings['payment']['default'];
		}

		if (!in_array($selectedMethod,$availableMethods) && $selectedMethod !== $this->settings['payment']['default']) {
			$selectedMethod = $this->settings['payment']['default'];
			$this->writeValueInSession('paymentMethod',$selectedMethod);
		}

		$this->paymentType = $selectedMethod;
		$this->view->assign('paymentMethod',$selectedMethod);
		foreach ($availableMethods as $method) {
			if ($method === $selectedMethod) {
				$this->payments[$method] = [
					'selected' => TRUE
				];
			} else {
				$this->payments[$method] = [
					'selected' => FALSE
				];
			}
		}

		if (count($this->payments) < 2) {
			$this->paymentView = FALSE;
		}
		$this->view->assign('paymentView',$this->paymentView);
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

			$expertise = [
				'src' => 'https://api.vinou.de'.$wine['expertisePDF']
			];

			if ($this->settings['cacheExpertise']) {
				$oldExpertise = $wine['expertisePDF'];
            	$cachePDFProcess = \Interfrog\Vinou\Utility\Pdf::storeApiPDF($oldExpertise,$this->absLocalDir,$wine['id'].'-',$wine['chstamp']);

            	if ($cachePDFProcess['requestStatus'] === 404) {
	                $recreatedExpertise = $this->api->getExpertise($wine['id']);
	                $cachePDFProcess = \Interfrog\Vinou\Utility\Pdf::storeApiPDF($recreatedExpertise,$this->absLocalDir,$wine['id'].'-',$wine['chstamp']);
	            }

				$expertise['tempfile'] = $this->localDir.$cachePDFProcess['fileName'];
			}

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
		return $wine;
	}

	/**
	 * action basket
	 *
	 * @return void
	 */
	public function basketAction() {
		$basket = $this->detectOrCreateBasket();

		if ($this->request->hasArgument('delete')) {
			if ($this->request->getArgument('delete') === 'all') {
				$itemsInBasket = $this->itemsRepository->findByBasketId($basket->getUid());
				foreach ($itemsInBasket as $item) {
					$this->itemsRepository->remove($item);
				}
			} else {
				$item = $this->itemsRepository->findByUid($this->request->getArgument('delete'));
				if (!is_null($item)) {
					$this->itemsRepository->remove($item);
				}
			}
			$this->persistenceManager->persistAll();
		}

		if ($this->request->hasArgument('product') && $this->request->hasArgument('quantity')) {

			if (intval($this->request->getArgument('quantity')) > 0) {
				$newBasketItem = new \Interfrog\Vinou\Domain\Model\Items;
				$newBasketItem->setBasket($basket);
				$newBasketItem->setParenttype('basket');
				$newBasketItem->setProduct($this->productsRepository->findByUid($this->request->getArgument('product')));
				$newBasketItem->setQuantity($this->request->getArgument('quantity'));
				$this->itemsRepository->add($newBasketItem);
				$this->persistenceManager->persistAll();
			}
		}

		$basketData = $this->fetchProductsByBasket($basket);

		$this->view->assign('basket', $basketData['basketData']);
		$this->view->assign('items', $basketData['items']);
		$this->view->assign('currentPid',$GLOBALS['TSFE']->id);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action order
	 *
	 * @return void
	 */
	public function orderAction() {
		$this->initialize();

		$basketData = $this->fetchProductsByBasket($this->detectOrCreateBasket());
		$this->view->assign('basket', $basketData['basketData']);
		$this->view->assign('items', $basketData['items']);
		
		$required = [];
		$required['billing'] = $this->switchFieldValues(explode(',',$this->settings['billing']['required']));
		$required['delivery'] = $this->switchFieldValues(explode(',',$this->settings['delivery']['required']));
		$required['mandatorySign'] = $this->settings['mandatorySign'];
		$this->view->assign('required', $required);

		
		$this->storeArgumentInSession('billing');
		$this->view->assign('billing', $this->getArgumentInSession('billing'));

		$this->storeArgumentInSession('delivery');
		$this->view->assign('delivery', $this->getArgumentInSession('delivery'));

		$account = $this->storeArgumentInSession('account');
		$this->view->assign('account', $this->getArgumentInSession('account'));

		$this->view->assign('settings', $this->settings);
		$this->view->assign('payment', $this->payments);
		$this->view->assign('paymentType', $this->paymentType);

		$mode = 'default';
		/**
		 * SWITCH MODE TO DELIVERY
		 * if deliveryAdress was chcked or the backlink contains delivery
		 */
		if (
			($this->deliveryViewEnabled() && $this->isNextStep('payment')) ||
			$this->isBackStep('delivery') ||
			($this->isBackStep('payment') && !$this->paymentView && isset($GLOBALS['TSFE']->fe_user->sesData['delivery']))
		) {
			$mode = 'delivery';
		}

		/**
		 * SWITCH MODE TO PAYMENT
		 * if billing was set and no delivery was set
		 */
		if (
			($this->isNextStep('payment') && $mode !== 'delivery')  ||
			($this->isBackStep('payment') && $this->paymentView)
		) {
			$mode = 'payment';
		}

		/**
		 * SWITCH MODE TO PAYMENT
		 * if billing was set and no delivery was set
		 */
		if (
			$this->isBackStep('account') && $this->paymentView
		) {
			$mode = 'account';
		}

		/**
		 * SWITCH MODE TO SUMMARY
		 * if billing was set and no delivery was set
		 */
		if (
			($this->isNextStep('summary') && !$this->deliveryViewEnabled()) ||
			($mode == 'payment' && !$this->paymentView && !$this->request->hasArgument('back'))
		) {
			if ($this->getArgumentInSession('paymentMethod') == 'debiting' && !$GLOBALS['TSFE']->fe_user->sesData['account']) {
				$mode = 'account';
			} else {
				$mode = 'summary';
			}
		}

		/**
		 * CREATE ORDER
		 * with check for conditions of purchase
		 */
		if ($this->isNextStep('createOrder')) {
			$mode = 'summary';
			if ($this->request->getArgument('conditionsOfPurchase') !== 'yes') {
				$this->Alert('error','noCop',ERROR);
			} elseif (!isset($GLOBALS['TSFE']->fe_user->sesData['billing'])) {
				$this->Alert('error','noBilling',ERROR);
			} else {
				$this->createOrder($basketData);
			}
		}

		/** 
		 * REDIRECT IF BASKET IS EMPTY
		 *
		 */
		if ($mode === 'default' && is_null($basketData)) {
			$this->redirect('basket', NULL, NULL, [], $this->basketPid);
		}
		
		$this->view->assign('mode',$mode);
	}

	private function createOrder($basketData) {
		$newOrder = new \Interfrog\Vinou\Domain\Model\Orders;

		$newOrder->setBasket($basketData['basketData']['basket']);
		$newOrder->setNet(number_format($basketData['basketData']['net'],2));
		$newOrder->setTax(number_format($basketData['basketData']['tax'],2));
		$newOrder->setGross(number_format($basketData['basketData']['gross'],2));

		$billing = $this->getArgumentInSession('billing');
		/*** BILLING ADDRESS ***/
		/*** required Fields ***/
		$newOrder->setFirstname($billing['firstname']);
		$newOrder->setLastname($billing['lastname']);
		$newOrder->setAddress($billing['address']);
		$newOrder->setZip($billing['zip']);
		$newOrder->setCity($billing['city']);

		/*** BILLING ADDRESS ***/
		/*** additional Fields ***/
		!isset($billing['salutation']) ?: $newOrder->setSalutation($billing['salutation']);
		!isset($billing['company']) ?: $newOrder->setCountry($billing['company']);
		!isset($billing['country']) ?: $newOrder->setCompany($billing['country']);
		!isset($billing['email']) ?: $newOrder->setEmail($billing['email']);
		!isset($billing['phone']) ?: $newOrder->setPhone($billing['phone']);

		$delivery = $this->getArgumentInSession('delivery');
		if (!$delivery) {
			$newOrder->setDeliveryFirstname($billing['firstname']);
			$newOrder->setDeliveryLastname($billing['lastname']);
			$newOrder->setDeliveryAddress($billing['address']);
			$newOrder->setDeliveryZip($billing['zip']);
			$newOrder->setDeliveryCity($billing['city']);

			!isset($billing['company']) ?: $newOrder->setDeliveryCompany($billing['company']);
			!isset($billing['salutation']) ?: $newOrder->setDeliverySalutation($billing['salutation']);
			!isset($billing['country']) ?: $newOrder->setDeliveryCountry($billing['country']);
		} else {
			$newOrder->setDeliveryFirstname($delivery['firstname']);
			$newOrder->setDeliveryLastname($delivery['lastname']);
			$newOrder->setDeliveryAddress($delivery['address']);
			$newOrder->setDeliveryZip($delivery['zip']);
			$newOrder->setDeliveryCity($delivery['city']);

			!isset($delivery['company']) ?: $newOrder->setDeliveryCompany($delivery['company']);
			!isset($delivery['salutation']) ?: $newOrder->setDeliverySalutation($delivery['salutation']);
			!isset($delivery['country']) ?: $newOrder->setDeliveryCountry($delivery['country']);
		}

		$newOrder->setPaymenttype($this->paymentType);
		$this->ordersRepository->add($newOrder);
		$this->persistenceManager->persistAll();

		foreach ($basketData['items'] as $item) {
			$orderItem = $item['rawpos'];
			$orderItem->setShoporder($newOrder);
			$orderItem->setNet(number_format($item['net'],2));
			$orderItem->setGross(number_format($item['gross'],2));
			$orderItem->setOrdered(1);
			$orderItem->setParenttype('shoporder');
			$this->itemsRepository->update($orderItem);
		}
		$this->persistenceManager->persistAll();

		$mailContent = [
			'billing' => $billing,
			'delivery' => $delivery,
			'items' => $basketData['items'],
			'basket' => $basketData['basketData'],
			'paymentMethod' => $this->paymentType
		];

		$recipient = [
			$billing['email'] => $billing['firstname'] . " " . $billing['lastname']
		];

		$attachement = [];
		if ($this->settings['mail']['attachements']['generalBusinessTerms'] !== '') {
			$attachement[] = $this->settings['mail']['attachements']['generalBusinessTerms'];
		}

		$this->sendTemplateEmail(
			$recipient,
			$this->sender,
			\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.createorder.subject','vinou').": ".str_pad($newOrder->getUid(), 10, "0", STR_PAD_LEFT),
			'Createorder',
			$mailContent, 
			$attachement
		);

		$this->sendTemplateEmail(
			$this->admin,
			$this->sender,
			\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.createnotification.subject','vinou').": ".str_pad($newOrder->getUid(), 10, "0", STR_PAD_LEFT),
			'CreateNotification',
			$mailContent
		);

		switch ($this->paymentType) {
			case 'paypal':
				$this->initPaypalPayment($basketData,$newOrder,$billing,$delivery);
				break;
			case 'debiting':
				$this->initDebitingPayment($newOrder);
				break;
			case 'bill':
				$this->clearAllSessionData();
				$this->redirect('finish', NULL, NULL, ['order' => $newOrder->getUid(),'paymentMethod' => 'bill'], $this->finishPid);
				break;
			default:
				/* Prepaid */
				$this->clearAllSessionData();
				$this->redirect('finish', NULL, NULL, ['order' => $newOrder->getUid(),'paymentMethod' => 'prepaid'], $this->finishPid);
				break;
		}
	}

	private function detectOrCreateBasket() {
		if ($GLOBALS['TSFE']->loginUser) {
		    $feUserId = $GLOBALS['TSFE']->fe_user->id;
		    if (!is_null($this->basketsRepository->findByFeUserId($feUserId))) {
		    	return $this->basketsRepository->findByFeUserId($feUserId);
		    } else {
		    	$newBasket = new \Interfrog\Vinou\Domain\Model\Baskets;
		    	$newBasket->setFeUser($feUserId);
		    	$this->basketsRepository->add($newBasket);
		    	$this->persistenceManager->persistAll();
		    	return $newBasket;
		    }
		} else {
		    $sessionId = $GLOBALS['TSFE']->fe_user->id;
		    if (isset($GLOBALS['TSFE']->fe_user->sesData['vinou']['overrideSessionId'])) {
		    	$sessionId = $GLOBALS['TSFE']->fe_user->sesData['vinou']['overrideSessionId'];
		    }
		    $sessionBasket = $this->basketsRepository->findBySessionId($sessionId);

			if (!is_null($sessionBasket)) {
				if (isset($this->itemsRepository->findByOrderedBasket($sessionBasket->getUid())[0])) {
					return $this->createSessionBasket($this->overrideSessionId());
				} else {
					return $sessionBasket;
				}
			} else {
				return $this->createSessionBasket($sessionId);
			}
		}
	}

	private function createSessionBasket($sessionId) {
		$newBasket = new \Interfrog\Vinou\Domain\Model\Baskets;
		$newBasket->setSession($sessionId);
		$this->basketsRepository->add($newBasket);
		$this->persistenceManager->persistAll();
		return $newBasket;
	}

	private function overrideSessionId() {
		$newSessionId = $GLOBALS['TSFE']->fe_user->createSessionId();
		$sessionData = [
			'overrideSessionId' => $newSessionId
		];
		$GLOBALS['TSFE']->fe_user->setKey('ses', 'vinou', $sessionData);
		$GLOBALS['TSFE']->fe_user->storeSessionData();
		return $newSessionId;
	}

	private function isNextStep($name) {
		if ($this->request->hasArgument('nextStep') && $this->request->getArgument('nextStep') == $name) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	private function isBackStep($name) {
		if ($this->request->hasArgument('back') && $this->request->getArgument('back') == $name) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	private function deliveryViewEnabled() {
		if ($this->request->hasArgument('deliveryAdress') && $this->request->getArgument('deliveryAdress') == 'yes') {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	private function switchFieldValues($fields){
		foreach ($fields as $key => $field) {
			$fields[$field] = true;
			unset($fields[$key]);
		}
		return $fields;
	}

	private function storeArgumentInSession($name) {
		if ($this->request->hasArgument($name)) {
			$argument = $this->request->getArgument($name);
			$this->writeValueInSession($name,$argument);
		} else {
			$argument = $this->getArgumentInSession($name);
		}
		return $argument;
	}

	private function writeValueInSession($name,$value) {
		if ($GLOBALS['TSFE']->loginUser) {
			$GLOBALS['TSFE']->fe_user->setKey('user', $name, $value);
		} else {
			$GLOBALS['TSFE']->fe_user->setKey('ses', $name, $value);
		}
		return $argument;
	}

	private function getArgumentInSession($name) {
		if (isset($GLOBALS['TSFE']->fe_user->sesData[$name])) {
			if ($GLOBALS['TSFE']->loginUser) {
				$argument = $GLOBALS['TSFE']->fe_user->getKey('user', $name);
			} else {
				$argument = $GLOBALS['TSFE']->fe_user->getKey('ses', $name);
			}
		} else {
			$argument = FALSE;
		}
		return $argument;
	}

	private function clearAllSessionData() {
		unset($GLOBALS['TSFE']->fe_user->sesData['billing']);
		unset($GLOBALS['TSFE']->fe_user->sesData['delivery']);
		unset($GLOBALS['TSFE']->fe_user->sesData['account']);
		unset($GLOBALS['TSFE']->fe_user->sesData['paymentMethod']);
	}

	private function fetchProductsByBasket($basket) {
		$itemsInBasket = $this->itemsRepository->findByBasketId($basket->getUid());
		$items = [];
		$sumNet = 0;
		$sumGross = 0;
		$posIndex = 1;
		foreach ($itemsInBasket as $item) {
			$product = $item->getProduct();
			$addItem = [];
			$addItem['uid'] = $item->getUid();
			$addItem['index'] = $posIndex;
			$addItem['product'] = $product;
			$addItem['quantity'] = $item->getQuantity();
			$addItem['gross'] = $this->calcGrossPrice($product,$item->getQuantity());
			$addItem['net'] = $this->calcNetPrice($product,$item->getQuantity());
			$addItem['tax'] = $addItem['gross'] - $addItem['net'];
			$addItem['rawpos'] = $item;

			$sumNet = $sumNet + $addItem['net'];
			$sumGross = $sumGross + $addItem['gross'];
			$items[] = $addItem;
			$posIndex++;
		}

		$basketData = [];
		$basketData['basket'] = $basket;
		$basketData['net'] = $sumNet;
		$basketData['gross'] = $sumGross;
		$basketData['tax'] = $sumGross - $sumNet;

		$returnArr = [
			'basketData' => $basketData,
			'items' => $items
		];
		return $returnArr;
	}

	private function fetchProductsByOrder($order) {
		$itemsInOrder = $this->itemsRepository->findByOrderId($order->getUid());
		$items = [];
		$sumNet = 0;
		$sumGross = 0;
		$posIndex = 1;
		foreach ($itemsInOrder as $item) {
			$product = $item->getProduct();
			$addItem = [];
			$addItem['uid'] = $item->getUid();
			$addItem['index'] = $posIndex;
			$addItem['product'] = $product;
			$addItem['quantity'] = $item->getQuantity();
			$addItem['gross'] = $item->getGross();
			$addItem['net'] = $item->getNet();
			$addItem['tax'] = $item->getGross() - $item->getNet();
			$addItem['rawpos'] = $item;

			$sumNet = $sumNet + $addItem['net'];
			$sumGross = $sumGross + $addItem['gross'];
			$items[] = $addItem;
			$posIndex++;
		}

		$orderData = [];
		$orderData['order'] = $order;
		$orderData['net'] = $sumNet;
		$orderData['gross'] = $sumGross;
		$orderData['tax'] = $sumGross - $sumNet;

		$returnArr = [
			'orderData' => $orderData,
			'items' => $items
		];
		return $returnArr;
	}

	private function calcNetPrice($product,$quantity) {
		switch ($product->getPricetype()) {
			case 'net':
				return $product->getPrice() * $quantity;
				break;
			default:
				return ($product->getPrice() / (1 + ($product->getTax() / 100)))  * $quantity;
				break;
		}
	}

	private function calcGrossPrice($product,$quantity) {
		switch ($product->getPricetype()) {
			case 'net':
				return ($product->getPrice() * (1 + ($product->getTax() / 100)))  * $quantity;
				break;
			default:
				return $product->getPrice() * $quantity;
				break;
		}
	}

	private function initPaypalPayment($basketData,$order,$billing,$delivery) {

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    $rootURL = $protocol.\TYPO3\CMS\Core\Utility\GeneralUtility::getHostname();

	    $returnLinkConf = array(
			'parameter' => $this->finishPid,
			'additionalParams' => '&tx_vinou_wines[action]=finish&tx_vinou_wines[controller]=Products&tx_vinou_wines[paymentMethod]=paypal',
			'returnLast' => 'url'
		);
		$returnURL = $rootURL.$GLOBALS['TSFE']->cObj->typoLink('', $returnLinkConf);

		$cancelLinkConf = array(
			'parameter' => $this->finishPid,
			'additionalParams' => '&tx_vinou_wines[action]=cancel&tx_vinou_wines[controller]=Products&tx_vinou_wines[paymentMethod]=paypal',
			'returnLast' => 'url'
		);
		$cancelURL = $rootURL.$GLOBALS['TSFE']->cObj->typoLink('', $cancelLinkConf);

        $min = str_repeat(0, 15) . 1;
        $max = str_repeat(9, 16);
        $invoiceId = mt_rand($min, $max);
        $order->setInvoiceid($invoiceId);

        $paypalItemlist = [];

		foreach ($basketData['items'] as $item) {
			$payPalPosition = [
				"quantity" => strval($item['quantity']),
				"name" => $item['product']->getTitle(),
				// "price" => number_format($this->calcNetPrice($item['product'],1),2),
				"price" => number_format($this->calcGrossPrice($item['product'],1),2),
				"currency" => "EUR",
				"description" => $item['product']->getTeaser(),
				// "tax" => number_format($this->calcGrossPrice($item['product'],1) - $this->calcNetPrice($item['product'],1),2)
			];
			array_push($paypalItemlist,$payPalPosition);
		}

		$paymentInfo = [
			"intent" => "sale",
			"redirect_urls" => [
				"return_url" => $returnURL,
				"cancel_url" => $cancelURL
			],
			"payer" => [
				"payment_method" => "paypal"
			],
			"transactions" => [
				[
					"amount" => [
						"total" => number_format($basketData['basketData']['gross'],2),
						"currency" => "EUR",
						// "details" => [
						// 	"subtotal" => number_format($basketData['basketData']['net'],2),
						// 	"shipping" => "1.00",
						// 	"tax" => number_format($basketData['basketData']['tax'],2),
						// 	"shipping_discount" => "-1.00"
						// ]
					],
					"item_list" => [
						"items" => $paypalItemlist
					],
					"description" => "Bestellung über ".$rootURL,
					"invoice_number" => $invoiceId
				]
			]
		];

		$paymentResult = PaypalUtility::createPayment($paymentInfo,$this->paypalToken,$this->extConf['mode']);

		if (!is_null($paymentResult->links)) {
			$paymentdetails = [];
			foreach ($paymentResult->links as $urls) {
				$paymentdetails[$urls->rel] = $urls->href;
			}

			$order->setTransactionid($paymentResult->id);
			$order->setPaymentdetails(serialize($paymentdetails));
			$this->ordersRepository->update($order);
			$this->persistenceManager->persistAll();

			$this->clearAllSessionData();
			\TYPO3\CMS\Core\Utility\HttpUtility::redirect($paymentdetails['approval_url']);
		} else {
			$this->Alert('error','paypalnotcreated',ERROR);	
		}
	}

	private function initDebitingPayment($order) {
		$accountData = $this->getArgumentInSession('account');
		$paymentdetails = [];
		foreach ($accountData as $label => $value) {
			$paymentdetails[$label] = $value;
		}
		$order->setPaymentdetails(serialize($paymentdetails));
		$this->ordersRepository->update($order);
		$this->persistenceManager->persistAll();

		$this->clearAllSessionData();
		$this->redirect('finish', NULL, NULL, ['order' => $order->getUid(),'paymentMethod' => 'debiting'], $this->finishPid);
	}

	/**
	 * action finish
	 *
	 * @return void
	 */
	public function finishAction() {
		$this->initialize();

		$paymentMethod = $this->getArgumentInSession('paymentMethod');
		if ($this->request->hasArgument('paymentMethod')) {
			$paymentMethod = $this->request->getArgument('paymentMethod');
		}

		$this->view->assign('paymentMethod', $paymentMethod);

		switch ($paymentMethod) {
			case "paypal":
				$payerId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('PayerID');
				$paymentId = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('paymentId');

				if (!is_null($paymentId) && !is_null($payerId)) {
					$order = $this->ordersRepository->findByTransactionid($paymentId);
					if (!is_null($order)) {
						$executeResult = PaypalUtility::executePayment($payerId,$paymentId,$this->paypalToken,$this->extConf['mode']);
						if ($executeResult->state == 'approved') {
							$order->setPayed(1);
							$this->ordersRepository->update($order);
							$this->persistenceManager->persistAll();
							$this->view->assign('order', $order);
							$orderData = $this->fetchProductsByOrder($order);
							$this->view->assign('orderData', $orderData['orderData']);
							$this->view->assign('items', $orderData['items']);
							$this->view->assign('billing', $order->getBilling());
							$this->view->assign('delivery', $order->getDelivery());

							$mailContent = [
								'order' => $orderData['orderData'],
								'items' => $orderData['items'],
								'billing' => $order->getBilling(),
								'delivery' => $order->getDelivery(),
								'paymentMethod' => $order->getPaymenttype()
							];

							$recipient = [
								$order->getEmail() => $order->getFirstname() . " " . $order->getLastname()
							];

							$attachement = [];
							if ($this->settings['mail']['attachements']['cancellationPolicy'] !== '') {
								$attachement[] = $this->settings['mail']['attachements']['cancellationPolicy'];
							}

							$this->sendTemplateEmail(
								$recipient,
								$this->sender,
								\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.acceptorder.subject','vinou').": ".str_pad($order->getUid(), 10, "0", STR_PAD_LEFT),
								'Acceptorder',
								$mailContent,
								$attachement
							);

							$this->sendTemplateEmail(
								$this->admin,
								$this->sender,
								\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.paymentnotification.subject','vinou').": ".str_pad($order->getUid(), 10, "0", STR_PAD_LEFT),
								'PaymentNotification',
								$mailContent
							);
						} else {
							$this->view->assign('error',TRUE);
							$this->Alert('error','paypalprocess',ERROR);			
						}
					} else {
						$this->view->assign('error',TRUE);
						$this->Alert('error','noorder',ERROR);
					}
				}
				break;
			default:
				$order = $this->ordersRepository->findByUid($this->request->getArgument('order'));
				$orderData = $this->fetchProductsByOrder($order);
				$mailContent = [
					'order' => $orderData['orderData'],
					'items' => $orderData['items'],
					'billing' => $order->getBilling(),
					'delivery' => $order->getDelivery(),
					'paymentMethod' => $order->getPaymenttype()
				];

				$recipient = [
					$order->getEmail() => $order->getFirstname() . " " . $order->getLastname()
				];

				$attachement = [];
				if ($this->settings['mail']['attachements']['cancellationPolicy'] !== '') {
					$attachement[] = $this->settings['mail']['attachements']['cancellationPolicy'];
				}

				$this->sendTemplateEmail(
					$recipient,
					$this->sender,
					\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.acceptorder.subject','vinou').": ".str_pad($order->getUid(), 10, "0", STR_PAD_LEFT),
					'Acceptorder',
					$mailContent,
					$attachement
				);

				$this->sendTemplateEmail(
					$this->admin,
					$this->sender,
					\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.paymentnotification.subjectprepaid','vinou').": ".str_pad($order->getUid(), 10, "0", STR_PAD_LEFT),
					'PaymentNotification',
					$mailContent
				);
				break;
		}
		
		$this->view->assign('action', 'finish');
	}

	protected function Alert($titlecode, $messagecode, $type){

		$messageTitle = LocalizationUtility::translate( 'message.title.'.$titlecode , 'vinou');
		$messageContent = LocalizationUtility::translate( 'message.content.'.$messagecode , 'vinou');

		if (substr($_SERVER['HTTP_HOST'],-4,4) == '.dev') {
			$messageContent .= ' '.$messagecode;
		}

		$msg = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
			$messageContent,
			$messageTitle,  
			$type, 
			TRUE
		);
		$this->controllerContext->getFlashMessageQueue()->enqueue($msg);
	}

	/**
	* @param array $recipient recipient of the email in the format array('recipient@domain.tld' => 'Recipient Name')
	* @param array $sender sender of the email in the format array('sender@domain.tld' => 'Sender Name')
	* @param string $subject subject of the email
	* @param string $templateName template name (UpperCamelCase)
	* @param array $variables variables to be passed to the Fluid view
	* @param array $attachement attachement to be passed to the Fluid view
	* @return boolean TRUE on success, otherwise false
	*/
	protected function sendTemplateEmail(array $recipient, array $sender, $subject, $templateName, array $variables = array(), array $attachement = array()) {
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');

		$extbaseFrameworkConfiguration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);

		$templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . 'Plain.html';
		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		$emailView->assignMultiple($variables);
		$emailBody = $emailView->render();
		$message->setBody($emailBody, 'text/plain');

		if ((bool) $this->extConf['sendHtmlMail']) {
			$templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . 'Html.html';
			$emailView->setTemplatePathAndFilename($templatePathAndFilename);
			$emailHtmlBody = $emailView->render();
			$message->addPart($emailHtmlBody, 'text/html');
		}

		$subject = '=?utf-8?B?'. base64_encode($subject) .'?=';
		
		$message->setTo($recipient)
				->setFrom($sender)
				->setSubject($subject);

		if (isset($attachement[0])) {
			foreach ($attachement as $file) {
				$message->attach(\Swift_Attachment::fromPath($file));
			}
		}
		
		$message->send();
		return $message->isSent();
	}

}