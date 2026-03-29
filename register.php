<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("config.php");
include("smtp_config.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error = "";
$success = "";

if(isset($_POST['register'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = "student";

    // Backend validation (IMPORTANT)
    if($password !== $confirm_password ||
       !preg_match('/[A-Z]/', $password) ||
       !preg_match('/[a-z]/', $password) ||
       !preg_match('/[0-9]/', $password) ||
       !preg_match('/[\W]/', $password) ||
       strlen($password) < 8
    ){
        $error = "Password must meet required criteria.";
    }

    if(empty($error)) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if($check_stmt->num_rows > 0) {
            $error = "Email already exists.";
        }

        $check_stmt->close();
    }

    if(empty($error)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $verification_token = bin2hex(random_bytes(32));

        $stmt = $conn->prepare("
            INSERT INTO users 
            (fullname, email, password, role, is_verified, verification_token) 
            VALUES (?, ?, ?, ?, 0, ?)
        ");

        $stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $verification_token);

        if($stmt->execute()) {

            $verification_link = "http://campusconnect2026.wuaze.com/verify.php?token=" . $verification_token;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
$mail->SMTPAuth = true;
$mail->Username = SMTP_USER;
$mail->Password = SMTP_PASS;
$mail->SMTPSecure = SMTP_SECURE;
$mail->Port = SMTP_PORT;

$mail->setFrom(SMTP_EMAIL, 'CampusConnect');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email - CampusConnect';
                $mail->Body = "
                    <h3>Welcome to CampusConnect!</h3>
                    <p>Please click below to verify your account:</p>
                    <a href='$verification_link'>Verify Account</a>
                ";
                $mail->send();

                $success = "Registration successful! Please check your email to verify your account.";

            } catch (Exception $e) {
    $error = "Mailer Error: " . $mail->ErrorInfo;
}

        } else {
            $error = "Registration failed.";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Student Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>

*{
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{
margin:0;
height:100vh;
display:flex;
}

/* LEFT PANEL (FORM) */

.left{
width:45%;
background:#121212;
color:white;
display:flex;
align-items:center;
justify-content:center;
padding:60px;
}

.form-box{
width:320px;
}

.form-box h2{
margin-bottom:5px;
}

.form-box p{
color:#aaa;
margin-bottom:25px;
font-size:14px;
}

/* INPUTS */

input{
width:100%;
background:transparent;
border:none;
border-bottom:1px solid #555;
padding:12px 5px;
margin-bottom:20px;
color:white;
outline:none;
}

input::placeholder{
color:#777;
}

/* PASSWORD ICON */

.password-wrapper{
position:relative;
}

.password-wrapper i{
position:absolute;
right:5px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
color:#aaa;
}

/* PASSWORD RULES */

.password-rules{
display:none;
background:#1e1e1e;
border:1px solid #444;
padding:10px;
border-radius:8px;
margin-bottom:15px;
font-size:13px;
}

.password-rules p{
margin:4px 0;
color:#ff7676;
}

.password-rules p.valid{
color:#5cff7a;
}

/* BUTTON */

button{
width:100%;
padding:12px;
border:none;
border-radius:8px;
background:linear-gradient(90deg,#8A00C4,#B14EFF);
color:white;
cursor:pointer;
font-weight:600;
margin-top:10px;
transition:0.3s;
}

button:hover{
opacity:0.9;
}

button:disabled{
background:#666;
cursor:not-allowed;
}

/* BOTTOM LINK */

.bottom{
margin-top:25px;
font-size:14px;
color:#aaa;
}

.bottom a{
color:#B14EFF;
text-decoration:none;
font-weight:600;
}

/* RIGHT PANEL */

.right{
width:55%;
background:linear-gradient(135deg,#8A00C4,#B14EFF);
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
color:white;
padding:80px;
text-align:center;
}

.right h1{
font-size:64px;
font-weight:800;
line-height:1.1;
margin-bottom:20px;
letter-spacing:-1px;
}

.right p{
font-size:16px;
opacity:0.9;
}

/* ERROR / SUCCESS */

.error{
color:#ff7b7b;
margin-bottom:15px;
}

.success{
color:#5cff7a;
margin-bottom:15px;
}

/* MOBILE */

@media(max-width:900px){

body{
flex-direction:column;
}

.left{
width:100%;
}

.right{
display:none;
}

}

</style>
</head>

<body>

<!-- LEFT SIDE -->

<div class="left">

<div class="form-box">

<h2>Register</h2>
<p>Student Registration</p>

<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(!empty($success)) echo "<div class='success'>$success</div>"; ?>

<form method="POST">

<input type="text" name="name" placeholder="Full Name" required>

<input type="email" name="email" placeholder="Email" required>

<div class="password-wrapper">
<input type="password" id="password" name="password" placeholder="Password" required>
<i class="fa-solid fa-eye" onclick="togglePassword('password',this)"></i>
</div>

<div id="password-rules" class="password-rules">
<p id="rule-length">• At least 8 characters</p>
<p id="rule-upper">• One uppercase letter</p>
<p id="rule-lower">• One lowercase letter</p>
<p id="rule-number">• One number</p>
<p id="rule-special">• One special character</p>
</div>

<div class="password-wrapper">
<input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
<i class="fa-solid fa-eye" onclick="togglePassword('confirm_password',this)"></i>
</div>

<button type="submit" name="register" id="registerBtn" disabled>
Register
</button>

</form>

<div class="bottom">
Already have an account? <a href="login.php">Log in</a>
</div>

</div>

</div>

<!-- RIGHT SIDE -->

<div class="right">

<h1>Welcome to<br>student portal</h1>

<p>Create your account to start using CampusConnect</p>

</div>

<script>

/* PASSWORD SHOW/HIDE */

function togglePassword(fieldId,icon){

let field=document.getElementById(fieldId);

if(field.type==="password"){
field.type="text";
icon.classList.replace("fa-eye","fa-eye-slash");
}else{
field.type="password";
icon.classList.replace("fa-eye-slash","fa-eye");
}

}

const passwordInput=document.getElementById("password");
const confirmInput=document.getElementById("confirm_password");
const registerBtn=document.getElementById("registerBtn");

const ruleLength=document.getElementById("rule-length");
const ruleUpper=document.getElementById("rule-upper");
const ruleLower=document.getElementById("rule-lower");
const ruleNumber=document.getElementById("rule-number");
const ruleSpecial=document.getElementById("rule-special");
const rulesBox=document.getElementById("password-rules");

passwordInput.addEventListener("focus",()=>rulesBox.style.display="block");
passwordInput.addEventListener("blur",()=>rulesBox.style.display="none");

function validatePassword(){

const password=passwordInput.value;
const confirm=confirmInput.value;

let valid=true;

if(password.length>=8){ruleLength.classList.add("valid")}else{ruleLength.classList.remove("valid");valid=false}

if(/[A-Z]/.test(password)){ruleUpper.classList.add("valid")}else{ruleUpper.classList.remove("valid");valid=false}

if(/[a-z]/.test(password)){ruleLower.classList.add("valid")}else{ruleLower.classList.remove("valid");valid=false}

if(/[0-9]/.test(password)){ruleNumber.classList.add("valid")}else{ruleNumber.classList.remove("valid");valid=false}

if(/[\W]/.test(password)){ruleSpecial.classList.add("valid")}else{ruleSpecial.classList.remove("valid");valid=false}

if(password!==confirm){valid=false}

registerBtn.disabled=!valid;

}

passwordInput.addEventListener("input",validatePassword);
confirmInput.addEventListener("input",validatePassword);

</script>

</body>
</html>