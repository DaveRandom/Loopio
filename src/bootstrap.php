<?php

require __DIR__ . '/Loopio/version.php';

spl_autoload_register(function($className) {
    if (strpos($className, 'Loopio') === 0 && is_file($path = __DIR__ . '/' . strtr($className, '\\', '/') . '.php')) {
        require $path;
    }
});
