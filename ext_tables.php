<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript','Vinou Connector');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Interfrog.'.$_EXTKEY,
	'Wines',
	'Vinou Connector'
);

// Add Flexform for Plugin Products
$coupons = str_replace('_','',$_EXTKEY) . '_wines';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$coupons] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($coupons, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_wines.xml');
