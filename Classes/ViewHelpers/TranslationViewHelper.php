<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class TranslationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $key
     * @param string $countrycode
     * @return string
     */
	public function render($key,$countrycode = 'de') {

        $translation = new \Vinou\Translations\Utilities\Translation($countrycode);

        return $translation->get($countrycode,$key);
	}
}