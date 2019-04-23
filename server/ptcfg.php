<?php
/*
INSERT INTO `points` (`id`, `name`, `password`) VALUES ('1', 'Name', 'Hello');
*/

/*
if (!isset($_GET['loc'])) die("No Location");
$loc = $_GET['loc'];
if (!preg_match("/^\d{1,10}$/", $loc)) die("Invalid location");

if (!isset($_GET['password'])) die("No password");
$pwd = $_GET['password'];
*/
$pwd = "Hello";
$loc = 1;
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
$sql = "SELECT * from `points` where `id` = ".$loc;
$response = $mysqli->query($sql);
if ($response->num_rows == 0) die("Location not registered");
$result = $response->fetch_assoc();
if (strcmp($result["password"], $pwd) != 0) die("Incorrect password");

$token = "";
$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
$max = count($characters) - 1;
for ($i = 0; $i < 20; $i++) {
	$rand = mt_rand(0, $max);
	$token .= $characters[$rand];
}

$sql = "UPDATE `points` SET `token` = '".$token."' WHERE `points`.`id` = ".$loc;
$mysqli->query($sql);

// Check user in users
$sql = "SELECT * from `points` where `id` = ".$loc;
$result = $mysqli->query($sql);

$json->id = $result["id"];
$json->name = $result["name"];
$json->token = $result["token"];

echo(json_encode($json));

?>
