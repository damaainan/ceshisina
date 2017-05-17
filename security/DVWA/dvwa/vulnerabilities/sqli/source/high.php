<?php

if( isset( $_SESSION [ 'id' ] ) ) {
	// Get input
	$id = $_SESSION[ 'id' ];

	$link = mysqli_connect($_DVWA[ 'db_server' ], $_DVWA[ 'db_user' ], $_DVWA[ 'db_password' ], $_DVWA[ 'db_database' ]);
	// Check database
	$query  = "SELECT first_name, last_name FROM users WHERE user_id = '$id' LIMIT 1;";
	$result = mysqli_query($link, $query ) or die( '<pre>Something went wrong.</pre>' );

//自己新改的
	$row = mysqli_fetch_array($result, MYSQLI_NUM);
	$num_rows = mysqli_num_rows($result);
//自己新改的

	// Get results
	// $num = mysql_numrows( $result );
	$num = $num_rows ;
	$i   = 0;
	while( $i < $num ) {
		// Get values
		// $first = mysql_result( $result, $i, "first_name" );
		// $last  = mysql_result( $result, $i, "last_name" );

		$first =  $row[$i]["first_name"];
		$last =  $row[$i]["last_name"];
		// Feedback for end user
		$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";

		// Increase loop count
		$i++;
	}

	mysqli_close($link);
}

?>
