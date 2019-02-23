<?php
namespace Vinou\VinouConnector\Eid;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class could called with AJAX via eID
 *
 * @author  Christian Händel <christian@vinou.de>, Vinou.
 * @package TYPO3
 * @subpackage  EidCacheExpertise
 */
class ClientLogin {

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
    protected $localDir = 'typo3temp/vinou_connector/';
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
        $data = $this->api->getClientLogin();
        if (!$data) {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Initialize Extbase
     *
     * @param \array $TYPO3_CONF_VARS           The global $TYPO3_CONF_VARS array. Will be set internally in ->TYPO3_CONF_VARS
     */
    public function __construct($TYPO3_CONF_VARS) {
        session_start();
        $_SESSION['start'] = strftime('%d.%m.%Y',time());

        $this->extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['vinou_connector']);
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
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

        $this->api = new \Vinou\VinouConnector\Utility\Api(
            $this->extConf['token'],
            $this->extConf['authId'],
            $dev
        );

        $this->absoluteTempDirectory = GeneralUtility::getFileAbsFileName($this->extConf['cachingFolder']);
        if(!is_dir($this->absoluteTempDirectory)){
            mkdir($this->absoluteTempDirectory, 0777, true);
        }
        $this->translations = new \Vinou\VinouConnector\Utility\Translation();
    }
}
    global $TYPO3_CONF_VARS;
    $eid = GeneralUtility::makeInstance('Vinou\VinouConnector\Eid\ClientLogin', $TYPO3_CONF_VARS);
    echo $eid->run();
?>