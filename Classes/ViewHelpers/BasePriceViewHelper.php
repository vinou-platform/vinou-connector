<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class BasePriceViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		return $arguments['price'] / $arguments['size'];
    }


    public function initializeArguments() {
        $this->registerArgument('price', 'string', 'The item price', true);
        $this->registerArgument('size', 'string', 'The item size', true);
    }
}