<?php
//error_reporting( E_ALL );
set_time_limit( 0 );
ob_implicit_flush();
$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
socket_bind( $socket, '192.168.2.143', 11109 );
socket_listen($socket);
$acpt=socket_accept($socket);
echo "Acpt!\n";
while ( $acpt ) {
  $words=fgets(STDIN);
  socket_write($acpt,$words);
  $hear=socket_read($acpt,1024);
  echo $hear;
  if("bye\r\n"==$hear){
    socket_shutdown($acpt);
    break;
  }
  usleep( 1000 );
}
socket_close($socket);