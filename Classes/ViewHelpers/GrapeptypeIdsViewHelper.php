<?php
namespace Vinou\VinouConnector\ViewHelpers;

class GrapeptypeIdsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param array $ids
     * @return string
     */
	public function render($ids) {

        return implode(', ',$ids);
	}
}