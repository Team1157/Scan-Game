<?php
header('Content-Type: application/json');
$teams = ["Red", "Yellow", "Green", "Blue"];
function done($err, $errMsg = "", $output = null) {
    $output->err = $err;
    $output->errmsg = $errMsg;
    die(json_encode($output));
    /*
    Error Messages:
    0  - Success
    1  - Database Error
    2  - Missing Input
    3  - Invalid Input
    4  - Not authenticated
    5  - Rate limit
    6  - Noes not exist
    7  - Name not in lookup or mismatch
    */
}

$mysqli = null;
try { // Connect
    $mysqli = new mysqli('127.0.0.1', 'scangame', 'scangame', 'scangame');
    if ($mysqli->connect_errno) {
        throw new Exception("Error: " . $mysqli->connect_errno . ". " . $mysqli->connect_error . "");
        //echo "Errno: " . $mysqli->connect_errno . "<br>Error: " . $mysqli->connect_error . "<br>";
    }
} catch (Exception $e) {
    done(1, "Error: Could not connect to database");
}

function input($name, $isID = false) {
    if (!isset($_POST[$name])) done(2, "No " . $name);
    $out = $_POST[$name];
    if ($isID & !preg_match("/^\d{1,10}$/", $out)) done(3, "Invalid " . $name);
    global $mysqli;
    return mysqli_real_escape_string($mysqli, $out);
}

function randStr($chars = 1) {
    $out = null;
    $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < $chars; $i++) {
        $out .= $characters[$rand = mt_rand(0, $max)];
    }
    return $out;
}

function check_token($loc) {
    global $mysqli;
    $token = input("token");
    $sql = "SELECT * from `points` where `id` = " . $loc;
    $result = $mysqli->query($sql);
    if ($result->num_rows == 0 || strcmp($result->fetch_assoc()["token"], $token) != 0) done(4, "Bad token");
    
}

