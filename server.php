<?php

// Laravel dev-server router (restores the file missing from vendor).
// Temporary helper for local screenshot capture.
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri) && ! is_dir(__DIR__.'/public'.$uri)) {
    return false;
}

require_once __DIR__.'/public/index.php';
