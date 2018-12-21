<?php
namespace Vinou\VinouConnector\Domain\Userfuncs;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Vinou GmbH, christian@vinou.de
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package vinou
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

class Tca {

        public function showValues($PA, $fObj) {

                $submitted = unserialize($PA['itemFormElValue']);
                $returnString = '<table border="1" cellspacing="0" cellpadding="0"><tr><th style="padding:5px 10px">Feld</th><th style="padding:5px 10px;">Wert</th></tr>';

                foreach ($submitted as $label => $value) {
                        $returnString .= '<tr><td style="padding:5px 10px">'.$label.'</td><td style="padding:5px 10px">'.$value.'</td></tr>';
                }

                $returnString .= "</table>";
                return $returnString;
        }
}


