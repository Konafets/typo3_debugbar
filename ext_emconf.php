<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'A TYPO3 debug bar',
    'description' => 'Utilizes the PHP Debugbar to provide information of the system health to the frontend.',
    'category' => 'fe',
    'author' => 'Stefano Kowalke',
    'author_email' => 'info@arroba-it.de',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '1.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'php' => '5.4.0-7.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
