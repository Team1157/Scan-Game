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
$last = $response->fetch_assoc();

// Check last scan
$sql = "SELECT distinct user FROM scans where location = 1 and time > (
    SELECT time FROM `scans` where `location` = 1 and `team` <> (
        SELECT team FROM `scans` where `location` = 1 ORDER BY `time` DESC LIMIT 1
    ) OR id = 0 ORDER by `time` desc limit 1
) ORDER BY `time`;";
$response = $mysqli->query($sql);
if ($response->num_rows == 0) die("Location unclaimed");
$users = $response->fetch_assoc();

/*/ Check user in users
$sql = "SELECT `name` from `users` where `id` = ".$result["user"];
$name = $mysqli->query($sql)->fetch_assoc()["name"];
*/
// Output
$json->claimed_team = $last["team"];
$json->multiplier = 1;
$json->owners = $users;
$json->last_time = $last["time"];
done(0, "Success", $json);
?>
