<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/Configuration/TSconfig/templateLayouts.ts">');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Interfrog.'.$_EXTKEY,
	'Wines',
	array(
		'Wines' => 'list, basket, order, finish, detail',
	),
	// non-cacheable actions
	array(
		'Wines' => 'basket, order, finish',
	)
);


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Interfrog\\Vinou\\Command\\CacheApiFilesTask'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.title',
    'description' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tasks.cacheapifiles.description',
    'additionalFields' => 'Interfrog\Vinou\Command\CacheApiFilesTaskAdditionalFieldProvider'
);

?>