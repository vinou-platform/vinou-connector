<?php
	if (!defined('TYPO3_MODE')) {
		die('Access denied.');
	}

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/Config','Vinou Connector - Config');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/Styles','Vinou Connector - Styles');

	/*
	 * Wines
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Wines',
		'Vinou Weinliste',
		'EXT:vinou_connector/Resources/Public/Icons/wines.svg'
	);

	// Add Flexform for Plugin Wines
	$wines = str_replace('_','',$_EXTKEY) . '_wines';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$wines] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($wines, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_wines.xml');

    $iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
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
		'Vinou Produkte',
		'EXT:vinou_connector/Resources/Public/Icons/products.svg'
	);

	// Add Flexform for Plugin Wines
	$products = str_replace('_','',$_EXTKEY) . '_products';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$products] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($products, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_products.xml');

    $iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'extension-vinouconnector-products',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/products.svg']
    );


    /*
	 * Enquiry
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Enquiry',
		'Vinou Anfrageformular',
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
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($enquiry, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_enquiry.xml');


	/*
	 * Shop
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Shop',
		'Vinou Shop',
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
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($shop, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_shop.xml');
