<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\ApiConnector\FileHandler\Images;

class CacheApiImageViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $cacheImageProcess = Images::storeApiImage($arguments['image'], $arguments['tstamp'] ?? null, Helper::getImageCacheDir());
        return Helper::getImageCacheDir(false) . $cacheImageProcess['fileName'];

    }

    public function initializeArguments() {
        $this->registerArgument('image', 'string', 'The api image string', true);

        $this->registerArgument('tstamp', 'string', 'The timestamp of api object to use for get cache significance');
    }
}