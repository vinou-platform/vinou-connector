<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\ApiConnector\FileHandler\Pdf;
use \Vinou\ApiConnector\Tools\Helper;

class CacheApiPdfViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	const LOCALDIR = 'typo3temp/vinou_connector/';

    /**
     * @param string $pdf
     * @param string $tstamp
     * @param int $id
     * @return string
     */
	public function render($pdf,$tstamp,$id) {

		$absLocalDir = GeneralUtility::getFileAbsFileName(self::LOCALDIR);
        if(!is_dir($absLocalDir)){
            mkdir($absLocalDir, 0777, true);
        }

        $cachePDFProcess = Pdf::storeApiPDF($pdf,$tstamp,$absLocalDir,$id.'-');
        $fileName = $cachePDFProcess['fileName'];

		return self::LOCALDIR.$fileName;
	}
}