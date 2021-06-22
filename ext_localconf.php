<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

	$extKey = \Vinou\VinouConnector\Utility\Helper::getExtKey();

	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.'/Configuration/TSconfig/mod.wizard.tsconfig">');
	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$extKey.'/Configuration/TSconfig/templateLayouts.tsconfig">');

	$plugins = [
		'Wines' => 'list, detail',
		'Products' => 'list, detail',
		'Bundles' => 'list, detail',
		'Shop' => 'list, basket, order, finish, topseller',
		'Office' => 'register',
		'Wineries' => 'list, detail',
		'Merchants' => 'list, detail',
		'Client' => 'login, lostPassword, resetPassword, activate, profile, orders, orderDetails'
	];


	foreach ($plugins as $name => $actions) {

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'Vinou.'.$extKey,
			$name,
			[$name => $actions],
			// non-cacheable actions
			[$name => $actions]
		);

	}

	/**
	 * eID to cache expertises
	 */
	$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['cacheExpertise'] = 'EXT:'.$extKey.'/Classes/Eid/CacheExpertise.php';

	/**
	 * eID to manage ajax actions
	 */
	$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['vinouActions'] = 'EXT:'.$extKey.'/Classes/Eid/AjaxActions.php';


	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Vinou\\VinouConnector\\Command\\CacheExpertiseTask'] = array(
		'extension' => $extKey,
		'title' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheexpertise.title',
		'description' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheexpertise.description',
		'additionalFields' => 'Vinou\VinouConnector\Command\CacheExpertiseTaskAdditionalFieldProvider'
	);

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Vinou\\VinouConnector\\Command\\CacheImagesTask'] = array(
		'extension' => $extKey,
		'title' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheimages.title',
		'description' => 'LLL:EXT:'.$extKey.'/Resources/Private/Language/locallang.xlf:tasks.cacheimages.description',
		'additionalFields' => 'Vinou\VinouConnector\Command\CacheImagesTaskAdditionalFieldProvider'
	);

?>