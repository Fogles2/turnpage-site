<?php
require_once 'config.php';

function tp_read_state() {
    $file = MAINTENANCE_STATE_FILE;
    $default = ['on' => false, 'target' => null];
    $json = json_decode(@file_get_contents($file), true);
    if (!is_array($json)) {
        $json = ['on' => false, 'target' => null];
    }
    return $json;
}

$state = tp_read_state();
$adminBypass = isset($_GET['bypass']) && $_GET['bypass'] === '1';

if (!empty($state['on']) && !$adminBypass) {
    header('Location: maintenance.php');
    exit;
}

// Serve the existing site content (index.html)
$path = __DIR__ . '/index.html';
if (file_exists($path)) {
    readfile($path);
} else {
    echo '<!DOCTYPE html>
<html><head><title>Site</title></head><body><h1>Welcome</h1><p>index.html not found.</p></body></html>';
}
?>