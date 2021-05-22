<?php
namespace Vinou\VinouConnector\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Frontend\Utility\EidUtility;
use \Vinou\ApiConnector\FileHandler\Pdf;
use \Vinou\VinouConnector\Utility\Helper;

class CacheExpertise {

	protected $api;

	public function __construct() {

		header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

		$this->initTYPO3Frontend();
		$this->api = Helper::initApi();

	}

	public function run() {

		if (GeneralUtility::_GET('wineID')) {
			$wine = $this->api->getExpertise(GeneralUtility::_GET('wineID'));
			$wine = $this->api->getWine(GeneralUtility::_GET('wineID'));

			if (Helper::getExtConfValue('cacheExpertise') == 1) {
				$cachePDFProcess = Pdf::storeApiPDF(
					$wine['expertisePdf'],
					$wine['chstamp'],
					Helper::getPdfCacheDir(),
					$wine['id'].'-',
					true
				);
				$redirectURL = '/' . Helper::getPdfCacheDir(false) . $cachePDFProcess['fileName'];
			}

			else
				$redirectURL = 'https://api.vinou.de' . $wine['expertisePdf'];

			header('Location: '.$redirectURL);
		}

		exit;
	}

	private function initTYPO3Frontend() {
		$userObj = EidUtility::initFeUser();
		$pid = (GeneralUtility::_GET('id') ? GeneralUtility::_GET('id') : 1);
		$GLOBALS['TSFE'] = GeneralUtility::makeInstance(
			TypoScriptFrontendController::class,
			$TYPO3_CONF_VARS,
			$pid,
			0,
			true
		);
		$GLOBALS['TSFE']->connectToDB();
		$GLOBALS['TSFE']->fe_user = $userObj;
		$GLOBALS['TSFE']->id = $pid;
		$GLOBALS['TSFE']->determineId();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->getConfigArray();
	}
}

// error_reporting(E_ALL);
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

$eid = GeneralUtility::makeInstance('Vinou\VinouConnector\Eid\CacheExpertise');
echo $eid->run();