<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Vinou Connector',
	'description' => 'Extension that provides a connection for vinou.de and display wines and winelists via fluid templates',
	'category' => 'plugin',
	'author' => 'Interfrog',
	'author_email' => 'info@interfrog.de',
	'author_company' => 'Interfrog Produktion GmbH',
	'state' => 'alpha',
	'uploadfolder' => NULL,
	'createDirs' => NULL,
	'clearCacheOnLoad' => true,
	'version' => '1.0.2',
	'constraints' => array(
		'depends' => array(
			'extbase' => '6.2',
			'fluid' => '6.2',
			'typo3' => '6.2',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'autoload' => array(
		'psr-4' => array('Interfrog\\Vinou\\' => 'Classes')
	),
);