<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\ApiConnector\FileHandler\Images;

class CacheApiImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	const LOCALDIR = 'typo3temp/vinou_connector/';

    /**
     * @param string $image
     * @param string $tstamp
     * @return string
     */
	public function render($image,$tstamp) {

		$absLocalDir = GeneralUtility::getFileAbsFileName(self::LOCALDIR);
        if(!is_dir($absLocalDir)){
            mkdir($absLocalDir, 0777, true);
        }

        $cacheImageProcess = Images::storeApiImage($image,$tstamp,$absLocalDir);
        $fileName = $cacheImageProcess['fileName'];

		return self::LOCALDIR.$fileName;
	}
}