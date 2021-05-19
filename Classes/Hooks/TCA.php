<?php
namespace Vinou\VinouConnector\Hooks;

use \Vinou\ApiConnector\Api;
use \Vinou\VinouConnector\Utility\Helper;
use \TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;


class TCA {

	protected $extConf = null;
	protected $api = null;

	public function init() {

		$this->extConf = Helper::getExtConf();
		$this->api = Helper::initApi();

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
		$winetypes = json_decode(file_get_contents(Helper::getLLPath() . 'winetypes.json'),true);
		isset($winetypes[$GLOBALS['BE_USER']->uc['lang']]) ? $winetypes = $winetypes[$GLOBALS['BE_USER']->uc['lang']] : $winetypes = $winetypes['en'];
		foreach ($winetypes as $key => $label) {
			array_push($config['items'], array($label,$key));
		}
	}
}
