<?php

if( isset( $_GET[ 'Login' ] ) ) {
	// Get username
	$user = $_GET[ 'username' ];

	// Get password
	$pass = $_GET[ 'password' ];
	$pass = md5( $pass );

	$link = mysqli_connect($_DVWA[ 'db_server' ], $_DVWA[ 'db_user' ], $_DVWA[ 'db_password' ], $_DVWA[ 'db_database' ]);
	// Check the database
	$query  = "SELECT * FROM `users` WHERE user = '$user' AND password = '$pass';";
	$result = mysqli_query($link, $query ) or die( '<pre>' . mysqli_error($link) . '</pre>' );

//自己新改的
	$row = mysqli_fetch_array($result, MYSQLI_NUM);
	$num_rows = mysqli_num_rows($result);
//自己新改的
	if( $result && $num_rows == 1 ) {
		// Get users details
		$row = mysqli_fetch_array($result, MYSQLI_NUM);
		$avatar = $row[0]["avatar"];
		// $avatar = mysql_result( $result, 0, "avatar" );

		// Login successful
		$html .= "<p>Welcome to the password protected area {$user}</p>";
		$html .= "<img src=\"{$avatar}\" />";
	}
	else {
		// Login failed
		$html .= "<pre><br />Username and/or password incorrect.</pre>";
	}

	mysqli_close($link);
}

?>
