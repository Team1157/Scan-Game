<?php
require 'scangame.php';
/*
delete from `scans` where `scans`.`id` > 0;
alter table `scans` AUTO_INCREMENT = 1;
*/

// Get the location, user, and token
$loc = input("loc", true);
$id = input("id", true);

check_token($loc);

// Check last scan for location
$sql = "SELECT * from `scans` where `location` = " . $loc . " and `time` > now()-3";
if ($mysqli->query($sql)->num_rows != 0) done(5, "Location scanned to recently");


// Check if user exists. If they do return their name otherwise makes user and returns name
function get_user($id) {
    $firstIn = input("first");
    $lastIn = input("last");
    $nameIn = $firstIn . " " . $lastIn;
    global $mysqli, $teams;
    
    // Get user by id
    $sql = "SELECT * from `users` where `id` = " . $id;
    $response = $mysqli->query($sql);
    if ($response->num_rows == 1) { // User exists. Get their name
        $user = $response->fetch_assoc();
        if (strcmp($nameIn, $user["name"])) return $user; // Names exact
        else if (similar_text($nameIn, $user["name"]) > 7) return $user; // Names close
        else done(7, "mismatch name"); // Reee names dont match
    }
    // User doesnt exist. Make them
    // Lookup the user's name
    $sql = "SELECT * FROM `lookup` WHERE `first` LIKE '" . $firstIn . "' AND last LIKE '%" . substr($lastIn, 2) . "' AND sid is null;";
    //$sql = "SELECT `first`,`last` FROM `lookup` WHERE `first` LIKE '" . $firstIn . "' AND last LIKE '%' AND sid is null;";
    $response = $mysqli->query($sql);
    if ($response->num_rows == 0) done(7, "Name not in lookup ");
    $back = $response->fetch_assoc();
    $firstL = $back["first"];
    $lastL = $back["last"];
    $nameL = $firstL . " " . $lastL;
    
    // Get random team
    $team = $teams[array_rand($teams)];
    // Adds user to users
    $insert = "UPDATE `scangame`.`lookup` SET `sid` = " . $id . " WHERE `id` = " . $back["id"];
    $mysqli->query($insert);
    $insert = "INSERT INTO `users` (`name`, `id`, `added`, `team`) VALUES ('" . $nameL . "', '" . $id . "', CURRENT_TIMESTAMP, '" . $team . "')";
    $mysqli->query($insert);
    
    // Get name (to confirm that they were added correctly)
    $sql = "SELECT * from `users` where `id` = " . $id;
    $response = $mysqli->query($sql);
    if ($response->num_rows == 0) done(1, "IDK WHAT HAPPENED!!!");
    return $response->fetch_assoc();
}

// Check if user exists
$user = get_user($id);

// Check last scan for user
$sql = "SELECT * from `scans` where `user` = " . $id . " and `time` > now()-3";
if ($mysqli->query($sql)->num_rows != 0) done(5, "User scanned to recently");

// Log the scan
$sql = "INSERT INTO `scans` (`time`, `user`, `location`, `team`) VALUES (CURRENT_TIMESTAMP, '" . $id . "', '" . $loc . "', '" . $user["team"] . "')";
$mysqli->query($sql);

// Get id of just logged scan
$sql = "SELECT * from `scans` where `id` = " . $mysqli->insert_id;
$result = $mysqli->query($sql)->fetch_assoc();

// Output
$json->scan_id = (int)$result["id"];
$json->location = $result["location"];
$json->time = $result["time"];
$json->user_id = $result["user"];
$json->user_name = $user["name"];
$json->user_team = $user["team"];
// TODO: ADD TEAM TO SCAN
$json->user_created = ($user["added"] == $result["time"]);
done(0, "Success", $json);

?>