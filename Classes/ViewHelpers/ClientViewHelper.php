<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ClientViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        return Session::getValue('client');
    }


    public function initializeArguments() {

    }
}