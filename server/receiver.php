<?php
/*
delete from `scans` where `scans`.`id` > 0;
alter table `scans` AUTO_INCREMENT = 1;
*/
if (!isset($_GET['id'])) die("No ID");
if (!isset($_GET['loc'])) die("No Location");

$id = $_GET['id'];
$loc = $_GET['loc'];

$mysqli = null;
try { // Connect
	$mysqli = new mysqli('127.0.0.1', 'scangame', 'scangame', 'scangame');
	if ($mysqli->connect_errno) {
		throw new Exception("Error: ".$mysqli->connect_errno.". ".$mysqli->connect_error."");
		//echo "Errno: " . $mysqli->connect_errno . "<br>Error: " . $mysqli->connect_error . "<br>";
	}
} catch (Exception $e) {
	header("HTTP/1.1 500 Internal Server Error");
	die("Error: Could not connect to database");
}

$eid = mysqli_real_escape_string($mysqli, $id);
$eloc = mysqli_real_escape_string($mysqli, $loc);
$sql = "INSERT INTO `scans` (`time`, `user`, `location`) VALUES (CURRENT_TIMESTAMP, '".$eid."', '".$eloc."')";
$mysqli->query($sql);

$sql = "SELECT * from `scans` where `id` = ".$mysqli->insert_id;
$result = $mysqli->query($sql)->fetch_assoc();

echo("Scan id: ".$result["id"]."<br>User: ".$result["user"]."<br>Location: ".$result["location"]."<br>Time: ".$result["time"]);

?>
