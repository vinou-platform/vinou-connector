<?php
namespace Vinou\VinouConnector\ViewHelpers;

class ExplodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $string
     * @return array
     */
	public function render($string) {
        return explode(',', $string);
	}
}