<?php
// An array of names

$users = array("John", "Jane", "Sally", "Philip");

// Pass the array to array_walk

array_walk($users, function ($name) {
  echo "Hello $name\r\n";
});


// Create a user
$user = "Philip02";
// Create a Closure
$greeting = function() use ($user) {
     echo "Hello $user";
};

$greeting();