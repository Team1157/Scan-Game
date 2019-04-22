<?php
//if (!isset($_GET['id'])) die("No ID");
//if (!isset($_GET['loc'])) die("No Location");
//$id = $_GET['id'];
//$loc = $_GET['loc'];

//echo("Scan ".$id." at loc ".$loc." at ");
$db = null;
try { // Connect
	echo("Ree1");
	$db = new mysqli('127.0.0.1', 'scangame', 'scangame', 'scangame');
	echo("Ree2");
	if ($db->connect_errno) {
		throw new Exception("Error: ".$mysqli->connect_errno.". ".$mysqli->connect_error."");
		//echo "Errno: " . $mysqli->connect_errno . "<br>Error: " . $mysqli->connect_error . "<br>";
	}
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	echo "Error: Could not connect to database";
	exit;
}
echo("Ree");
echo($db.VERSION());



?>
</br>End
