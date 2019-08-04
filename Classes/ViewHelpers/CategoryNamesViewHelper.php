<?php
namespace Vinou\VinouConnector\ViewHelpers;

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