<?php
$config = require __DIR__ . '/config.php';

$url = $_GET['file'] ?? '';
$nonce = $_GET['nonce'] ?? '';
$expire = intval($_GET['expire'] ?? 0);
$token = $_GET['token'] ?? '';

function hmac($url,$expire,$nonce,$secret){
    return hash_hmac('sha256', "$url|$expire|$nonce", $secret);
}

if(!$url || !$nonce || !$token){
    http_response_code(403); exit('Missing parameters');
}
if($config['enable_expiry'] && $expire>0 && time() > $expire){
    http_response_code(403); exit('Link expired');
}
if(hash_equals(hmac($url,$expire,$nonce,$config['secret_key']),$token) === false){
    http_response_code(403); exit('Invalid token');
}

// cURL streaming
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_BUFFERSIZE, 8192);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$bytes_sent = 0;
$speed = $config['limit_speed'] ? $config['speed_kb']*1024 : 0;
$max_bytes = $config['max_file_size_mb'] > 0 ? $config['max_file_size_mb']*1024*1024 : 0;

curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl,$data) use(&$bytes_sent,$speed,$max_bytes){
    $len = strlen($data);
    $bytes_sent += $len;
    if($max_bytes>0 && $bytes_sent>$max_bytes) return 0;
    if($speed>0){
        $chunk_size = min(8192,$len);
        $offset=0;
        while($offset<$len){
            $slice = substr($data,$offset,$chunk_size);
            echo $slice; flush();
            $offset += strlen($slice);
            usleep(intval(strlen($slice)/$speed*1e6));
        }
        return $len;
    } else {
        echo $data; flush();
        return $len;
    }
});

curl_exec($ch);
curl_close($ch);
