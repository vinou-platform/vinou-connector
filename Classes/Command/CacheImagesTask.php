<?php
namespace Vinou\VinouConnector\Command;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\ApiConnector\FileHandler\Images;

define('VINOU_MODE', 'cli');

class CacheImagesTask extends AbstractTask {

	protected $cacheDir = null;
	protected $api = null;
	protected $reportData = [];

	public function init() {

		$this->api = Helper::initApi();

		$this->reportData = [
			'itemsPerTask' => $this->itemsPerTask,
			'imported' => 0
		];
	}

	public function execute(){
		$this->init();

		$data = $this->api->getWinesAll();
		$wines = isset($data['wines']) ? $data['wines'] : $data['data'];

		for ($i=0; $i < count($wines); $i++) {
			$wine = $wines[$i];

			$cacheImageProcess = Images::storeApiImage(
				$wine['image'],
				$wine['chstamp'],
				Helper::getImageCacheDir(),
				$this->cacheDir
			);

			if ($cacheImageProcess['fileFetched'])
				$this->reportData['imported']++;

			if ($this->reportData['imported'] == $this->itemsPerTask)
				break;
		}

		return true;
	}
}