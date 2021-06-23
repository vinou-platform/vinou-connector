<?php

return [
    'frontend' => [
        'vinou/vinou-connector/ajaxactions' => [
            'target' => \Vinou\VinouConnector\Middleware\AjaxActions::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ]
        ],
    ],
];