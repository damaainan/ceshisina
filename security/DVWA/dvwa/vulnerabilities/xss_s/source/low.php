<?php

if( isset( $_POST[ 'btnSign' ] ) ) {
	// Get input
	$message = trim( $_POST[ 'mtxMessage' ] );
	$name    = trim( $_POST[ 'txtName' ] );

	$link = mysqli_connect($_DVWA[ 'db_server' ], $_DVWA[ 'db_user' ], $_DVWA[ 'db_password' ], $_DVWA[ 'db_database' ]);

	// Sanitize message input
	$message = stripslashes( $message );
	$message = mysqli_real_escape_string($link, $message );

	// Sanitize name input
	$name = mysqli_real_escape_string($link, $name );

	// Update database
	$query  = "INSERT INTO guestbook ( comment, name ) VALUES ( '$message', '$name' );";
	$result = mysqli_query($link, $query ) or die( '<pre>' . mysqli_error($link) . '</pre>' );

	//mysql_close();
}

?>
