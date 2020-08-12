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
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($wines, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Wines.xml');

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
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($products, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Products.xml');

    $iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'extension-vinouconnector-products',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/products.svg']
    );

    /*
	 * Facebook
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Facebook',
		'Vinou Facebook',
		'EXT:vinou_connector/Resources/Public/Icons/facebook.svg'
	);

	// Add Flexform for Plugin Wines
	$facebook = str_replace('_','',$_EXTKEY) . '_facebook';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$facebook] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($facebook, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Facebook.xml');

    $iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'extension-vinouconnector-facebook',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/facebook.svg']
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
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($enquiry, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Enquiry.xml');


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
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($shop, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Shop.xml');


	/*
	 * Office
	 */

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		$_EXTKEY,
		'Office',
		'Vinou Office',
		'EXT:vinou_connector/Resources/Public/Icons/office.svg'
	);

	// Add Flexform for Plugin Wines
	$office = str_replace('_','',$_EXTKEY) . '_office';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$office] = 'pi_flexform';
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($office, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Office.xml');

    $iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'extension-vinouconnector-office',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/office.svg']
    );
