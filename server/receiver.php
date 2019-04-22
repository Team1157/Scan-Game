<?php
if (!isset($_GET['id'])) die("No ID");
if (!isset($_GET['loc'])) die("No Location");
$id = $_GET['id'];
$loc = $_GET['loc'];

echo("Scan ".$id." at loc ".$loc." at ");
$mysqli = null;
try { // Connect
	$mysqli = new mysqli('127.0.0.1', 'scangame', 'scangame', 'scangame');
	if ($mysqli->connect_errno) {
		throw new Exception("Error: ".$mysqli->connect_errno.". ".$mysqli->connect_error."");
		//echo "Errno: " . $mysqli->connect_errno . "<br>Error: " . $mysqli->connect_error . "<br>";
	}
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	echo "Error: Could not connect to database";
	exit;
}
echo("Ree");

$sql = "version";
if (!$queryresult = $mysqli->query($sql)) {
	header("HTTP/1.1 500 Internal Server Error");
	echo "Error. Database query failed.";
	echo "Query: " . $sql . "<br>Errno: " . $mysqli->errno . "<br>Error: " . $mysqli->error . "<br>";
	exit;
}
if ($queryresult->num_rows == 0) return; 
echo($queryresult);

echo("re?");


?>
</br>End
