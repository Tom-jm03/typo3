<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title' => 'RequestMirror',
    'description' => 'RequestMirror',
    'category' => 'example',
    'version' => '11.0.0',
    'state' => 'beta',
    'author' => 'Stefan Bürk',
    'author_email' => 'stefan@buerk.tech',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.99.99',
            'frontend' => '11.0.0-11.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];