<?php
require 'scangame.php';

$team_info = array();
$sql = "SELECT team FROM users WHERE team <> '' GROUP BY team";
$response = $mysqli->query($sql);
while ($team = $response->fetch_assoc()["team"]) {
    $sql = sprintf("SELECT * FROM `scans` WHERE `team` = '%s'", $team);
    $team_info[] = array('name' => $team, 'score' => $mysqli->query($sql)->num_rows, 'score_per_second' => -1);
}

$json->teams = $team_info;
done(0, "Success", $json);
?>
