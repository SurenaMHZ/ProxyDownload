<?php
// PHP Proxy Configuration
return [
    'secret_key' => 'CHANGE_THIS_SECRET',       // HMAC secret key
    'require_auth' => true,                     // Enable/disable authentication
    'auth_token' => 'MY_SUPER_SECRET_TOKEN',   // Token required if auth enabled
    'enable_expiry' => true,                    // Enable/disable link expiry
    'expiry_time' => 3600,                      // Expiry in seconds
    'limit_speed' => true,                      // Enable speed limit
    'speed_kb' => 100,                          // KB/s
    'max_file_size_mb' => 0                     // 0 = unlimited
];
