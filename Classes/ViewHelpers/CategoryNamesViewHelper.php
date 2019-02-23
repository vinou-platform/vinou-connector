<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class CategoryNamesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param array $categories
     * @return string
     */
	public function render($categories) {

		$names = [];
		foreach ($categories as $key => $category) {
			array_push($names,$category['name']);
		}

        return implode(', ',$names);
	}
}