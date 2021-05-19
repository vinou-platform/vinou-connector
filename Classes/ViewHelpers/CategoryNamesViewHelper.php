<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CategoryNamesViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
    	$names = [];
		foreach ($arguments['categories'] as $key => $category) {
			array_push($names, $category['name']);
		}

        return implode(', ',$names);
    }


    public function initializeArguments() {
        $this->registerArgument('categories', 'array', 'The categories of given item', true);
    }
}