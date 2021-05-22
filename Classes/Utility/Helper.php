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
	protected static $cacheDir = 'typo3temp/vinou';
	protected static $imagesCacheDir = 'vinou/cache/images';
	protected static $ordersCacheDir = 'vinou/orders';

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
		return ExtensionManagementUtility::extPath(self::$extKey) . self::$llPath;
	}

	public static function getImageCacheDir($absolute = true) {
		return self::ensureDir($absolute, self::$imagesCacheDir);
	}

	public static function getPdfCacheDir($absolute = true) {
		return self::ensureDir($absolute, self::$cacheDir);
	}

	public static function getOrderCacheDir($absolute = true) {
		return self::ensureDir($absolute, self::$ordersCacheDir);
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

	public static function ensureCacheDir($absolute = true) {

		$extConf = self::getExtConf();
		$cacheDir = self::$cacheDir;

		if (isset($extConf['cachingFolder']))
			$cacheDir = $extConf['cachingFolder'];

		return self::ensureDir($absolute, $cacheDir);
	}

	public static function ensureDir ($absolute = true, $dir = null, $secure = false, $chmod = 0777) {

		$relDir = self::normalizeDirPath($dir);

		$dir = GeneralUtility::getFileAbsFileName($relDir);
		if(!is_dir($dir))
			mkdir($dir, $chmod, true);

		if ($secure && !is_file($dir .'/.htaccess'))
			file_put_contents($dir .'/.htaccess', 'Deny from all');

		return $absolute ? $dir : $relDir;
	}

	public static function normalizeDirPath($dir) {
		if (substr($dir, -1) != '/')
			$dir .= '/';
		return $dir;
	}

}
