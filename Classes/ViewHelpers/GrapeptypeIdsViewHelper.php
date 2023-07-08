<?php
namespace Vinou\VinouConnector\ViewHelpers;

class GrapeptypeIdsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param array $ids
     * @return string
     */
	public function render($ids) {

        if (is_array($ids))
            return implode(', ',$ids);

        if (is_numeric($ids))
            return $ids;

        return null;
	}
}