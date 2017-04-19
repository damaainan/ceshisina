<?php 

function udpGet($sendMsg = '', $ip = '127.0.0.1', $port = '9998'){ 
  $handle = stream_socket_client("udp://{$ip}:{$port}", $errno, $errstr); 
  if( !$handle ){ 
    die("ERROR: {$errno} - {$errstr}\n"); 
  } 
  fwrite($handle, $sendMsg."\n"); 
  $result = fread($handle, 1024); 
  fclose($handle); 
  return $result; 
} 
$result = udpGet('Hello World'); 
echo $result;