<?php
require 'scangame.php';
/*
delete from `scans` where `scans`.`id` > 0;
alter table `scans` AUTO_INCREMENT = 1;
*/

// Get the location, user, and token
$id = input("id", true);
$loc = input("loc", true);
check_token($loc);

// Check last scan for location
$sql = "SELECT * from `scans` where `location` = " . $loc . " and `time` > now()-5";
if ($mysqli->query($sql)->num_rows != 0) done(5, "Location scanned to recently");

// Check last scan for user
$sql = "SELECT * from `scans` where `user` = " . $id . " and `time` > now()-5";
if ($mysqli->query($sql)->num_rows != 0) done(5, "User scanned to recently");

// Check if user exists
$sql = "SELECT `name` from `users` where `id` = " . $id;
$name = null;
$response = $mysqli->query($sql);
if ($response->num_rows == 1) { // User exists. Get their name
    $name = $response->fetch_assoc()["name"];
} else { // User doesnt exist. Make them
    $insert = "INSERT INTO `users` (`id`, `added`, `team`) VALUES ('" . $id . "', CURRENT_TIMESTAMP, 'Team" . $id . "')";
    $mysqli->query($insert);
    $response = $mysqli->query($sql);
    $name = $response->fetch_assoc()["name"];
}

// Log the scan
$sql = "INSERT INTO `scans` (`time`, `user`, `location`) VALUES (CURRENT_TIMESTAMP, '" . $id . "', '" . $loc . "')";
$mysqli->query($sql);

// Get id of just logged scan
$sql = "SELECT * from `scans` where `id` = " . $mysqli->insert_id;
$result = $mysqli->query($sql)->fetch_assoc();

// Output
$json->scan_id = (int)$result["id"];
$json->user = $result["user"];
$json->location = $result["location"];
$json->time = $result["time"];
done(0, "Success", $json);

?>
