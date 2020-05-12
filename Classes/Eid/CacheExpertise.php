<?php
namespace Vinou\VinouConnector\Eid;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\ApiConnector\Api;
use \Vinou\ApiConnector\FileHandler\Pdf;

/**
 * This class could called with AJAX via eID
 *
 * @author  Christian Händel <christian@vinou.de>, Vinou.
 * @package TYPO3
 * @subpackage  EidCacheExpertise
 */
class CacheExpertise {

    /**
     * extConf
     * @var array
     */
    protected $extConf;

    protected $api;
    protected $llPath = 'Resources/Private/Language/';
    protected $localDir = 'typo3temp/vinou_connector/';
    protected $absoluteTempDirectory = '';
    protected $translations;


    /**
     * Generates the output
     *
     * @return \string      from action
     */
    public function run() {
        if (GeneralUtility::_GET('wineID')) {
            $wine = $this->api->getExpertise(GeneralUtility::_GET('wineID'));
            $wine = $this->api->getWine(GeneralUtility::_GET('wineID'));
            if ($this->extConf['cacheExpertise'] == 1) {
                $cachePDFProcess = Pdf::storeApiPDF($wine['expertisePdf'],$this->absoluteTempDirectory.'/',$wine['id'].'-',$wine['chstamp'],true);
                $redirectURL = '/'.$this->extConf['cachingFolder'].'/'.$cachePDFProcess['fileName'];
            } else {
                $redirectURL = 'https://api.vinou.de'.$wine['expertisePdf'];
            }
            header('Location: '.$redirectURL);
        }
        exit;
    }

    /**
     * Initialize Extbase
     *
     * @param \array $TYPO3_CONF_VARS           The global $TYPO3_CONF_VARS array. Will be set internally in ->TYPO3_CONF_VARS
     */
    public function __construct($TYPO3_CONF_VARS) {
        $this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['vinou_connector']);

        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        $this->initVinou();
        return true;
    }

    private function initVinou() {
        $dev = false;
        if ($this->extConf['vinouMode'] == 'dev') {
            $dev = true;
        }

        $this->api = new Api (
            $this->extConf['token'],
            $this->extConf['authId'],
            true,
            $dev
        );

        $this->absoluteTempDirectory = GeneralUtility::getFileAbsFileName($this->extConf['cachingFolder']);
        if(!is_dir($this->absoluteTempDirectory)){
            mkdir($this->absoluteTempDirectory, 0777, true);
        }
    }
}
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    define('VINOU_MODE','Ajax');
    global $TYPO3_CONF_VARS;
    $eid = GeneralUtility::makeInstance('Vinou\VinouConnector\Eid\CacheExpertise', $TYPO3_CONF_VARS);
    echo $eid->run();
?>