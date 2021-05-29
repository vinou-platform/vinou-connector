<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\ApiConnector\FileHandler\Pdf;

class CacheApiPdfViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $cachePDFProcess = Pdf::storeApiPDF($arguments['pdf'], $arguments['tstamp'], Helper::getPdfCacheDir(), $arguments['id'].'-');
        return Helper::getPdfCacheDir(false) . $cachePDFProcess['fileName'];

    }

    public function initializeArguments() {
        $this->registerArgument('pdf', 'string', 'The api pdf string', true);

        $this->registerArgument('tstamp', 'string', 'The timestamp of api object to use for get cache significance');

        $this->registerArgument('id', 'integer', 'The id of api object to use for get cache significance', true);
    }
}