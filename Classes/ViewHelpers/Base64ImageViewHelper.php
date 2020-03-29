<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \Vinou\ApiConnector\Tools\Helper;

class Base64ImageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $url
     * @return string
     */
	public function render($url) {

		Helper::imageToBase64($url);
	}
}