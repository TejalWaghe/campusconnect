<?php
include("smtp_config.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* ===============================
   EMAIL TO STUDENT WHEN STATUS UPDATED
================================ */

function sendComplaintUpdate($email, $name, $title, $status){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
    $mail->Timeout = 5;
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Port = SMTP_PORT;

$mail->setFrom(SMTP_EMAIL, 'CampusConnect');
$mail->addAddress($email,$name);

$mail->isHTML(true);

$mail->Subject = "CampusConnect Complaint Update";

$mail->Body = "
Hello <b>$name</b>,<br><br>

Your complaint <b>$title</b> status has been updated to:<br><br>

<b>$status</b><br><br>

Thank you,<br>
CampusConnect System
";

if(!$mail->send()){
    error_log("Mail Error: " . $mail->ErrorInfo);
}

}catch(Exception $e){
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

}


/* ===============================
   EMAIL TO ADMINS WHEN STUDENT SUBMITS COMPLAINT
================================ */

function sendAdminNotification($email,$admin_name,$student_name,$title,$category,$date){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
    $mail->Timeout = 5;
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Port = SMTP_PORT;

$mail->setFrom(SMTP_EMAIL, 'CampusConnect');
$mail->addAddress($email,$admin_name);

$mail->isHTML(true);

$mail->Subject = "New SpeakUp Message Submitted";

$mail->Body = "
Hello <b>$admin_name</b>,<br><br>

A new SpeakUp complaint has been submitted on CampusConnect.<br><br>

<b>Student:</b> $student_name <br>
<b>Title:</b> $title <br>
<b>Category:</b> $category <br>
<b>Date:</b> $date <br><br>

Please login to the admin dashboard to review it.<br><br>

CampusConnect System
";

if(!$mail->send()){
    error_log("Mail Error: " . $mail->ErrorInfo);
}

}catch(Exception $e){
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

}


/* ===============================
   EMAIL TO ALL USERS WHEN LOST/FOUND POSTED
================================ */

function sendLostFoundNotification($email,$name,$type,$category,$description){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
    $mail->Timeout = 5;
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Port = SMTP_PORT;

$mail->setFrom(SMTP_EMAIL, 'CampusConnect');
$mail->addAddress($email,$name);

$mail->isHTML(true);

$mail->Subject = "CampusConnect Lost & Found Alert";

$mail->Body = "
Hello <b>$name</b>,<br><br>

A new item has been reported in the <b>Lost & Found Portal</b>.<br><br>

<b>Type:</b> $type <br>
<b>Category:</b> $category <br>
<b>Description:</b> $description <br><br>

If this item belongs to you, please login to CampusConnect.<br><br>

CampusConnect System
";

if(!$mail->send()){
    error_log("Mail Error: " . $mail->ErrorInfo);
}

}catch(Exception $e){
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

}


/* ===============================
   EMAIL TO STUDENTS WHEN NEW POLL CREATED
================================ */

function sendPollNotification($email,$name,$question,$expiry){

$mail = new PHPMailer(true);

try{

$mail->isSMTP();
    $mail->Timeout = 5;
$mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Port = SMTP_PORT;

$mail->setFrom(SMTP_EMAIL, 'CampusConnect');
$mail->addAddress($email,$name);

$mail->isHTML(true);

$mail->Subject = "New Poll on CampusConnect";

$mail->Body = "
Hello <b>$name</b>,<br><br>

A new poll has been created on CampusConnect.<br><br>

<b>Question:</b> $question <br>
<b>Expiry Date:</b> $expiry <br><br>

Please login to CampusConnect to vote.<br><br>

CampusConnect System
";

if(!$mail->send()){
    error_log("Mail Error: " . $mail->ErrorInfo);
}

}catch(Exception $e){
    error_log("Mailer Error: " . $mail->ErrorInfo);
}

}
