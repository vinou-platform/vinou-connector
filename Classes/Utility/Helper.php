<?php
namespace Vinou\VinouConnector\Utility;

use \Vinou\ApiConnector\Api;
use \TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;


/**
* Helper
*/
class Helper {

	protected static $extKey = 'vinou_connector';
	protected static $llPath = 'Resources/Private/Language/';
	protected static $cacheDir = 'typo3temp/vinou/';

	public static function getExtKey() {
		return self::$extKey;
	}

	public static function getExtConf() {
		return (array)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::$extKey);
	}

	public static function getExtConfValue($key) {
		$extConf = self::getExtConf();
		return $extConf[$key] ?? false;
	}

	public static function getLLPath() {
		return ExtensionManagementUtility::extPath(self::$extKey).self::$llPath;
	}

	public static function initApi() {
		$extConf = self::getExtConf();

		self::ensureCacheDir();

		$dev = false;
		if ($extConf['vinouMode'] == 'dev') {
			$dev = true;
		}

		return new Api (
			$extConf['token'],
			$extConf['authId'],
			true,
			$dev
		);
	}

	public static function ensureCacheDir() {

		$extConf = self::getExtConf();
		$cacheDir = self::$cacheDir;

	    if (isset($extConf['cachingFolder']))
	    	$cacheDir = $extConf['cachingFolder'];

		return self::ensureDir($cacheDir);
	}

	public static function ensureDir ($dir = null, $secure = false, $chmod = 0777) {

		if (substr($dir, -1) != '/')
			$dir .= '/';

		$dir = GeneralUtility::getFileAbsFileName($dir);
		if(!is_dir($dir))
			mkdir($dir, $chmod, true);

		if ($secure && !is_file($dir .'/.htaccess'))
			file_put_contents($dir .'/.htaccess', 'Deny from all');

		return $dir;
	}

}
