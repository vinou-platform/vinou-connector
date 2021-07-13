<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\ApiConnector\Session\Session;

class ClientController extends ActionController {

	/**
	 * objectManager
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @TYPO3\CMS\Extbase\Annotation\Inject
	 */
	protected $persistenceManager;

	/**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

	/**
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
     */
    protected $uriBuilder;

    protected $client;
	protected $extKey;
	protected $api;
	protected $authService;
	protected $sender;
	protected $admin;


	public function initialize($internal = false) {

		$this->extKey = Helper::getExtKey();
		$this->api = Helper::initApi();
		$this->settings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		$this->sender = [
			$this->settings['mail']['senderEmail'] => $this->settings['mail']['senderName']
		];
		$this->admin = [
			$this->settings['mail']['adminEmail'] => $this->settings['mail']['adminName']
		];

		$this->view->assign('settings', $this->settings);

		if ($internal) {
			$this->client = $this->api->getClient();
			if (!$this->client)
				$this->redirectToPid($this->settings['pages']['loginPid']); 

		}
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function loginAction() {
		$this->initialize();

		$mode = $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : 'login';
		$client = Session::getValue('client');

		switch ($mode) {

			case 'logout':
				$this->api->clientLogout();
				Session::deleteValue('client');
				$client = false;
				$this->redirectToPid($this->settings['pages']['loginPid']);
				break;

			case 'login':
				if (!$client && $this->request->hasArgument('mail') && $this->request->hasArgument('password')) {
					$postData = [
						'mail' => $this->request->getArgument('mail'),
						'password' => $this->request->getArgument('password')
					];
					$result = $this->api->clientLogin($postData);

					if (array_key_exists('error', $result))
						$this->alert('loginerror', FlashMessage::ERROR, 'error');

					else {
						$client = $this->api->getClient();
						Session::setValue('client', $client);
						$this->redirectToPid($this->settings['pages']['ordersPid']);
					}
				}
				break;
		}

		$this->view->assign('client', $client);
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function lostPasswordAction() {
		$this->initialize();


		if ($this->request->hasArgument('mail')) {
			$postData = [
				'mail' => $this->request->getArgument('mail')
			];

			$result = $this->api->getPasswordHash($postData);

			if (array_key_exists('hash', $result)) {

				$result['link'] = $this->uriBuilder->reset()
 					->setTargetPageUid($this->settings['pages']['loginPid'])
 					->setCreateAbsoluteUri(true)
 					->uriFor(
 						'resetPassword',
 						[
							'mail' => $this->request->getArgument('mail'),
							'hash' => $result['hash']
 						],
 						'Client',
 						$this->extensionName
 					);

				$this->view->assign('result', $result);

				$this->sendTemplateEmail(
					[$this->request->getArgument('mail')],
					$this->sender,
					'Dein Passwort-Reset-Link',
					'PasswordReset',
					$result
				);
			}

		}
	}

	/**
	 * action list
	 *
	 * @return void
	 */
	public function resetPasswordAction() {
		$this->initialize();

		$mail = null;
		$hash = null;

		if ($this->request->hasArgument('mail') && $this->request->hasArgument('hash')) {
			$mail = $this->request->getArgument('mail');
			$hash = $this->request->getArgument('hash');
		}

		$this->view->assign('mail', $mail);
		$this->view->assign('hash', $hash);

		if ($this->request->hasArgument('password') && $this->request->hasArgument('password_repeat')) {

			$data = [
				'mail' => $mail,
				'hash' => $hash,
				'password' => $this->request->getArgument('password'),
				'password_repeat' => $this->request->getArgument('password_repeat')
			];

			if (strlen($data['password']) == 0) {
				$this->alert('passwordLength', FlashMessage::ERROR, 'error');
				return;
			}

			if (strcmp($data['password'], $data['password_repeat']) !== 0) {
				$this->alert('passwordMatch', FlashMessage::ERROR, 'error');
				return;
			}

			$result = $this->api->resetPassword($data);
			if (!$result)
				$this->alert('passwordreseterror', FlashMessage::ERROR, 'error');
			else
				$this->view->assign('result', $result);

		}
		else if (is_null($mail) || is_null($hash))
			$this->alert('resetlinkinvalid', FlashMessage::ERROR, 'error');
	}

	/**
	 * action orders
	 *
	 * @return void
	 */
	public function ordersAction() {
		$this->initialize(true);
		$this->view->assign('orders', $this->api->getClientOrders());
	}

	/**
	 * action orderDetails
	 *
	 * @return void
	 */
	public function orderDetailsAction() {
		$this->initialize(true);
		if (!$this->request->hasArgument('order'))
			$this->redirect(
				'orders', 
				'Client'
			); 

		$this->view->assign('order', $this->api->getOrder([
			'uuid' => $this->request->getArgument('order')
		]));
		$this->view->assign('account', $this->api->getCustomer());
	}

	/**
	 * action profile
	 *
	 * @return void
	 */
	public function profileAction() {
		$this->initialize(true);
		if ($this->request->hasArgument('client')) {
			$data = $client = $this->request->getArgument('client');
			$this->view->assign('client', $client);

			$allowed = explode(',', $this->settings['edit']['allowed']);
			foreach ($data as $field => $value) {
				if (!in_array($field, $allowed) || strlen($value) == 0) {
					unset($data[$field]);
					continue;
				}
			}

			if (isset($data['password']) && strcmp($data['password'], $client['password_repeat']) !== 0) {
				$this->alert('passwordMatch', FlashMessage::ERROR, 'error');
				return;
			}

			$result = $this->api->editClient($data);
			if (isset($result['error']))
				$this->alert('clientupdateerror', FlashMessage::ERROR, 'error');
			else
				$this->alert('clientupdatesuccess', FlashMessage::OK, 'success');
		}
		else
			$this->view->assign('client', $this->client);
	}

	/**
	 * action register
	 *
	 * @return void
	 */
	public function registerAction() {
		$this->initialize();

		$required = array_flip(explode(',', $this->settings['register']['required']));
		$this->view->assign('required', $required);

		if ($this->request->getMethod() == 'POST') {

			// repost prevention
			$temp = Session::getValue('tempClient');
			if (isset($temp['id'])) {
				$this->view->assign('success', true);
				return;
			}

			// check, validate and register client
			if ($this->request->hasArgument('client')) {
				$data = $client = $this->request->getArgument('client');
				$diff = array_diff(array_keys($required), array_keys($client));

				if (count($diff) > 0)
					$this->alert('requiredfieldshacked', FlashMessage::ERROR, 'error');

				$data['username'] = $data['mail'];

				$result = $this->api->registerClient($data);

				if (isset($result['error'])) {

					switch ($result['error']) {
						case 'validation error':
							$this->alert('clientvalidationerror', FlashMessage::ERROR, 'error');
							break;

						default:
							$this->alert('clientcreateerror', FlashMessage::ERROR, 'error');
							break;
					}

					unset($client['password']);
					unset($client['password_repeat']);
					$this->view->assign('client', $client);

				} else {
					Session::setValue('tempClient', $result);
					$maildata = [
						'client' => $result,
						'customer' => $this->api->getCustomer(),
						'settings' => $this->settings,
						'link' => $this->uriBuilder->reset()
	 						->setTargetPageUid($this->settings['pages']['registerPid'])
	 						->setCreateAbsoluteUri(true)
		 					->uriFor(
		 						'activate',
		 						[
									'mail' => $client['mail'],
									'hash' => $result['lostpassword_hash']
		 						],
		 						'Client',
		 						$this->extensionName
		 					)
					];

					$subject = 'Bestätige Deine Registrierung auf ' . $_SERVER['SERVER_NAME'];

					$this->sendTemplateEmail(
						[$client['mail']],
						$this->sender,
						$subject,
						'Register',
						$maildata
					);

					$this->view->assign('success', true);
				}
			}
		}
		else
			$temp = Session::deleteValue('tempClient');
	}

	/**
	 * action activate
	 *
	 * @return void
	 */
	public function activateAction() {
		$this->initialize();
		$mail = null;
		$hash = null;

		if ($this->request->hasArgument('mail') && $this->request->hasArgument('hash')) {
			$mail = $this->request->getArgument('mail');
			$hash = $this->request->getArgument('hash');
		}

		$this->view->assign('mail', $mail);
		$this->view->assign('hash', $hash);

		if (is_null($mail) || is_null($hash))
			$this->alert('activationlinkinvalid', FlashMessage::ERROR, 'error');
		else {
			$result = $this->api->activateClient([
				'mail' => $mail,
				'hash' => $hash
			]);

			if (isset($result['error']))
				$this->alert('activationerror', FlashMessage::ERROR, 'error');
			else
				$this->view->assign('success', true);
		}
	}


	protected function alert($messagecode, $type, $titlecode = null){

		if (is_null($titlecode))
			$titlecode = $messagecode;

		$messageTitle = LocalizationUtility::translate( 'message.' . $titlecode . '.title' , $this->extKey);
		if (is_null($messageTitle))
			$messageTitle = 'Title: ' . $titlecode;

		$messageContent = LocalizationUtility::translate( 'message.' . $messagecode . '.content' , $this->extKey);
		if (is_null($messageContent))
			$messageContent = 'Content: ' . $messagecode;

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

	private function redirectToPid($pid = null) {
		if (is_null($pid) || $pid <= 1)
			return false; 

		$this->redirect(
			null, 
			null,
			null,
			null,
			$pid
		);
	}

}