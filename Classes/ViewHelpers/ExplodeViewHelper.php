<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ExplodeViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

    	$delimiter = $arguments['delimiter'] ?? ',';
		return explode($delimiter, $arguments['string']);
    }


    public function initializeArguments() {
        $this->registerArgument('string', 'string', 'The string to explode', true);
        $this->registerArgument('delimiter', 'string', 'The delimiter to explode');
    }
}