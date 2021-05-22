<?php
namespace Vinou\VinouConnector\Command;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Scheduler\Task\AbstractTask;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\ApiConnector\FileHandler\Pdf;

define('VINOU_MODE', 'cli');

class CacheExpertiseTask extends AbstractTask {

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
			$cachePDFProcess = Pdf::storeApiPDF(
				$wine['expertisePdf'],
				$wine['chstamp'],
				Helper::getPdfCacheDir(),
				$wine['id'] . '-',
				$wine['expertiseStatus'] != 'OK'
			);


			if ($cachePDFProcess['requestStatus'] === 404)
				$cachePDFProcess = Pdf::storeApiPDF(
					$this->api->getExpertise($wine['id']),
					$wine['chstamp'],
					Helper::getPdfCacheDir(),
					$wine['id'] . '-',
					strtotime($wine['chstamp'])
				);

			if ($cachePDFProcess['fileFetched'])
				$this->reportData['imported']++;

			if ($this->reportData['imported'] == $this->itemsPerTask)
				break;
		}

		return true;
	}
}