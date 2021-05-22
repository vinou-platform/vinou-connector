<?php
namespace Vinou\VinouConnector\Hooks;

use \Vinou\ApiConnector\Api;
use \Vinou\Translations\Utilities\Translation;
use \Vinou\VinouConnector\Utility\Helper;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;


class TCA {

	protected $api = null;
	protected $translations = null;

	public function init() {

		$this->api = Helper::initApi();
		$this->translations = new Translation();

	}

	/**
	 * @param array $config
	 */
	public function getWineries(array &$config) {
		$this->init();
		$wineries = $this->api->getWineriesAll();
		foreach ($wineries as $winery) {
			array_push($config['items'], array($winery['company'],$winery['id']));
		}
	}

	/**
	 * @param array $config
	 */
	public function getMerchants(array &$config) {
		$this->init();
		$merchants = $this->api->getMerchantsAll();
		foreach ($merchants as $merchant) {
			array_push($config['items'], array($merchant['company'],$merchant['id']));
		}
	}

	/**
	 * user template layout
	 * @param array $config
	 */
	public function user_templateLayout(array &$config) {
		$templateUtility = GeneralUtility::makeInstance('Vinou\\VinouConnector\\Utility\\TemplateLayoutUtility');
		$templateLayouts = $templateUtility->getAvailableTemplateLayouts($config['row']['pid']);
		foreach ($templateLayouts as $layout) {
			$additionalLayout = array(
				$GLOBALS['LANG']->sL($layout[0], TRUE),
				$layout[1]
			);
			array_push($config['items'], $additionalLayout);
		}
	}

	/**
	 * fetch vinou Categories
	 * @param array $config
	 */
	public function vinouCategories(array &$config) {
		$this->init();
		$categories = $this->api->getCategoriesAll();
		foreach ($categories as $category) {
			array_push($config['items'], array($category['name'],$category['id']));
		}
	}

	/**
	 * fetch vinou Types
	 * @param array $config
	 */
	public function vinouTypes(array &$config) {
		$this->init();
		$winetypes = $this->translations->get($GLOBALS['BE_USER']->uc['lang'], 'winetypes');
		if (!$winetypes)
			$winetypes = $this->translations->get('en', 'winetypes');
		foreach ($winetypes as $key => $label) {
			array_push($config['items'], array($label,$key));
		}
	}
}
