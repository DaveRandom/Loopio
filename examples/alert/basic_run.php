<?php

/**
 * examples/basic_run.php
 */

require __DIR__ . '/../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$reactor = (new AlertReactBridge\LoopFactory)->createAlertLoop($loop);

stream_set_blocking(STDIN, FALSE);

// Echo back the line each time there is readable data on STDIN
$reactor->onReadable(STDIN, function() {
    while ($line = fgets(STDIN)) {
        echo "--- $line";
    }
});

// Countdown for ten seconds
$secondsRemaining = 10;
$reactor->repeat(function() use ($reactor, &$secondsRemaining) {
    if (--$secondsRemaining) {
        echo "- countdown: $secondsRemaining\n";
    } else {
        $reactor->stop();
    }
}, $msInterval = 1000);

echo "Each line you type will be echoed back for the next {$secondsRemaining} seconds ...\n\n";

// Calling Reactor::run() will give control of program execution to the event reactor. The program
// will not continue beyond the next line until your code explicity calls Reactor::stop().
$reactor->run();
