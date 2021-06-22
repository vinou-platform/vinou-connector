<?php
namespace Vinou\VinouConnector\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FilterViewHelper extends AbstractViewHelper {

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        $return = [];
        $items = $arguments['items'];
        $property = $arguments['property'];
        $value = $arguments['items'];

        if (count($items) > 0) {

            if (str_contains($property, ','))
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

        return $arguments['first'] ? array_shift(array_values($return)) : $return;
    }


    public function initializeArguments() {
        $this->registerArgument('items', 'array', 'The items to filter', true);
        $this->registerArgument('property', 'string', 'The property to filter', true);
        $this->registerArgument('value', 'string', 'The valute to filter', true);
        $this->registerArgument('first', 'boolean', 'Set to return only first item');
    }
}