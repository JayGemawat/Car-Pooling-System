<?php
/*
 * Logout Script
 */
	session_start();
	$_SESSION=array();
	if(isset($_COOKIE[session_name()])) {
		setcookie(session_name(),"",time()-41000,'/');
	}
	session_destroy();
	header("Location: login.php?logout=1");
?>
