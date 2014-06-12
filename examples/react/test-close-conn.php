<?php

require __DIR__ . '/../../vendor/autoload.php';

$reactor = (new Alert\ReactorFactory)->select();
$loop = (new AlertReactBridge\LoopFactory)->createReactLoop($reactor);

$socket = new React\Socket\Server($loop);
$i      = 0;

$socket->on('connection', function ($conn) use (&$i, $loop) {
    $i++;

    $conn->on('end', function () use (&$i) {
        $i--;
    });
});

$loop->addPeriodicTimer(2, function () use (&$i) {
    echo "$i open connections?\n";
});

$socket->listen(8080);
$loop->run();
