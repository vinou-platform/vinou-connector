<?php
namespace Vinou\VinouConnector\Utility;

use \TYPO3\CMS\Core\Utility\GeneralUtility;


/**
* Shop
*/
class TypoScriptHelper {

	public static function extractSettings($pluginKey) {

        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);


        if (!array_key_exists($pluginKey . '.', $extbaseFrameworkConfiguration['plugin.']))
            return false;

        if (!array_key_exists('settings.', $extbaseFrameworkConfiguration['plugin.'][$pluginKey . '.']))
            return false;


        return self::removeTSSuffix($extbaseFrameworkConfiguration['plugin.'][$pluginKey . '.']['settings.']);

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