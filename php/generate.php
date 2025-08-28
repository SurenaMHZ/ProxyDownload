<?php
$config = require __DIR__ . '/config.php';

function hmac($url, $expire, $nonce, $secret){
    return hash_hmac('sha256', "$url|$expire|$nonce", $secret);
}

if($config['require_auth']){
    $token = $_GET['auth_token'] ?? '';
    if($token !== $config['auth_token']){
        http_response_code(403);
        exit(json_encode(['error'=>'Invalid auth']));
    }
}

$url = $_GET['file'] ?? '';
if(!$url){
    http_response_code(400);
    exit(json_encode(['error'=>'file param required']));
}

$nonce = bin2hex(random_bytes(12));
$expire = $config['enable_expiry'] ? time() + $config['expiry_time'] : 0;
$hash = hmac($url, $expire, $nonce, $config['secret_key']);

$download_url = "download.php?file=".urlencode($url)."&nonce=$nonce&expire=$expire&token=$hash";

header('Content-Type: application/json');
echo json_encode(['download_url'=>$download_url,'expire_at'=>$expire?date('c',$expire):null]);
