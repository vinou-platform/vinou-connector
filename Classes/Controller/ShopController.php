<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \Vinou\VinouConnector\Utility\PaypalUtility;

class ShopController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
	protected $orderDir = 'vinou/orders';
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
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;
		$this->settings['cacheExpertise'] = (bool)$this->extConf['cacheExpertise'];

		$this->checkFolders();

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

	    $this->api = new \Vinou\VinouConnector\Utility\Api(
	      $this->extConf['token'],
	      $this->extConf['authId'],
	      $dev
	    );

		$this->absLocalDir = GeneralUtility::getFileAbsFileName($this->localDir);
		if(!is_dir($this->absLocalDir)){
			mkdir($this->absLocalDir, 0777, true);
		}
	    $this->translations = new \Vinou\VinouConnector\Utility\Translation();

	    $loggedIn = FALSE;
		if($GLOBALS['TSFE']->loginUser) {
			$loggedIn = TRUE;
		}
		$this->view->assign('loggedIn', $loggedIn);

	}

	private function checkFolders() {
		$orderDir = PATH_site . $this->orderDir;

		if (!is_dir($orderDir))
			mkdir($orderDir, 0777, true);

		$htaccess = $orderDir .'/.htaccess';
		if (!is_file($htaccess)) {
			$content = 'Deny from all';
			file_put_contents($htaccess, $content);
		}
	}

	public function listAction() {
		$this->initialize();

		$postData = [
			'inshop' => true,
			'orderBy' => 'topseller DESC, sorting ASC'
		];
		$clusters = isset($this->settings['clusters']) ? explode(',',$this->settings['clusters']) : null;
		if (!is_null($clusters))
			$postData['cluster'] = $clusters;

		$data = $this->api->getWinesAll($postData);

		$this->view->assign('wines',$data['wines']);

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
		$this->initialize();

		$basket = $this->detectOrCreateBasket();
		$this->view->assign('basket',$basket);

		if (isset($basket['basketItems']) && count($basket['basketItems']) > 0) {
			$items = $basket['basketItems'];
			$this->view->assign('summary', $this->calculateSum($items));
			$this->view->assign('items', $items);
		}

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
			$this->api->removeSessionData('delivery');
		}

		/* get message if was set */
		$message = $this->storeAndGetArgument('message');
		$this->view->assign('message', $message);

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
					$this->Alert('error','noCop',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				} else if ($this->request->getArgument('gdpr') !== 'yes') {
					$this->Alert('error','noGdpr',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				} else {
					//$this->Alert('error','noConnection',\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					$this->sendOrderByBasket(
						$basket,
						$items,
						$summary,
						$billing,
						$delivery,
						$paymentMethod,
						$account,
						$message
					);
				}
				break;
			default:
				break;
		}

		$this->view->assign('mode',$mode);
	}

	private function sendOrderByBasket($basket,$items,$summary,$billing,$delivery,$paymentMethod,$account,$message) {
		$order = [
			'source' => 'shop',
			'payment_type' => $paymentMethod,
			'basket' => $basket['uuid'],
			'billing' => [
				'firstname' => $billing['firstname'],
				'lastname' => $billing['lastname'],
				'address' => $billing['address'],
				'zip' => $billing['zip'],
				'city' => $billing['city'],
				'email' => $billing['email'],
			],
			'delivery' => [
				'firstname' => $delivery['firstname'],
				'lastname' => $delivery['lastname'],
				'address' => $delivery['address'],
				'zip' => $delivery['zip'],
				'city' => $delivery['city'],
			],
		];

		$additionalBilling = ['salutation', 'company', 'country', 'phone'];
		foreach ($additionalBilling as $label) {
			if (isset($billing[$label]))
				$order['billing'][$label] = $billing[$label];
		}

		$additionalDelivery = ['salutation', 'company', 'country'];
		foreach ($additionalDelivery as $label) {
			if (isset($delivery[$label]))
				$order['delivery'][$label] = $delivery[$label];
		}

		$sendResult = $this->api->addOrder($order);
		if ($sendResult) {
			if ($message) {
				$order['message'] = $message;
			}

			file_put_contents($this->orderDir .'/order-'.time().'.json', json_encode($order));

			$recipient = [
				$billing['email'] => $billing['firstname'] . " " . $billing['lastname']
			];

			$mailContent = $order;
			$mailContent['items'] = $items;
			$mailContent['summary'] = $summary;

			$this->sendTemplateEmail(
				$recipient,
				$this->sender,
				\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.createorder.subject',$this->extKey).':'.$sendResult['data']['number'],
				'CreateOrderClient',
				$mailContent,
				$this->settings['mail']['attachements']
			);

			$this->sendTemplateEmail(
				$this->admin,
				$this->sender,
				\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.createnotification.subject',$this->extKey).':'.$sendResult['data']['number'],
				'CreateOrderAdmin',
				$mailContent
			);

			$this->clearAllSessionData();
			$this->redirect(NULL, NULL, NULL, [], $this->finishPid);
		}

		return true;
	}

	private function getRequiredFields() {
		$required = [];
		$required['billing'] = $this->switchFieldValues(explode(',',$this->settings['billing']['required']));
		$required['delivery'] = $this->switchFieldValues(explode(',',$this->settings['delivery']['required']));
		$required['mandatorySign'] = $this->settings['mandatorySign'];
		return $required;
	}

	private function detectOrCreateBasket() {
		$basketUuid = $this->api->readSessionData('basket');
		if (is_null($basketUuid) && isset($_COOKIE['basket'])) {
			$basketUuid = $_COOKIE['basket'];
		}

		return !is_null($basketUuid) ? $this->api->getBasket($basketUuid) : false;
	}

	private function storeAndGetArgument($argument) {
		if ($this->request->hasArgument($argument)) {
			$this->api->writeSessionData($argument,$this->request->getArgument($argument));
		}
		return $this->api->readSessionData($argument);
	}

	private function calculateSum(&$items) {
		$summary = [
			'net' => 0,
			'tax' => 0,
			'gross' => 0,
			'quantity' => 0,
		];

		foreach ($items as $item) {
			$summary['quantity'] += $item['quantity'];
			$summary['gross'] += ($item['quantity'] * $item['object']['price']);
		}

		$package = $this->api->findPackage('bottles',$summary['quantity']);
		$items[] = [
			'item_type' => 'package',
			'item_id' => $package['id'],
			'quantity' => 1,
			'object' => $package
		];

		$summary['gross'] += $package['price'];

		$summary['net'] = $summary['gross'] / 1.19;
		$summary['tax'] = $summary['gross'] - $summary['net'];
		return $summary;
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
		$this->api->removeSessionData('billing');
		$this->api->removeSessionData('delivery');
		$this->api->removeSessionData('deliveryAdress');
		$this->api->removeSessionData('message');
		$this->api->removeSessionData('account');
		$this->api->removeSessionData('paymentMethod');
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
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');

		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPaths']['']);

		$templatePathAndFilename = PATH_site . 'typo3conf/ext/vinou_connector/Resources/Private/Templates/Email/' . $templateName . '.html';
		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		$emailView->assignMultiple($variables);
		$emailHtmlBody = $emailView->render();
		$message->addPart($emailHtmlBody, 'text/html');

		$subject = '=?utf-8?B?'. base64_encode($subject) .'?=';

		$message->setTo($recipient)
				->setFrom($sender)
				->setSubject($subject);

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