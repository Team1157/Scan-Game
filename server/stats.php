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
$sql = "SELECT DISTINCT `user` FROM `scans` WHERE `location` = " . $loc . " AND `time` > (
    SELECT `time` FROM `scans` WHERE `location` = " . $loc . " AND `team` <> (
        SELECT `team` FROM `scans` WHERE `location` = " . $loc . " ORDER BY `time` DESC LIMIT 1
    ) OR `id` = 0 ORDER by `time` DESC LIMIT 1
) ORDER BY `time`;";
$response = $mysqli->query($sql);
if ($response->num_rows == 0) die("Location unclaimed");
$user_ids = array();
$multiplier = 0;
while ($row = $response->fetch_assoc()) {
    $uid = (int)$row['user'];
    $sql = "SELECT `name` FROM `users` WHERE `id` = " . $uid;
    $user = $mysqli->query($sql);
    if ($user->num_rows == 0) continue;
    $name = $user->fetch_assoc()["name"];
    $user_ids[] = $name;
    #$user_ids[] = array('id' => $uid, 'name' => $name);
    $multiplier++;
}
/*
$sql  = "SELECT * FROM `points`";
$response = $mysqli->query($sql);
while ($row = $response->fetch_assoc()) {
    $team = $row["team"];
    $sql = "SELECT * FROM `scans` WHERE `team` = ".$team;
    $user = $mysqli->query($sql);
    if ($user->num_rows == 0) continue;
    $name = $user->fetch_assoc()["name"];
    $user_ids[] = $name;
    #$user_ids[] = array('id' => $uid, 'name' => $name);
    $multiplier++;
}
*/

/*/ Check user in users
$sql = "SELECT `name` from `users` where `id` = ".$result["user"];
$name = $mysqli->query($sql)->fetch_assoc()["name"];
*/
// Output
$json->claimed_team = $last["team"];
$json->multiplier = $multiplier;
$json->owners = $user_ids;
$json->last_time = $last["time"];
done(0, "Success", $json);
?>
