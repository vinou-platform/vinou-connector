<?php
namespace Interfrog\Vinou\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class CacheApiImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	const LOCALDIR = 'typo3temp/vinou/';

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
        $fileName = \Interfrog\Vinou\Utility\Images::storeApiImage($image,$absLocalDir,$tstamp);

		return self::LOCALDIR.$fileName;
	}
}