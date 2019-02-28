<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class PriceCalcViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $price
     * @param string $target
     * @param string $tax
     * @param string $quantity
     * @return string
     */
	public function render($price, $target = 'self', $tax = 1.19, $quantity = 1) {

		switch ($target) {
			case 'gross':
				return ($price * $tax) * $quantity;
				break;

			case 'net':
				return ($price / $tax) * $quantity;
				break;

			default:
				//calculate only quantity
				return $price * $quantity;
				break;
		}
	}
}