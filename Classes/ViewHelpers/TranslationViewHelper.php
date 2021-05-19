<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use \Vinou\Translations\Utilities\Translation;

class TranslationViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

		$translation = new Translation($arguments['countrycode']);
        return $translation->get($arguments['countrycode'], $arguments['key']);
    }


    public function initializeArguments() {
        $this->registerArgument('key', 'string', 'The key to translate', true);
        $this->registerArgument('countrycode', 'string', 'The internaltional countrycode of target language', false, 'de');
    }
}