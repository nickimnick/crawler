<?php
  
  //DATABASE INFO
	$host = "localhost";
	$user = "root";
	$pass = "1079";
	$dbName = "crawler_db";
  
  $mysqli = new mysqli($host,$user,$pass,$dbName);

	if ($mysqli->connect_errno){
		
		$result = $mysqli->connect_error;
    exit();
    
  }
  
?>