<?php
namespace Vinou\VinouConnector\Utility;

use \TYPO3\CMS\Core\Site\Entity\Site;
use \TYPO3\CMS\Core\TypoScript\TemplateService;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\RootlineUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;


/**
* Shop
*/
class TypoScriptHelper {

	public static function extractSettings($pluginKey, $request = null) {

        $fullTS = is_null($request) ? self::loadDefault() : self::fetchTSByRequest($request);

        if (!is_array($fullTS) || count($fullTS) == 0)
            return false;

        if (!array_key_exists($pluginKey . '.', $fullTS['plugin.']))
            return false;

        if (!array_key_exists('settings.', $fullTS['plugin.'][$pluginKey . '.']))
            return false;


        return self::removeTSSuffix($fullTS['plugin.'][$pluginKey . '.']['settings.']);

    }

    public static function loadDefault() {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        return $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
    }

    public static function fetchTSByRequest($request) {
        $site = $request->getAttribute('site');
        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $site->getRootPageId());
        $rootline = $rootlineUtility->get();
        $templateService = GeneralUtility::makeInstance(TemplateService::class);
        $templateService->tt_track = 0;
        $templateService->runThroughTemplates($rootline);
        $templateService->generateConfig();
        return $templateService->setup;
    }

    public static function removeTSSuffix($array) {

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $newKey = str_replace('.', '', $key);
                $array[$newKey] = self::removeTSSuffix($array[$key]);
                unset($array[$key]);
            }
        }

        return $array;

    }

}