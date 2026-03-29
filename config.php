<?php
$conn = new mysqli(
"sql200.infinityfree.com",
"if0_41504095",
"AhfarWYZZewmh",
"if0_41504095_campusconnect"
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
