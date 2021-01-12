<?php
	if (!defined('TYPO3_MODE')) {
		die('Access denied.');
	}

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/Config','Vinou Connector - Config');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/Styles','Vinou Connector - Styles');

	$iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);

	/*
	 * Wines
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Wines',
		'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wines.title',
		'EXT:vinou_connector/Resources/Public/Icons/wines.svg'
	);

	// Add Flexform for Plugin Wines
	$wines = str_replace('_','',$_EXTKEY) . '_wines';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$wines] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($wines, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Wines.xml');

    $iconRegistry->registerIcon(
        'extension-vinouconnector-wines',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/wines.svg']
    );


    /*
	 * Products
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Products',
		'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.products.title',
		'EXT:vinou_connector/Resources/Public/Icons/products.svg'
	);

	// Add Flexform for Plugin Wines
	$products = str_replace('_','',$_EXTKEY) . '_products';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$products] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($products, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Products.xml');

    $iconRegistry->registerIcon(
        'extension-vinouconnector-products',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/products.svg']
    );


    /*
	 * Bundles
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Bundles',
		'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.bundles.title',
		'EXT:vinou_connector/Resources/Public/Icons/bundles.svg'
	);

	// Add Flexform for Plugin Wines
	$bundles = str_replace('_','',$_EXTKEY) . '_bundles';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$bundles] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($bundles, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Bundles.xml');

    $iconRegistry->registerIcon(
        'extension-vinouconnector-bundles',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/bundles.svg']
    );


    /*
	 * Enquiry
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Enquiry',
		'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.enquiry.title',
		'EXT:vinou_connector/Resources/Public/Icons/enquiry.svg'
	);

    $iconRegistry->registerIcon(
        'extension-vinouconnector-enquiry',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/enquiry.svg']
    );

	// Add Flexform for Plugin Enquiry
	$enquiry = str_replace('_','',$_EXTKEY) . '_enquiry';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$enquiry] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($enquiry, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Enquiry.xml');


	/*
	 * Shop
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Shop',
		'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.shop.title',
		'EXT:vinou_connector/Resources/Public/Icons/shop.svg'
	);

	$iconRegistry->registerIcon(
        'extension-vinouconnector-shop',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/shop.svg']
    );

	// Add Flexform for Plugin Shop
	$shop = str_replace('_','',$_EXTKEY) . '_shop';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$shop] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($shop, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Shop.xml');


	/*
	 * Office
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Office',
		'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.office.title',
		'EXT:vinou_connector/Resources/Public/Icons/office.svg'
	);

	// Add Flexform for Plugin Wines
	$office = str_replace('_','',$_EXTKEY) . '_office';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$office] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($office, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Office.xml');

    $iconRegistry->registerIcon(
        'extension-vinouconnector-office',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/office.svg']
    );
