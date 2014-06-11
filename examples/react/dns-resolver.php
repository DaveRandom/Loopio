<?php

// async DNS resolution

require __DIR__ . '/../../vendor/autoload.php';

$reactor = (new Alert\ReactorFactory)->select();
$loop = (new AlertReactBridge\LoopFactory)->createReactCompatibleLoop($reactor);

$factory = new React\Dns\Resolver\Factory();
$dns = $factory->create('8.8.8.8', $loop);

$domain = 'igor.io';

$dns
    ->resolve($domain)
    ->then(function ($ip) {
        echo "Host: $ip\n";
    }, function ($e) {
        echo "Error: {$e->getMessage()}\n";
    });

echo "Resolving domain $domain...\n";

$loop->run();
