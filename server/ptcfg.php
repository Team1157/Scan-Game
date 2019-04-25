<?php
require 'scangame.php';
/*
INSERT INTO `points` (`id`, `name`, `password`) VALUES ('1', 'Name', 'Hello');
*/
$loc = input("loc", true);
$pwd = input("password");


// Check last scan
$sql = "SELECT * from `points` where `id` = " . $loc;
$response = $mysqli->query($sql);
if ($response->num_rows == 0) done(6, "Location not registered");
$result = $response->fetch_assoc();
if (strcmp($result["password"], $pwd) != 0) done(4, "Bad password");

$token = randStr(20);

$sql = "UPDATE `points` SET `token` = '" . $token . "' WHERE `points`.`id` = " . $loc;
$mysqli->query($sql);

// Check user in users
$sql = "SELECT * from `points` where `id` = " . $loc;
$result = $mysqli->query($sql)->fetch_assoc();

$json->id = (int)$result["id"];
$json->name = $result["name"];
$json->token = $result["token"];
done(0, "Successfully scanned", $json);

?>
