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

        return (float)$arguments['size'] > 0 ? $arguments['price'] / $arguments['size'] : $arguments['price'];
    }


    public function initializeArguments() {
        $this->registerArgument('price', 'string', 'The item price', true);
        $this->registerArgument('size', 'string', 'The item size', true);
    }
}