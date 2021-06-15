<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class FilterViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param string $items
     * @param string $property
     * @param string $value
     * @param boolean $first
     * @return string
     */
	public function render($items, $property = null, $value = null, $first = false) {

		if (is_null($property))
            return $items;

        $return = [];

        if (is_array($items) && count($items) > 0) {

            if (str_contains($property, ',') && is_null($value))
                $property = explode(',', $property);

            $return = array_filter($items, function($item, $key) use ($property, $value) {
                if (is_array($property))
                    return in_array($key, $property);
                else
                    return $item[$property] == $value;
            }, ARRAY_FILTER_USE_BOTH);
        }

        if (empty($return))
            return false;

        return $first ? array_shift(array_values($return)) : $return;
	}
}