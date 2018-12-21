<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/Configuration/TSconfig/mod.wizard.ts">');
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/Configuration/TSconfig/templateLayouts.ts">');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Interfrog.'.$_EXTKEY,
	'Wines',
	array(
		'Wines' => 'list, detail',
	),
	// non-cacheable actions
	array(
		'Wines' => 'detail',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Interfrog.'.$_EXTKEY,
	'Enquiry',
	array(
		'Enquiry' => 'form, submitRequest',
	),
	// non-cacheable actions
	array(
		'Enquiry' => 'submitRequest',
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Interfrog.'.$_EXTKEY,
	'Shop',
	array(
		'Shop' => 'basket, order, finish',
	),
	// non-cacheable actions
	array(
		'Shop' => 'basket, order, finish',
	)
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['cacheExpertise'] = 'EXT:'.$_EXTKEY.'/Classes/Utility/EidCacheExpertise.php';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Vinou\\VinouConnector\\Command\\CacheExpertiseTask'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tasks.cacheexpertise.title',
    'description' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tasks.cacheexpertise.description',
    'additionalFields' => 'Vinou\VinouConnector\Command\CacheExpertiseTaskAdditionalFieldProvider'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Vinou\\VinouConnector\\Command\\CacheImagesTask'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tasks.cacheimages.title',
    'description' => 'LLL:EXT:'.$_EXTKEY.'/Resources/Private/Language/locallang.xlf:tasks.cacheimages.description',
    'additionalFields' => 'Vinou\VinouConnector\Command\CacheImagesTaskAdditionalFieldProvider'
);

?>