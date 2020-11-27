<?php
namespace Vinou\VinouConnector\Utility;


/**
* Shop
*/
class Shop {

    public static function calcCardQuantity($card) {
        $quantity = 0;
        foreach ($card as $item) {
            if ($item['item_type'] == 'bundle')
                $quantity = $quantity + $item['quantity'] * $item['item']['package_quantity'];
            else
                $quantity = $quantity + $item['quantity'];
        }
        return $quantity;
    }

    public static function quantityIsAllowed($quantity, $settings, $retString = false) {

        if (array_key_exists('minBasketSize', $settings) && $quantity < $settings['minBasketSize'])
            return $retString ? 'minBasketSize' : false;

        if (array_key_exists('packageSteps', $settings)) {
            $steps = $settings['packageSteps'];
            if (is_string($steps))
                $steps = array_map('trim', explode(',', $steps));

            if (array_key_exists('factor', $steps)) {
                $factor = (int)$steps['factor'];
                if ($quantity % $factor != 0)
                    return $retString ? 'packageSteps' : false;

                unset($steps['factor']);
            }

            if (count($steps) > 0 && !in_array($quantity, array_values($steps)))
                return $retString ? 'packageSteps' : false;

        }

        return $retString ? 'valid' : true;
    }

}