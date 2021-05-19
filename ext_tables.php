<?php

	if (!defined('TYPO3_MODE')) {
		die('Access denied.');
	}

	$extKey = 'vinou_connector';

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey,'Configuration/TypoScript/Config','Vinou Connector - Config');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey,'Configuration/TypoScript/Styles','Vinou Connector - Styles');

	$iconRegistry =
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);


    $plugins = [
    	'Wines',
    	'Products',
    	'Bundles',
    	'Shop',
    	'Office',
    	'Wineries',
    	'Merchants'
    ];

    foreach ($plugins as $pluginName) {

		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
			$extKey,
			$pluginName,
			'LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.' . strtolower($pluginName) . '.title',
			'EXT:vinou_connector/Resources/Public/Icons/' . strtolower($pluginName) . '.svg'
		);

		$pluginString = str_replace('_','',$extKey) . '_' . strtolower($pluginName);
		$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginString] = 'pi_flexform';
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
		    $pluginString,
		    'FILE:EXT:' . $extKey . '/Configuration/FlexForms/' . $pluginName . '.xml'
		);

	    $iconRegistry->registerIcon(
	        'extension-vinouconnector-' . strtolower($pluginName) ,
	        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
	        ['source' => 'EXT:vinou_connector/Resources/Public/Icons/' . strtolower($pluginName) . '.svg']
	    );

    }
