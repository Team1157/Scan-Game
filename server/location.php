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

$current_users = getClimers($loc);
$last = $current_users[0];

$user_names = array();
foreach ($current_users as $user) {
    $user_names[] = $user["name"];
}

/*/ Check user in users
$sql = "SELECT `name` from `users` where `id` = ".$result["user"];
$name = $mysqli->query($sql)->fetch_assoc()["name"];
*/
// Output
$json->claimed_team = $last["team"];
$json->multiplier = sizeof($current_users);
$json->owners = $user_names;
$json->last_time = $last["time"];
done(0, "Success", $json);
?>
