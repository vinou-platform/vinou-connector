<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class BasePriceViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $price
     * @param string $size
     * @return string
     */
	public function render($price, $size) {

		return (float)$size > 0 ? $price / $size : $price;
	}
}