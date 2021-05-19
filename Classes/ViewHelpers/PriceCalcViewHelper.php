<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PriceCalcViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

    	switch ($arguments['target']) {
			case 'gross':
				return ($arguments['price'] * $arguments['tax']) * $arguments['quantity'];
				break;

			case 'net':
				return ($arguments['price'] / $arguments['tax']) * $arguments['quantity'];
				break;

			default:
				//calculate only quantity
				return $arguments['price'] * $arguments['quantity'];
				break;
		}
    }


    public function initializeArguments() {
        $this->registerArgument('price', 'string', 'The item price to calculate', true);
        $this->registerArgument('target', 'string', 'The target mode which price should be calculated', false, 'self');
        $this->registerArgument('tax', 'float', 'The tax as absolute factor default ist 1.19', false, 1.19);
        $this->registerArgument('quantity', 'float', 'The quantity of element to multiplicate', false, 1);
    }
}