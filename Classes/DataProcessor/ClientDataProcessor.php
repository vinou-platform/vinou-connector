<?php
namespace Vinou\VinouConnector\DataProcessor;

use \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use \TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \Vinou\VinouConnector\Utility\Helper;

class ClientDataProcessor implements DataProcessorInterface
{
	public function process(
		ContentObjectRenderer $cObj,
		array $contentObjectConfiguration,
		array $processorConfiguration,
		array $processedData
	) {

		$api = Helper::initApi();
		$client = $api->getClient();
		$key = array_key_exists('as', $processorConfiguration) ? $processorConfiguration['as'] : 'client';
		$processedData[$key] = $api->getClient();
		return $processedData;
	}
}