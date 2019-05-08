<?php
require 'scangame.php';
/*
delete from `scans` where `scans`.`id` > 0;
alter table `scans` AUTO_INCREMENT = 1;
*/

$loc = input("loc", true);

// Check location
$sql = "SELECT * from `points` where `id` = " . $loc;
$response = $mysqli->query($sql);
if ($response->num_rows == 0) done(6, "Location not registered");

// Check last scan
$sql = "SELECT * from `scans` where `location` = ".$loc." ORDER BY `time` DESC LIMIT 1";
$response = $mysqli->query($sql);
if ($response->num_rows == 0) die("Location unclaimed");
$result = $response->fetch_assoc();

// Check user in users
$sql = "SELECT `name` from `users` where `id` = ".$result["user"];
$name = $mysqli->query($sql)->fetch_assoc()["name"];

// Output
$json->scan_id = (int)$result["id"];
$json->user = $result["user"];
$json->location = $result["location"];
$json->time = $result["time"];
done(0, "Success", $json);
?>
