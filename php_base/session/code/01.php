<?php
session_start();
setcookie('name','tao') ;
setcookie('gender','male') ;
var_dump(session_id());
var_dump($_COOKIE);