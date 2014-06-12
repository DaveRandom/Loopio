<?php

// downloading the two best technologies ever in parallel

require __DIR__ . '/../../vendor/autoload.php';

$reactor = (new Alert\ReactorFactory)->select();
$loop = (new AlertReactBridge\LoopFactory)->createReactLoop($reactor);

$files = array(
    'node-v0.6.18.tar.gz' => 'http://nodejs.org/dist/v0.6.18/node-v0.6.18.tar.gz',
    'php-5.5.12.tar.gz' => 'http://php.net/get/php-5.5.12.tar.gz/from/this/mirror',
);

foreach ($files as $file => $url) {
    $readStream = fopen($url, 'r');
    $writeStream = fopen($file, 'w');

    stream_set_blocking($readStream, 0);
    stream_set_blocking($writeStream, 0);

    $read = new React\Stream\Stream($readStream, $loop);
    $write = new React\Stream\Stream($writeStream, $loop);

    $read->on('end', function () use ($file, &$files) {
        unset($files[$file]);
        echo "Finished downloading $file\n";
    });

    $read->pipe($write);
}

$loop->addPeriodicTimer(5, function ($timer) use (&$files, $loop) {
    if (0 === count($files)) {
        $timer->cancel();
        file_put_contents('debug.txt', print_r($loop, true));
    }

    foreach ($files as $file => $url) {
        $mbytes = filesize($file) / (1024 * 1024);
        $formatted = number_format($mbytes, 3);
        echo "$file: $formatted MiB\n";
    }
});

echo "This script will show the download status every 5 seconds.\n";

$loop->run();
