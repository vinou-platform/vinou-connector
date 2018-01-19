<?php
namespace Interfrog\Vinou\Utility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class could called with AJAX via eID
 *
 * @author  Christian Händel <christian@vinou.de>, Vinou.
 * @package TYPO3
 * @subpackage  EidCacheExpertise
 */
class EidCacheExpertise {

    /**
     * extConf
     * @var array
     */
    protected $extConf;

    /**
     * objectManager
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected $objectManager;

    /**
     * persistence manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager; 

    protected $api;
    protected $llPath = 'Resources/Private/Language/';
    protected $localDir = 'typo3temp/vinou/';
    protected $absoluteTempDirectory = '';
    protected $translations;

    /**
     * configuration
     *
     * @var \array
     */
    protected $configuration;

    /**
     * bootstrap
     *
     * @var \array
     */
    protected $bootstrap;

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
                $cachePDFProcess = Pdf::storeApiPDF($wine['expertisePDF'],$this->absoluteTempDirectory.'/',$wine['id'].'-',$wine['chstamp'],true);
                $localDir = $this->extConf['cachingFolder'];
                if (substr($localDir, -1) != '/') {
                    $localDir = $localDir . '/';
                }
                $redirectURL = '/'.$localDir.$cachePDFProcess['fileName']. '?' .time();
            } else {
                $redirectURL = 'https://api.vinou.de'.$wine['expertisePDF'];
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
        $this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['vinou']);
        $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

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

        $this->api = new \Interfrog\Vinou\Utility\Api(
            $this->extConf['token'],
            $this->extConf['authId'],
            $dev
        );

        $this->absoluteTempDirectory = GeneralUtility::getFileAbsFileName($this->extConf['cachingFolder']);
        if(!is_dir($this->absoluteTempDirectory)){
            mkdir($this->absoluteTempDirectory, 0777, true);
        }
        $this->translations = new \Interfrog\Vinou\Utility\Translation();
    }
}
    global $TYPO3_CONF_VARS;
    $eid = GeneralUtility::makeInstance('Interfrog\Vinou\Utility\EidCacheExpertise', $TYPO3_CONF_VARS);
    echo $eid->run();
?>