<?php
namespace Vinou\VinouConnector\Controller;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use \Vinou\ApiConnector\PublicApi;
use \Vinou\VinouConnector\Utility\Helper;

class OfficeController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
   * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
   */
  protected $configurationManager;

	/**
	 * @var Api Api Endpoint.
	 */
	protected $api;

	protected $registrationDir = 'vinou/registrations';

	protected $detailPid;
	protected $backPid;


	public function initialize() {

		$this->api = Helper::initApi();

		$settings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		$this->settings = $settings;
		$this->settings['currentPage'] = $GLOBALS['TSFE']->id;

		$this->registrationDir = Helper::ensureDir($this->registrationDir, true);

	}

	/**
	 * action register
	 *
	 * @return void
	 */
	public function registerAction() {
		$this->initialize();
		$required = $this->switchFieldValues(explode(',', $this->settings['register']['required']));
		$required['mandatorySign'] = $this->settings['mandatorySign'];
		$this->view->assign('required', $required);
		$this->settings['source'] = $_SERVER['SERVER_NAME'] . '/' . $_SERVER['REQUEST_URI'];

		$arguments = $this->request->getArguments();
		if ($this->request->getMethod() == 'POST' && count($arguments)) {
			file_put_contents($this->registrationDir .'/registration-'.time().'.json', json_encode($customer));

			$api = new PublicApi();
			$registration = $api->register($arguments['customer']);

			if ($registration) {
				$this->view->assign('registration', TRUE);
				$finishPid = (int)$this->settings['finishPID'];
				if ($finishPid > 1) {
					$uriBuilder = $this->uriBuilder;
					$uri = $uriBuilder
					  ->setTargetPageUid($finishPid)
					  ->build();
					$this->redirectToUri($uri, 0, 404);
				}
			}
			else {
				$this->Alert('error','registrationError',FlashMessage::ERROR);
				$this->view->assign('customer', $arguments['customer']);
			}

		}

		$this->view->assign('settings', $this->settings);

	}

	private function switchFieldValues($fields){
		foreach ($fields as $key => $field) {
			$fields[$field] = true;
			unset($fields[$key]);
		}
		return $fields;
	}

	protected function Alert($titlecode, $messagecode, $type){

		$messageTitle = LocalizationUtility::translate( 'message.title.'.$titlecode , Helper::getExtKey());
		$messageContent = LocalizationUtility::translate( 'message.content.'.$messagecode , Helper::getExtKey());

		if (substr($_SERVER['HTTP_HOST'],-4,4) == '.dev') {
			$messageContent .= ' '.$messagecode;
		}

		$msg = GeneralUtility::makeInstance(FlashMessage::class,
			$messageContent,
			$messageTitle,
			$type,
			TRUE
		);
		$this->controllerContext->getFlashMessageQueue()->enqueue($msg);
	}

}