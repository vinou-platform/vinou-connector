<?php
	if (!defined('TYPO3_MODE')) {
		die ('Access denied.');
	}

	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/Configuration/TSconfig/mod.wizard.ts">');
	TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:'.$_EXTKEY.'/Configuration/TSconfig/templateLayouts.ts">');

	/**
     * plugin for wines actions
     */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Vinou.'.$_EXTKEY,
		'Wines',
		array(
			'Wines' => 'list, detail',
		),
		// non-cacheable actions
		array(
			'Wines' => 'detail',
		)
	);

	/**
     * plugin for enquiry actions
     */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Vinou.'.$_EXTKEY,
		'Enquiry',
		array(
			'Enquiry' => 'form, submitRequest',
		),
		// non-cacheable actions
		array(
			'Enquiry' => 'submitRequest',
		)
	);

	/**
     * plugin for shop actions
     */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Vinou.'.$_EXTKEY,
		'Shop',
		array(
			'Shop' => 'list, basket, order, finish',
		),
		// non-cacheable actions
		array(
			'Shop' => 'list, basket, order, finish',
		)
	);

	/**
     * eID to cache expertises
     */
	$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['cacheExpertise'] = 'EXT:'.$_EXTKEY.'/Classes/Eid/CacheExpertise.php';

	/**
     * eID to create token for login the current client
     */
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['clientLogin'] =
        'EXT:'.$_EXTKEY.'/Classes/Eid/ClientLogin.php';

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