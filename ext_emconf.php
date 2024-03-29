<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Vinou Connector',
	'description' => 'Extension that provides a connection for vinou.de and display wines and winelists via fluid templates',
	'category' => 'plugin',
	'author' => 'Vinou development team',
	'author_email' => 'kontakt@vinou.de',
	'author_company' => 'Vinou GmbH',
	'state' => 'beta',
	'uploadfolder' => NULL,
	'createDirs' => NULL,
	'clearCacheOnLoad' => true,
	'version' => '8.1.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '10.4.4-10.4.99',
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