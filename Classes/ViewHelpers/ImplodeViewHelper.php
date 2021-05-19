<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ImplodeViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

    	$glue = $arguments['glue'] ?? ', ';
		return explode($glue, $arguments['items']);
    }


    public function initializeArguments() {
        $this->registerArgument('items', 'array', 'The items to implode', true);
        $this->registerArgument('glue', 'string', 'The glue to implode');
    }
}