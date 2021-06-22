<?php
namespace Vinou\VinouConnector\Service;

use \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use \TYPO3\CMS\Core\Authentication\LoginType;
use \TYPO3\CMS\Core\Database\ConnectionPool;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \Vinou\VinouConnector\Utility\Helper;

class VinouAuthenticationService extends AbstractUserAuthentication {

	protected $api;

	public function init() {

		$this->api = Helper::initApi();
		return $this->api->connected;

	}

	public function reset() {

	}

	public function authUser() {

		 if ($this->api->getClient())
		 	return 200;

		 return 0;

	}

	public function getUser() {

		$user = $this->getVinouUser();
		if(!is_array($user)) {
			$this->logger->warning('Attempted vinou login failed', ['username' => 'vinou']);
			return false;
		} else {
			$this->logger->info('Successful vinou user found', ['id'=>$user['uid'], 'username'=>$user['username']]);
		}

		return $user;
	}

	private function getVinouUser() {

		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($this->user_table)->createQueryBuilder();
		return $queryBuilder
				   ->select('*')
				   ->from('fe_users')
				   ->where(
				      $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter('vinou'))
				    )
				   ->execute()
				   ->fetchColumn(0);

	}
}