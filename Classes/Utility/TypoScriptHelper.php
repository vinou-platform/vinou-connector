<?php
namespace Vinou\VinouConnector\Utility;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;


/**
* Shop
*/
class TypoScriptHelper {

	public static function extractSettings($pluginKey) {

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $fullTS = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        if (!array_key_exists($pluginKey . '.', $fullTS['plugin.']))
            return false;

        if (!array_key_exists('settings.', $fullTS['plugin.'][$pluginKey . '.']))
            return false;


        return self::removeTSSuffix($fullTS['plugin.'][$pluginKey . '.']['settings.']);

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