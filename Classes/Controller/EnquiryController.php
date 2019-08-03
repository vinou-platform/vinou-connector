<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Core\Utility\PathUtility;
use \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class EnquiryController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;
		$this->settings['cacheExpertise'] = (bool)$this->extConf['cacheExpertise'];

		$this->sender = [
			$this->settings['mail']['senderEmail'] => $this->settings['mail']['senderName']
		];
		$this->admin = [
			$this->settings['mail']['adminEmail'] => $this->settings['mail']['adminName']
		];

		$dev = false;
	    if ($this->extConf['vinouMode'] == 'dev') {
	      $dev = true;
	    }

	    $this->api = new \Vinou\ApiConnector\Api(
	      $this->extConf['token'],
	      $this->extConf['authId'],
	      true,
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

	/**
	 * action detail
	 *
	 * @return void
	 */
	public function formAction() {
		$this->initialize();
		$postData = [];
		if (isset($this->settings['sortBy']) && !empty($this->settings['sortBy'])) {
			$postData['sortBy'] = $this->settings['sortBy'];
		}
		if (isset($this->settings['sortDirection']) && !empty($this->settings['sortDirection'])) {
			$postData['sortDirection'] = $this->settings['sortDirection'];
		}
		if(empty($postData)) {
			$wines = $this->api->getWinesAll();	
		} else {
			$wines = $this->api->getWinesAll($postData);
		}
		$this->view->assign('wines', $wines);
		$this->view->assign('settings', $this->settings);
	}

	/**
	 * action detail
	 *
	 * @return void
	 */
	public function submitRequestAction() {
		$this->initialize();
		$args = $this->request->getArguments();
		$mailData = [
			'net' => $args['net'],
			'tax' => $args['tax'],
			'gross' => $args['gross'],
			'wines' => []
		];
		foreach ($args['wines'] as $wine) {
			if ($wine['quantity'] > 0) {
				$wine['sum'] = $wine['quantity'] * $wine['price'];
				array_push($mailData['wines'],$wine);
			}
		}
		$mailData['customer'] = $args['customer'];
		$mailData['age'] = $args['age'];
		$mailData['shipping'] = $args['shipping'];
		$mailData['title'] = $this->settings['senderMailSubject'];

		/*customer Mail*/
		$customerMail = $this->sendTemplateEmail(
			$args['customer']['email'],
			[$this->settings['serverMail'] => $this->settings['serverName']],
			$this->settings['senderMailSubject'],
			$this->settings['mail']['senderMailTemplate'],
			$mailData
		);

		$mailData['title'] = $this->settings['adminMailSubject'];
		/*admin Mail*/
		$adminMail = $this->sendTemplateEmail(
			$this->settings['requestTo'],
			[$this->settings['serverMail'] => $this->settings['serverName']],
			$this->settings['adminMailSubject'],
			$this->settings['mail']['adminMailTemplate'],
			$mailData,
			$args['customer']['email']
		);

		if ($customerMail && $adminMail) {
			$uriBuilder = $this->uriBuilder;
			$uri = $uriBuilder
			  ->setTargetPageUid($this->settings['finishPid'])
			  ->build();
			$this->redirectToUri($uri);
		} else {
			$error = [
				'customerMail' => $customerMail,
				'adminMail' => $adminMail
			];
		}

		$this->view->assign('errors', $error);
		$this->view->assign('settings', $this->settings);
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
	protected function sendTemplateEmail($recipient, $sender, $subject, $templateFile, array $variables = array(), $replyTo = false, array $attachement = array()) {
		/** @var \TYPO3\CMS\Fluid\View\StandaloneView $emailView */
		$emailView = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$message = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');

		$emailView->setTemplatePathAndFilename($templateFile);
		$emailView->assignMultiple($variables);
		$emailBody = $emailView->render();

		$subject = '=?utf-8?B?'. base64_encode($subject) .'?=';
		$message->setTo($recipient)
				->setFrom($sender)
				->setSubject($subject);

		if (!is_null($cc)) {
            if (strpos($cc,',')) {
                $cc = explode(',',$cc);
            }
            $message->setCc($cc);
        }
        if (!is_null($bcc)) {
            if (strpos($bcc,',')) {
                $bcc = explode(',',$bcc);
            }
            $message->setBcc($bcc);
        }
        if ($replyTo) {
        	$message->setReplyTo($replyTo);
        }

        $message->setBody($emailBody);
        $message->addPart($emailBody, 'text/html');

		if (isset($attachement[0])) {
			foreach ($attachement as $file) {
				$message->attach(\Swift_Attachment::fromPath($file));
			}
		}
		
		$message->send();
		return $message->isSent();
	}

}