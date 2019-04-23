<?php
/*
delete from `scans` where `scans`.`id` > 0;
alter table `scans` AUTO_INCREMENT = 1;
*/
$loc = 2;
if (!isset($_GET['loc'])) die("No Location");
$loc = $_GET['loc'];
//*/

if (!preg_match("/^\d{1,10}$/", $loc)) die("Invalid location");

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


// Check last scan
$sql = "SELECT * from `scans` where `location` = ".$loc." ORDER BY `time` DESC LIMIT 1";
$response = $mysqli->query($sql);
if ($response->num_rows == 0) die("Location unclaimed");
$result = $response->fetch_assoc();

// Check user in users
$sql = "SELECT `name` from `users` where `id` = ".$result["user"];
$name = $mysqli->query($sql)->fetch_assoc()["name"];

echo("Scan id: ".$result["id"]."<br>User: ".$result["user"]."<br>Name: ".$name."<br>Location: ".$result["location"]."<br>Time: ".$result["time"]);

?>
