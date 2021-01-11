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
			'Wines' => 'list, detail',
		)
	);

	/**
     * plugin for products actions
     */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Vinou.'.$_EXTKEY,
		'Products',
		array(
			'Products' => 'list, detail',
		),
		// non-cacheable actions
		array(
			'Products' => 'list, detail',
		)
	);

	/**
     * plugin for bundles actions
     */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Vinou.'.$_EXTKEY,
		'Bundles',
		array(
			'Bundles' => 'list, detail',
		),
		// non-cacheable actions
		array(
			'Bundles' => 'list, detail',
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
			'Enquiry' => 'form, submitRequest',
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
     * plugin for shop actions
     */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Vinou.'.$_EXTKEY,
		'Office',
		array(
			'Office' => 'register',
		),
		// non-cacheable actions
		array(
			'Office' => 'register',
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

    /**
     * eID to manage ajax actions
     */
    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['vinouActions'] =
        'EXT:'.$_EXTKEY.'/Classes/Eid/AjaxActions.php';


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