<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class MergearrayViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $array
     * @return string
     */
	public function render($array) {
		$return = [];

		foreach ($array as $entry) {
			$return = array_merge($return, $entry);
		}

		sort($return);

		return array_unique($return);
	}
}