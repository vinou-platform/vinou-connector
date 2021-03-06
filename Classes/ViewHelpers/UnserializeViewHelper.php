<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class UnserializeViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

    	return unserialize($arguments['string']);
    }


    public function initializeArguments() {
        $this->registerArgument('string', 'string', 'The string to unserialize', true);
    }
}