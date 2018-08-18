<?php

function resetBit($number, $bit_index){
  $mask = string2bin(decbin(~(1 << $bit_index)));
  return $number & $mask;
}

function setBit($number, $bit_index){
  $mask = string2bin(decbin(1 << $bit_index));
  return $number | $mask;
}

function getBit($number, $bit_index){
  $mask = string2bin(decbin(1 << $bit_index));
  //echo $mask;echo ' '.$number.' '.($number & $mask); die(':(');
  return (int)(bindec($number & $mask) !== 0);
}

function string2bin($string, $bit_size=32){
  $string = substr($string, -32);
  return str_pad(decbin(bindec($string)), $bit_size, 0, STR_PAD_LEFT);
}

function insertBinaryIntoAnother($number_host, $number_guest, $index_from, $index_to){ // indexes count from the right
  $number_host = string2bin($number_host);
  $number_guest = string2bin($number_guest);
  for($i = $index_from; $i <= $index_to; $i++){
    $number_host = resetBit($number_host, $i);
    $guest_index = $i - $index_from;
    $value = getBit($number_guest, $guest_index);
    if($value){
        $number_host = setBit($number_host, $i);
    }
    // echo "index: $i, guest_index: $guest_index, value: $value\r\n";
  }
  return $number_host;
}

$number_host = '1000000000000';
$number_guest = '11011';
$index_from = 2;
$index_to = 6;

$new_binary = insertBinaryIntoAnother($number_host, $number_guest, $index_from, $index_to);
echo "new_binary: '$new_binary'";

