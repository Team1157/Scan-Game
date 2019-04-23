<?php
/*
delete from `scans` where `scans`.`id` > 0;
alter table `scans` AUTO_INCREMENT = 1;
*/
$id = 1;
$loc = 2;

if (!isset($_GET['id'])) die("No ID");
if (!isset($_GET['loc'])) die("No Location");

$id = $_GET['id'];
$loc = $_GET['loc'];
//*/

if (!preg_match("/^\d{1,10}$/", $id)) die("Invalid id");
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
$sql = "SELECT * from `scans` where `location` = ".$loc." and `time` > now()-5";
if ($mysqli->query($sql)->num_rows != 0) die("Location scanned to reacently");

$sql = "SELECT * from `scans` where `user` = ".$id." and `time` > now()-5";
if ($mysqli->query($sql)->num_rows != 0) die("User scanned to reacently");

// Check user in users
$sql = "SELECT `name` from `users` where `id` = ".$id;
$name = null;
$response = $mysqli->query($sql);
if ($response->num_rows == 1) $name = $response->fetch_assoc()["name"];
else {
	$insert = "INSERT INTO `users` (`name`, `id`, `added`, `team`) VALUES ('NAME FOR ".$id."', '".$id."', CURRENT_TIMESTAMP, 'Team".$id."')";
	$mysqli->query($insert);
	$response = $mysqli->query($sql);
	$name = $response->fetch_assoc()["name"];
}

//$sql = "INSERT INTO `scans` (`time`, `user`, `location`) VALUES (CURRENT_TIMESTAMP, '".$id."', '".$loc."')";
//$mysqli->query($sql);


$sql = "INSERT INTO `scans` (`time`, `user`, `location`) VALUES (CURRENT_TIMESTAMP, '".$id."', '".$loc."')";
$mysqli->query($sql);

$sql = "SELECT * from `scans` where `id` = ".$mysqli->insert_id;
$result = $mysqli->query($sql)->fetch_assoc();

echo("Scan id: ".$result["id"]."<br>User: ".$result["user"]."<br>Name: ".$name."<br>Location: ".$result["location"]."<br>Time: ".$result["time"]);

?>
