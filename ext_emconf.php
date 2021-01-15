<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Vinou Connector',
	'description' => 'Extension that provides a connection for vinou.de and display wines and winelists via fluid templates',
	'category' => 'plugin',
	'author' => 'Vinou development team',
	'author_email' => 'kontakt@vinou.de',
	'author_company' => 'Vinou GmbH',
	'state' => 'alpha',
	'uploadfolder' => NULL,
	'createDirs' => NULL,
	'clearCacheOnLoad' => true,
	'version' => '5.6.2',
	'constraints' => array(
		'depends' => array(
			'extbase' => '8.7',
			'fluid' => '8.7',
			'typo3' => '8.7',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'autoload' => array(
		'psr-4' => array('Vinou\\VinouConnector\\' => 'Classes')
	),
);