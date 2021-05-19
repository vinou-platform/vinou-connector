<?php
namespace Vinou\VinouConnector\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\ApiConnector\FileHandler\Pdf;
use \Vinou\VinouConnector\Utility\Helper;

/**
 * This class could called with AJAX via eID
 *
 * @author  Christian Händel <christian@vinou.de>, Vinou.
 * @package TYPO3
 * @subpackage  EidCacheExpertise
 */
class CacheExpertise {

    protected $api;

    public function __construct() {

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        $this->api = Helper::initApi();
    }

    /**
     * Run cache and redirect
     */
    public function run() {

        if (GeneralUtility::_GET('wineID')) {
            $wine = $this->api->getExpertise(GeneralUtility::_GET('wineID'));
            $wine = $this->api->getWine(GeneralUtility::_GET('wineID'));

            $redirectURL = 'https://api.vinou.de' . $wine['expertisePdf'];

            if (Helper::getExtConfValue('cacheExpertise') == 1) {
                $cacheResult = Pdf::storeApiPDF(
                    $wine['expertisePdf'],
                    $wine['chstamp'],
                    Helper::ensureCacheDir(),
                    $wine['id'].'-',true);
                $redirectURL = '/'. Helper::$cacheDir .'/'. $cacheResult['fileName'];
            }

            header('Location: '.$redirectURL);
        }
    }

}

error_reporting(E_ALL);
ini_set("display_errors", 1);
define('VINOU_MODE','Ajax');

$eid = GeneralUtility::makeInstance(CacheExpertise::class);
echo $eid->run();
?>