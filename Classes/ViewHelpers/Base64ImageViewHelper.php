<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use \Vinou\ApiConnector\Tools\Helper;

class Base64ImageViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		return Helper::imageToBase64($arguments['url']);
    }


    public function initializeArguments() {
        $this->registerArgument('url', 'string', 'The url to image to convert', true);
    }
}