<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\ApiConnector\Session\Session;

class ClientViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @return array
     */
	public function render() {

		return Session::getValue('client');
	}
}