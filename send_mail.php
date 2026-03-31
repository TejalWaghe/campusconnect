<?php
include("smtp_config.php");

/* =========================================
   COMMON FUNCTION (DO NOT MODIFY)
========================================= */

function sendMail($toEmail, $toName, $subject, $htmlContent){

    $data = [
        "sender" => [
            "email" => SENDER_EMAIL,
            "name"  => SENDER_NAME
        ],
        "to" => [
            [
                "email" => $toEmail,
                "name"  => $toName
            ]
        ],
        "subject" => $subject,
        "htmlContent" => $htmlContent
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: " . BREVO_API_KEY,
        "content-type: application/json"
    ]);

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        error_log("Curl Error: " . curl_error($ch));
        return false;
    }

    curl_close($ch);
    return true;
}

/* ===============================
   EMAIL TO STUDENT WHEN STATUS UPDATED
================================ */

function sendComplaintUpdate($email, $name, $title, $status){

    $subject = "CampusConnect Message Update";

    $body = "
    Hello <b>$name</b>,<br><br>

    Your message <b>$title</b> status has been updated to:<br><br>

    <b>$status</b><br><br>

    Thank you,<br>
    CampusConnect System
    ";

    sendMail($email, $name, $subject, $body);
}

/* ===============================
   EMAIL TO ADMINS WHEN STUDENT SUBMITS COMPLAINT
================================ */

function sendAdminNotification($email,$admin_name,$student_name,$title,$category,$date){

    $subject = "New SpeakUp Message Submitted";

    $body = "
    Hello <b>$admin_name</b>,<br><br>

    A new SpeakUp message has been submitted on CampusConnect.<br><br>

    <b>Student:</b> $student_name <br>
    <b>Title:</b> $title <br>
    <b>Category:</b> $category <br>
    <b>Date:</b> $date <br><br>

    Please login to the admin dashboard to review it.<br><br>

    CampusConnect System
    ";

    sendMail($email, $admin_name, $subject, $body);
}

/* ===============================
   EMAIL TO ALL USERS WHEN LOST/FOUND POSTED
================================ */

function sendLostFoundNotification($email,$name,$type,$category,$description){

    $subject = "CampusConnect Lost & Found Alert";

    $body = "
    Hello <b>$name</b>,<br><br>

    A new item has been reported in the <b>Lost & Found Portal</b>.<br><br>

    <b>Type:</b> $type <br>
    <b>Category:</b> $category <br>
    <b>Description:</b> $description <br><br>

    If this item belongs to you, please login to CampusConnect.<br><br>

    CampusConnect System
    ";

    sendMail($email, $name, $subject, $body);
}

/* ===============================
   EMAIL TO STUDENTS WHEN NEW POLL CREATED
================================ */

function sendPollNotification($email,$name,$question,$expiry){

    $subject = "New Poll on CampusConnect";

    $body = "
    Hello <b>$name</b>,<br><br>

    A new poll has been created on CampusConnect.<br><br>

    <b>Question:</b> $question <br>
    <b>Expiry Date:</b> $expiry <br><br>

    Please login to CampusConnect to vote.<br><br>

    CampusConnect System
    ";

    sendMail($email, $name, $subject, $body);
}
?>
