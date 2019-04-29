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

// Check if user exists. If they do return their name
function check_user($id) {
    $nameIn = input("name");
    global $mysqli;
    
    $sql = "SELECT `name` from `users` where `id` = " . $id;
    $response = $mysqli->query($sql);
    $nameU = null;
    if ($response->num_rows == 1) { // User exists. Get their name
        $nameU = $response->fetch_assoc()["name"];
        if (strcmp($nameIn, $nameU)) return $nameIn;
        else if (similar_text($nameIn, $nameU) > 7) return $nameU;
        else done(7, "mismatch name");
    } else { // User doesnt exist. Make them
        $insert = "INSERT INTO `users` (`name`, `id`, `added`, `team`) VALUES ('" . $name . "', '" . $id . "', CURRENT_TIMESTAMP, 'Team" . $id . "')";
        $mysqli->query($insert);
        $response = $mysqli->query($sql);
        $name = $response->fetch_assoc()["name"];
    }
    
}

// Check if user exists
$name = check_user($id);


// Check last scan for location
$sql = "SELECT * from `scans` where `location` = " . $loc . " and `time` > now()-5";
if ($mysqli->query($sql)->num_rows != 0) done(5, "Location scanned to recently");

// Check last scan for user
$sql = "SELECT * from `scans` where `user` = " . $id . " and `time` > now()-5";
if ($mysqli->query($sql)->num_rows != 0) done(5, "User scanned to recently");

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
