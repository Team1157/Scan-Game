<?php
/*
INSERT INTO `points` (`id`, `name`, `password`) VALUES ('1', 'Name', 'Hello');
*/
header('Content-Type: application/json');
function done($err, $errMsg = "", $output = null) {
	$output->err = $err;
	$output->errmsg = $errMsg;
	die(json_encode($output));
}
/*
Error Messages:
0  - Success
1  - Database Error
2  - No location
3  - No user
4  - No password
5  - No token
6  - Invalid location
7  - Invalid user
8  - Invalid password
9  - Invalid token
10 - Not authenticated

*/

if (!isset($_POST['loc'])) done(2, "No Location");
$loc = $_POST['loc'];
if (!preg_match("/^\d{1,10}$/", $loc)) done(6, "Invalid location");

if (!isset($_POST['password'])) done(4, "No password");
$pwd = $_POST['password'];
//*/
//$pwd = "Hello";
//$loc = 1;
$mysqli = null;
try { // Connect
	$mysqli = new mysqli('127.0.0.1', 'scangame', 'scangame', 'scangame');
	if ($mysqli->connect_errno) {
		throw new Exception("Error: ".$mysqli->connect_errno.". ".$mysqli->connect_error."");
		//echo "Errno: " . $mysqli->connect_errno . "<br>Error: " . $mysqli->connect_error . "<br>";
	}
} catch (Exception $e) {
	done(1, "Error: Could not connect to database");
}

// Check last scan
$sql = "SELECT * from `points` where `id` = ".$loc;
$response = $mysqli->query($sql);
if ($response->num_rows == 0) done(6, "Location not registered");
$result = $response->fetch_assoc();
if (strcmp($result["password"], $pwd) != 0) done(8, "Incorrect password");

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
$result = $mysqli->query($sql)->fetch_assoc();

$json->id = (int)$result["id"];
$json->name = $result["name"];
$json->token = $result["token"];
done(0, "Success", $json);

?>
