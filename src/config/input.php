<?php

declare(strict_types=1);

use orange\framework\Application;

return [
    // get
    'query' => Application::fromGlobals('query'),
    // post
    'request' => Application::fromGlobals('request'),

    'server' => Application::fromGlobals('server'),
    'files' => Application::fromGlobals('files'),
    'cookie' => Application::fromGlobals('cookie'),

    'inputStream' => file_get_contents('php://input'),

    // for cli detection
    // override in your config if needed
    // only 1 is actually needed because both are checked
    'php_sapi' =>  Application::fromGlobals('php_sapi'), // string
    'stdin' =>  Application::fromGlobals('stdin'), // boolean
];
