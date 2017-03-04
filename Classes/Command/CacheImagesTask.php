<?php 
namespace Interfrog\Vinou\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Interfrog\Vinou\Utility\Api;
use Interfrog\Vinou\Utility\Images;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Interfrog Produktion GmbH, a.buechner@division.ag
 *
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
 * @package scheduler_tasks
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CacheImagesTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

    protected $extKey = 'vinou';
    protected $objectManager;
    protected $persistenceManager; 
    protected $extConf = NULL;
    protected $absoluteTempDirectory = '';
    protected $api = NULL;
    protected $reportData = [];

    /**
     * Taskdata
     * @var array
     */
    public $data;

    public function init() {

        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
        $this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');

        $this->absoluteTempDirectory = GeneralUtility::getFileAbsFileName($this->extConf['cachingFolder']);
        if(!is_dir($this->absoluteTempDirectory)){
            mkdir($this->absoluteTempDirectory, 0777, true);
        }

        $dev = false;
        if ($this->extConf['vinouMode'] == 'dev') {
          $dev = true;
        }

        $this->api = new Api(
          $this->extConf['token'],
          $this->extConf['authId'],
          $dev
        );
    }

    public function execute(){
        $this->init();

        $this->reportData['itemsPerTask'] = $this->itemsPerTask;
        $this->reportData['imported'] = 0;

        $allWines = $this->api->getWinesAll();
        $needMore = FALSE;
        for ($i=0; $i < count($allWines); $i++) {
            
            $cacheImageProcess = Images::storeApiImage($allWines[$i]['image'],$this->absoluteTempDirectory,$allWines[$i]['chstamp']);
            if (!$cacheImageProcess['fileExists']) {
                $this->reportData['imported']++;
            }

            if ($this->reportData['imported'] == $this->itemsPerTask) {
                $needMore = TRUE;
                break;
            }
        }

        if ($needMore) {
            // THINGS TO DO IF MORE IMAGES MUST BE FETCHED
        }

        return true;
    }
}