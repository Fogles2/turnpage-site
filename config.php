<?php
// Maintenance system configuration
define('MAINTENANCE_STATE_FILE', __DIR__ . '/maintenance_state.json');
define('MAINTENANCE_PASSWORD', 'changeme123'); // Change this!

// Email notification settings
define('NOTIFY_TO', 'admin@example.com');     // Where to send subscriber notifications
define('NOTIFY_FROM', 'noreply@example.com'); // From address

// SMTP settings (optional, for PHPMailer)
define('SMTP_HOST', '');     // e.g., 'smtp.gmail.com'
define('SMTP_PORT', 587);    // Usually 587 for TLS, 465 for SSL
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', ''); // SMTP username
define('SMTP_PASSWORD', ''); // SMTP password
?>