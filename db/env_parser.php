<?php

function loadEnv(string $path): void {
    if (! is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        // only VAR=value
        if (! strpos($line, '=')) {
            continue;
        }
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        // strip quotes if any
        $val = preg_replace('/^([\'"])(.*)\1$/', '$2', $val);
        // only set if not already in env
        if (getenv($key) === false) {
            putenv("$key=$val");
            $_ENV[$key] = $val;
        }
    }
}