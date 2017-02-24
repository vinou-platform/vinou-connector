<?php
namespace Interfrog\Vinou\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class CacheApiPdfViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	protected $apiUrl = 'https://api.vinou.de';
	protected $localDir = 'typo3temp/vinou/';

    /**
     * @param string $pdf
     * @param string $tstamp
     * @param int $id
     * @return string
     */
	public function render($pdf,$tstamp,$id) {

		$absLocalDir = GeneralUtility::getFileAbsFileName($this->localDir);
        if(!is_dir($absLocalDir)){
            mkdir($absLocalDir, 0777, true);
        }
        $fileName = \Interfrog\Vinou\Utility\Pdf::storeApiPDF($pdf,$absLocalDir,$id.'-');

		return $this->localDir.$fileName;
	}
}