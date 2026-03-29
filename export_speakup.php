<?php
session_start();
include("config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: login.php");
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="complaints.csv"');

$output = fopen("php://output", "w");

/* CSV Header */
fputcsv($output, array(
    "Title",
    "Category",
    "Message",
    "Submitted By",
    "Status",
    "Date"
));

$query = "
SELECT speakup.title, speakup.category, speakup.message,
users.fullname, speakup.status, speakup.message_date
FROM speakup
JOIN users ON speakup.user_id = users.id
ORDER BY speakup.id DESC
";

$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)) {

    fputcsv($output, array(
        $row['title'],
        $row['category'],
        $row['message'],
        $row['fullname'],
        $row['status'],
        $row['message_date']
    ));

}

fclose($output);
exit();
?>