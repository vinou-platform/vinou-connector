<?php
namespace Interfrog\Vinou\ViewHelpers;

class UnserializeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param string $string
	 * @return array content
	 */
    public function render($string) {

    	$outPutContent = unserialize($string);
        return $outPutContent;
    }
}