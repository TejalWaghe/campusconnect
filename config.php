<?php

$host = "hopper.proxy.rlwy.net";
$user = "root";
$pass = "WRSnDaIJjSFsWuWCSnFRmCtomAalaTSu";
$db   = "railway";
$port = 11334;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
