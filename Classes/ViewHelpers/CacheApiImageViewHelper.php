<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\ApiConnector\FileHandler\Images;

class CacheApiImageViewHelper extends AbstractViewHelper {

	const LOCALDIR = 'vinou/cache/images/';

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        if (!$arguments['image'])
            return;

        $cacheDir = 'vinou/cache/images/';
        $absLocalDir = Helper::ensureDir($cacheDir);
        $cacheImageProcess = Images::storeApiImage($arguments['image'], $arguments['tstamp'] ?? null, $absLocalDir);

        return $cacheDir . $cacheImageProcess['fileName'];

    }

    public function initializeArguments() {
        $this->registerArgument('image', 'string', 'The api image string', true);

        $this->registerArgument('tstamp', 'string', 'The timestamp of api object to use for get cache significance', true);
    }
}