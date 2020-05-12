<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \Vinou\Translations\Utilities\Translation;

class TranslationViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $key
     * @param string $countrycode
     * @return string
     */
	public function render($key,$countrycode = 'de') {

        $translation = new Translation($countrycode);

        return $translation->get($countrycode,$key);
	}
}