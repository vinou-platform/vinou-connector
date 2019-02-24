<?php
namespace Vinou\VinouConnector\ViewHelpers;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;

class SortViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

    /**
     * @param array $objects
     * @param string $property
     * @return string
     */
	public function render($objects,$property) {


		$sortArray = array();

		foreach($objects as $item){
			foreach($item as $key=>$value){
		   		if(!isset($sortArray[$key])){
		        	$sortArray[$key] = array();
		    	}
		    	$sortArray[$key][] = $value;
			}
		}

		array_multisort($sortArray[$property],SORT_ASC,$objects);

        return $objects;
	}
}