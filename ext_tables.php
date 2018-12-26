<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript','Vinou Connector');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Wines',
	'Vinou Weinliste',
	'EXT:vinou_connector/Resources/Public/Icons/wines.png'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Enquiry',
	'Vinou Anfrageformular',
	'EXT:vinou_connector/Resources/Public/Icons/enquiry.png'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Shop',
	'Vinou Shop',
	'EXT:vinou_connector/Resources/Public/Icons/shop.png'
);

// Add Flexform for Plugin Products
$wines = str_replace('_','',$_EXTKEY) . '_wines';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$wines] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($wines, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_wines.xml');

// Add Flexform for Plugin Products
$enquiry = str_replace('_','',$_EXTKEY) . '_enquiry';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$enquiry] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($enquiry, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_enquiry.xml');

// Add Flexform for Plugin Products
$shop = str_replace('_','',$_EXTKEY) . '_shop';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$shop] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($shop, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_shop.xml');
