<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \Vinou\ApiConnector\Session\Session;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\VinouConnector\Utility\Shop;

class ShopController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * objectManager
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

	protected $api;

	protected $settings = [];
	protected $errors = [];
	protected $messages = [];

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

		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

		$this->extKey = Helper::getExtKey();
		$this->api = Helper::initApi();

		$this->settings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);
		isset($this->settings['detailPid']) ? $this->detailPid = $this->settings['detailPid'] : $this->detailPid = NULL;
		isset($this->settings['backPid']) ? $this->backPid = $this->settings['backPid'] : $this->backPid = NULL;
		if ($this->request->hasArgument('backPid')) {
			$this->backPid = $this->request->getArgument('backPid');
		}
		isset($this->settings['basketPid']) ? $this->basketPid = $this->settings['basketPid'] : $this->basketPid = NULL;
		isset($this->settings['orderPid']) ? $this->orderPid = $this->settings['orderPid'] : $this->orderPid = $GLOBALS['TSFE']->id;
		isset($this->settings['finishPid']) ? $this->finishPid = $this->settings['finishPid'] : $this->finishPid = $GLOBALS['TSFE']->id;
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;
		$this->settings['cacheExpertise'] = (bool)$this->extConf['cacheExpertise'];


		$this->sender = [
			$this->settings['mail']['senderEmail'] => $this->settings['mail']['senderName']
		];
		$this->admin = [
			$this->settings['mail']['adminEmail'] => $this->settings['mail']['adminName']
		];

		$this->getPaymentMethods();
	}

	public function listAction() {
		$this->initialize();

		$objectTypes = explode(',',$this->settings['showTypes']);
		$items = [];

		if (in_array('wines',$objectTypes)) {

			$postData = [
				'inshop' => true,
				'orderBy' => 'topseller DESC, sorting ASC'
			];
			$clusters = isset($this->settings['clusters']) ? explode(',',$this->settings['clusters']) : null;
			if (!is_null($clusters))
				$postData['cluster'] = $clusters;

			if ((int)$this->settings['category'] > 0) {
				$data = $this->api->getCategoryWines((int)$this->settings['category'], $postData);
				$wines = isset($data['data']) ? $data['data'] : false;

			} else {
				$data = $this->api->getWinesAll($postData);
				$wines = isset($data['wines']) ? $data['wines'] : $data['data'];
			}

			foreach ($wines as &$wine) {
				$wine['object_type'] = 'wine';
			}
			$items = array_merge($items,$wines);

			if (isset($data['clusters'])) {
				if (isset($data['clusters']['categories'])) {
					$categories = [];
					foreach ($data['clusters']['categories'] as $catArray) {
						foreach ($catArray as $key => $category) {
							if (!isset($categories[$key]))
								$categories[$key] = $category;
						}
					}
					$data['clusters']['categories'] = $categories;
				}

				$this->view->assign('clusters',$data['clusters']);
			}
		}

		if (in_array('products',$objectTypes)) {

			$products = $this->api->getProductsAll();
			if ($products) {
				foreach ($products as &$product) {
					$product['object_type'] = 'product';
				}
				$items = array_merge($items,$products);
			}
		}

		if (in_array('bundles',$objectTypes)) {

			$bundles = $this->api->getBundlesAll();
			if ($bundles) {
				foreach ($bundles['data'] as &$bundle) {
					$bundle['object_type'] = 'bundle';
				}
				$items = array_merge($items,$bundles['data']);
			}

		}

		$sortProperty = strlen($this->settings['sortBy']) > 0 ? $this->settings['sortBy'] : 'sorting';

		$sortDirection = $this->settings['sortDirection'];

		usort($items, function($a, $b) use ($sortProperty, $sortDirection) {
			if ($a['topseller'] == $b['topseller']) {
				if (strcmp($sortDirection, 'ASC') == 0)
					return $a[$sortProperty] > $b[$sortProperty];
				else
					return $a[$sortProperty] < $b[$sortProperty];
			}
			return $a['topseller'] < $b['topseller'];
		});

		$this->view->assign('settings',$this->settings);
		$this->view->assign('items',$items);

	}


	public function topsellerAction() {
		$this->initialize();

		$objectTypes = explode(',',$this->settings['showTypes']);
		$items = [];
		$postData = [
			'inshop' => true,
			'filter' => [
				'topseller' => true
			],
			'orderBy' => 'sorting ASC'
		];

		if (in_array('wines',$objectTypes)) {

			$data = $this->api->getWinesAll($postData);
			$wines = isset($data['wines']) ? $data['wines'] : $data['data'];
			foreach ($wines as &$wine) {
				if ($wine['topseller'] == 0)
					continue;

				$wine['object_type'] = 'wine';
				array_push($items, $wine);
			}
		}

		if (in_array('products',$objectTypes)) {

			$products = $this->api->getProductsAll($postData);
			foreach ($products as &$product) {
				if ($product['topseller'] == 0)
					continue;

				$product['object_type'] = 'product';
				array_push($items, $product);
			}
		}

		if (in_array('bundles',$objectTypes)) {

			$bundles = $this->api->getBundlesAll($postData);
			foreach ($bundles['data'] as &$bundle) {
				if ($bundle['topseller'] == 0)
					continue;

				$bundle['object_type'] = 'bundle';
				array_push($items, $bundle);
			}
		}

		$sortProperty = strlen($this->settings['sortBy']) > 0 ? $this->settings['sortBy'] : 'sorting';
		$sortDirection = $this->settings['sortDirection'];

		usort($items, function($a, $b) use ($sortProperty, $sortDirection) {
			if ($a['topseller'] == $b['topseller']) {
				if (strcmp($sortDirection, 'ASC') == 0)
					return $a[$sortProperty] > $b[$sortProperty];
				else
					return $a[$sortProperty] < $b[$sortProperty];
			}
			return $a['topseller'] < $b['topseller'];
		});

		$this->view->assign('settings',$this->settings);
		$this->view->assign('items',$items);

	}

	/**
	 * create payment array
	 *
	 * @return void
	 */
	public function getPaymentMethods() {
		$selectedMethod = $this->storeAndGetArgument('paymentMethod');
		$availableMethods = explode(',',$this->settings['payment']['methods']);

		if (!$selectedMethod) {
			$selectedMethod = $this->settings['payment']['default'];
		}

		if (!in_array($selectedMethod,$availableMethods) && $selectedMethod !== $this->settings['payment']['default']) {
			$selectedMethod = $this->settings['payment']['default'];
			Session::setValue('paymentMethod',$selectedMethod);
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
	 * action basket
	 *
	 * @return void
	 */
	public function basketAction() {
		$this->initialize();

		if ($this->request->hasArgument('removecampaign') && $this->request->getArgument('removecampaign') == 1)
			Session::deleteValue('campaign');

		$basket = $this->detectOrCreateBasket();
		$this->view->assign('basket',$basket);

		if (isset($basket['basketItems']) && count($basket['basketItems']) > 0) {
			$items = $basket['basketItems'];
			$this->view->assign('items', $items);

			$summary = $this->calculateSum($items);
			$validation = Shop::quantityIsAllowed($summary['bottles'], $this->settings['basket'], true);

			foreach ($items as $item) {
				if ($item['item_type'] == 'package')
					$this->view->assign('package', $package);
			}

			$this->view->assign('validation', $validation);
			$this->view->assign('summary', $summary);
		}

		$packagings = $this->api->getAllPackages();
		$this->view->assign('packagings', $packagings);
		$this->view->assign('currentPid',$GLOBALS['TSFE']->id);
		$this->view->assign('customer', $this->api->getCustomer());
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action order
	 *
	 * @return void
	 */
	public function orderAction() {
		$this->initialize();

		/* Collect Settings for orderView */
		$this->view->assign('required', $this->getRequiredFields());
		$this->view->assign('settings', $this->settings);

		/* get basket data */
		$basket = $this->detectOrCreateBasket();

		/* goto basket view if no basket could be detected */
		if (is_null($basket))
			$this->redirect('basket', NULL, NULL, [], $this->basketPid);

		$this->view->assign('basket', $basket);

		/* detect for package and calculate summary*/
		$items = $basket['basketItems'];


		$summary = $this->calculateSum($items);

		$basketSettings = $this->settings['basket'];
		if (isset($basketSettings['minBasketSize']) && $summary['bottles'] < $basketSettings['minBasketSize'])
			$this->redirect(NULL, NULL, NULL, [], $this->basketPid);

		if (isset($basketSettings['packageSteps']) && !in_array($summary['bottles'], explode(',',$basketSettings['packageSteps'])))
			$this->redirect(NULL, NULL, NULL, [], $this->basketPid);

		$this->view->assign('summary', $summary);
		$this->view->assign('items', $items);

		/* get billing information */
		$billing = $this->storeAndGetArgument('billing');
		$this->view->assign('billing', $billing);

		if((bool)$this->storeAndGetArgument('deliveryAdress')) {
			/* set delivery if a separate dddress was defined */
			$delivery = $this->storeAndGetArgument('delivery');
			$this->view->assign('delivery', $this->storeAndGetArgument('delivery'));
		} else {
			/* set billing as delivery if no separate address was defined  */
			$delivery = $billing;
			Session::deleteValue('delivery');
		}

		/* get message if was set */
		$note = $this->storeAndGetArgument('note');
		$this->view->assign('note', $note);

		/* get account information */
		$account = $this->storeAndGetArgument('account');
		$this->view->assign('account', $account);

		/* set payment method */
		$paymentMethod = $this->storeAndGetArgument('paymentMethod');
		$this->view->assign('paymentMethod', $paymentMethod);

		$mode = 'default';
		if ($this->request->hasArgument('goTo'))
			$mode = $this->request->getArgument('goTo');

		switch ($mode) {
			case 'payment':
				$this->view->assign('payment', $this->payments);
				$this->view->assign('paymentType', $this->paymentType);
				break;
			case 'account':
				break;
			case 'summary':
				if ($paymentMethod == 'debiting' && !$account)
					$mode = 'account';
				break;
			case 'submit':
				if ($this->request->getArgument('conditionsOfPurchase') !== 'yes') {
					$this->Alert('error','noCop', FlashMessage::ERROR);
				} else if ($this->request->getArgument('gdpr') !== 'yes') {
					$this->Alert('error','noGdpr', FlashMessage::ERROR);
				} else {
					//$this->Alert('error','noConnection', FlashMessage::ERROR);
					$this->sendOrderByBasket(
						$basket,
						$items,
						$summary,
						$billing,
						$delivery,
						$paymentMethod,
						$account,
						$note
					);
				}
				break;
			default:
				break;
		}

		$this->view->assign('mode',$mode);
	}

	private function sendOrderByBasket($basket,$items,$summary,$billing,$delivery,$paymentMethod,$account,$note) {

		$campaign = Session::getValue('campaign');
        if ($campaign && $campaign['data']['id'] > 0) {
        	$this->api->addItemToBasket(['data' => [
                'quantity' => 1,
                'item_type' => 'campaign',
                'item_id' => $campaign['data']['id']
            ]]);
        }

		$order = [
			'source' => 'shop',
			'payment_type' => $paymentMethod,
			'basket' => $basket['uuid'],
			'billing_type' => 'client',
			'billing' => [
				'first_name' => $billing['firstname'],
				'last_name' => $billing['lastname'],
				'address' => $billing['address'],
				'zip' => $billing['zip'],
				'city' => $billing['city'],
				'mail' => $billing['email'],
			],
			'delivery_type' => 'address',
			'delivery' => [
				'first_name' => $delivery['firstname'],
				'last_name' => $delivery['lastname'],
				'address' => $delivery['address'],
				'zip' => $delivery['zip'],
				'city' => $delivery['city'],
			],
			'invoice_type' => isset($this->settings['checkout']['invoice_type']) ? $this->settings['checkout']['invoice_type'] : 'gross',
            'payment_period' => isset($this->settings['checkout']['payment_period']) ? (int)$this->settings['checkout']['payment_period'] : 14,
			'note' => $note
		];

		$additionalBilling = ['gender', 'company', 'countrycode', 'phone'];
		foreach ($additionalBilling as $label) {
			if (isset($billing[$label]))
				$order['billing'][$label] = $billing[$label];
		}

		$additionalDelivery = ['gender', 'company', 'countrycode'];
		foreach ($additionalDelivery as $label) {
			if (isset($delivery[$label]))
				$order['delivery'][$label] = $delivery[$label];
		}

		$check = $this->api->checkClientMail($order['billing']);
        if ($check) {
            $order['client_id'] = $check;
            $order['billing_type'] = 'address';
        } else {
            unset($order['billing_type']);
        }

		if ($paymentMethod == 'paypal') {

			if (!isset($this->settings['finishPaypalPid']))
				$this->Alert('error','nofinishPaypalPid', FlashMessage::ERROR);

			$order['return_url'] = $this->uriBuilder
				->reset()
				->setTargetPageUid($this->settings['finishPaypalPid'])
				->setCreateAbsoluteUri(TRUE)
				->setNoCache(TRUE)
				->build();

			if (!isset($this->settings['cancelPaypalPid']))
				$this->Alert('error','nocancelPaypalPid', FlashMessage::ERROR);

			$order['cancel_url'] = $this->uriBuilder
				->reset()
				->setTargetPageUid($this->settings['cancelPaypalPid'])
				->setCreateAbsoluteUri(TRUE)
				->setNoCache(TRUE)
				->build();

		}

		if (in_array($paymentMethod, ['card', 'debit'])) {

			if (!isset($this->settings['finishPaymentPid']))
				$this->Alert('error','nofinishPaymentPid',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

			$order['return_url'] = $this->uriBuilder
				->reset()
				->setTargetPageUid($this->settings['finishPaymentPid'])
				->setCreateAbsoluteUri(TRUE)
				->setNoCache(TRUE)
				->build();

			if (!isset($this->settings['cancelPaymentPid']))
				$this->Alert('error','nocancelPaymentPid',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);

			$order['cancel_url'] = $this->uriBuilder
				->reset()
				->setTargetPageUid($this->settings['cancelPaymentPid'])
				->setCreateAbsoluteUri(TRUE)
				->setNoCache(TRUE)
				->build();

		}

		file_put_contents(Helper::getOrderCacheDir() . '/order-'. time() . '.json', json_encode($order));

		// addOrder will do the redirect if paypal was set
		$addedOrder = $this->api->addOrder($order);

		if ($addedOrder) {

			Session::setValue('order_uuid', $addedOrder['uuid']);

			$recipient = [
				$billing['email'] => $billing['firstname'] . " " . $billing['lastname']
			];

			$mailContent = [
				'order' => $this->api->getOrder($addedOrder['id'])
			];

			$this->sendTemplateEmail(
				$recipient,
				$this->sender,
				LocalizationUtility::translate('mail.createorder.subject',$this->extKey).':'.$addedOrder['number'],
				'CreateOrderClient',
				$mailContent,
				$this->settings['mail']['attachements']
			);

			$this->sendTemplateEmail(
				$this->admin,
				$this->sender,
				LocalizationUtility::translate('mail.createnotification.subject',$this->extKey).':'.$addedOrder['number'],
				'CreateOrderAdmin',
				$mailContent
			);

			$this->initPayment();
		}

		return true;
	}

	public function initPayment() {

		$stripe = Session::getValue('stripe');

		if ($stripe && array_key_exists('sessionId', $stripe) && array_key_exists('publishableKey', $stripe))
			$this->redirect(NULL, NULL, NULL, [], $this->settings['initPaymentPid']);

		$this->clearAllSessionData();
		$this->redirect(NULL, NULL, NULL, [], $this->finishPid);
	}

	public function finishPaypalAction() {
		$this->initialize();
		$error = false;

		$this->view->assign('settings', $this->settings);
		$order = $this->api->getSessionOrder();
		$this->view->assign('order', $order);

		$paypalresult = $this->api->finishPaypalPayment(GeneralUtility::_GET());
		$this->view->assign('paypalresult', $paypalresult);

		if (!$paypalresult) {
			$error = true;
		} else {
			$recipient = [
				$order['client']['mail'] => $order['client']['first_name'] . " " . $order['client']['last_name']
			];

			$mailContent = [
				'order' => $order
			];

			$this->sendTemplateEmail(
				$recipient,
				$this->sender,
				LocalizationUtility::translate('mail.createorder.subject',$this->extKey).':'.$order['number'],
				'CreateOrderClient',
				$mailContent,
				$this->settings['mail']['attachements']
			);

			$this->sendTemplateEmail(
				$this->admin,
				$this->sender,
				LocalizationUtility::translate('mail.createnotification.subject',$this->extKey).':'.$order['number'],
				'CreateOrderAdmin',
				$mailContent
			);

			$this->clearAllSessionData();
		}

		$this->view->assign('error', $error);
	}

	public function cancelPaypalAction() {
		$this->initialize();
		$this->view->assign('settings', $this->settings);
		$this->view->assign('order', $this->api->getSessionOrder());
		$error = false;

		$cancelresult = $this->api->cancelPaypalPayment();

		if (!$cancelresult) {
			$error = true;
		} else {
			$this->view->assign('cancelresult', $cancelresult);
		}

		$this->view->assign('error', $error);
	}

	public function initPaymentAction() {
		$this->initialize();
		$this->view->assign('settings', $this->settings);

		$stripe = Session::getValue('stripe');
		$this->view->assign('stripe', $stripe);
	}

	public function finishPaymentAction() {
		$this->initialize();
		$this->view->assign('settings', $this->settings);
		$this->view->assign('result', $this->api->finishPayment(GeneralUtility::_GET()));
		$this->view->assign('addedOrder', $this->api->getSessionOrder());
		$this->clearAllSessionData();
	}

	public function cancelPaymentAction() {
		$this->initialize();
		$this->view->assign('settings', $this->settings);
		$this->view->assign('result', $this->api->cancelPayment(GeneralUtility::_GET()));
		$this->view->assign('addedOrder', $this->api->getSessionOrder());
		$this->clearAllSessionData();
	}

	private function getRequiredFields() {
		$required = [];
		$required['billing'] = $this->switchFieldValues(explode(',',$this->settings['billing']['required']));
		$required['delivery'] = $this->switchFieldValues(explode(',',$this->settings['delivery']['required']));
		$required['mandatorySign'] = $this->settings['mandatorySign'];
		return $required;
	}

	private function detectOrCreateBasket() {
		$basketUuid = Session::getValue('basket');
		if (!$basketUuid && isset($_COOKIE['basket'])) {
			$basketUuid = $_COOKIE['basket'];
		}

		return $basketUuid ? $this->api->getBasket($basketUuid) : false;
	}

	private function storeAndGetArgument($argument) {
		if ($this->request->hasArgument($argument)) {
			Session::setValue($argument,$this->request->getArgument($argument));
		}
		return Session::getValue($argument);
	}

	/**
	* @param array $items items to calculate
	* @return array $summary
	*/
	private function calculateSum(&$items) {

		$summary = [
			'net' => 0,
			'tax' => 0,
			'gross' => 0,
			'quantity' => 0,
			'bottles' => 0
		];

		foreach ($items as $item) {

			if ($item['item_type'] == 'bundle'){
                $summary['quantity'] += $item['quantity'] * $item['item']['package_quantity'];
                $summary['bottles'] += $item['quantity'] * $item['item']['package_quantity'];
			}
            else {
                $summary['quantity'] += $item['quantity'];
            	$summary['bottles'] += $item['quantity'];
            }

			$summary['gross'] += ($item['quantity'] * $item['item']['gross']);
		}

		// load and append package
		$package = $this->api->findPackage('bottles',$summary['bottles']);

		$customer = $this->api->getCustomer();
		$fflimit = $customer['settings']['freightfreelimit'] ?? false;
		$applyfflimit = floatval($summary['gross']) >= floatval($fflimit);

		$package = [
			'item_type' => 'package',
			'item_id' => $package['id'],
			'quantity' => 1,
			'item' => $package,
			'gross' => $package && isset($package['gross']) && !$applyfflimit ? $package['gross'] : 0,
			'tax' =>  $package && isset($package['tax']) && !$applyfflimit ? $package['tax'] : 0,
			'net' =>  $package && isset($package['net']) && !$applyfflimit ? $package['net'] : 0,
			'taxrate' =>  $package && isset($package['taxrate']) ? $package['taxrate'] : 19,
		];

		$items[] = $package;
		$summary['gross'] += $package['gross'];


		// load and append campaign
		$campaign = Session::getValue('campaign');

		if (isset($campaign['data'])) {
			$data = $campaign['data'];
			$campaignItems = [];
			foreach ($items as $item) {
				if (isset($item['item_id']) && !is_null($item['item_id'])) {
					array_push($campaignItems, [
						'item_id' => $item['item_id'],
						'item_type' => $item['item_type'],
						'quantity' => $item['quantity'],
						'net' => $item['net'],
						'tax' => $item['tax'],
						'gross' => $item['gross'],
						'taxrate' => isset($item['object']['taxrate']) ? $item['object']['taxrate'] : 19
					]);
				}
			}

			// reload and calculate campaign
			$new = $this->api->findCampaign([
				'hash' => $data['hash'],
				'items' => $campaignItems
			]);

			if ($new && isset($new['data'])) {
				Session::setValue('campaign', $new);
				$campaign = $new;
			}

			if (isset($campaign['summary']) && isset($campaign['summary']['gross']))
				$summary['gross'] = $summary['gross'] + $campaign['summary']['gross'];

		}

		$this->view->assign('campaign', isset($campaign['data']) ? $campaign['data'] : $campaign);
		$this->view->assign('campaignDiscount', isset($campaign['summary']) ? $campaign['summary'] : false);

		$summary['net'] = $summary['gross'] / 1.19;
		$summary['tax'] = $summary['gross'] - $summary['net'];
		return $summary;
	}

	/**
	* @param array $fields fields to modify
	* @return array $fields
	*/
	private function switchFieldValues($fields){
		foreach ($fields as $key => $field) {
			$fields[$field] = true;
			unset($fields[$key]);
		}
		return $fields;
	}

	private function clearAllSessionData() {
		Session::deleteValue('billing');
		Session::deleteValue('delivery');
		Session::deleteValue('deliveryAdress');
		Session::deleteValue('message');
		Session::deleteValue('account');
		Session::deleteValue('campaign');
		Session::deleteValue('note');
	}

	/**
	 * action finish
	 *
	 * @return void
	 */
	public function finishAction() {
		$this->initialize();

		// ToDo add ne finish action
		$this->view->assign('action', 'finish');
		$this->view->assign('paymentMethod', Session::getValue('paymentMethod'));
		$this->view->assign('customer', $this->api->getCustomer());
		$this->view->assign('order', $this->api->getSessionOrder());

	}

	protected function Alert($titlecode, $messagecode, $type){

		$messageTitle = LocalizationUtility::translate( 'message.title.'.$titlecode , $this->extKey);
		$messageContent = LocalizationUtility::translate( 'message.content.'.$messagecode , $this->extKey);

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

		$extPath = 'typo3conf/ext/vinou_connector/Resources/Private/';

		$emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$emailView->setLayoutRootPaths([PATH_site . $extPath . 'Layouts/Email/']);
		$emailView->setTemplateRootPaths([PATH_site . $extPath . 'Templates/Email/']);
		$emailView->setTemplate($templateName . '.html');

		$variables['title'] = $subject;
		$variables['customer'] = $this->api->getCustomer();
		$emailView->assignMultiple($variables);
		$emailHtmlBody = $emailView->render();

		$message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$message->addPart($emailHtmlBody, 'text/html');
		$message->setTo($recipient)
				->setFrom($sender)
				->setSubject('=?utf-8?B?'. base64_encode($subject) .'?=');

		if (count($attachement) > 0) {
			foreach ($attachement as $file) {
				if ($file != '')
					$message->attach(\Swift_Attachment::fromPath($file));
			}
		}

		$message->send();
		return $message->isSent();
	}

}