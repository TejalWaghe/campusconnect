<?php
session_start();
include("config.php");
include("smtp_config.php");
include("send_mail.php"); // ✅ IMPORTANT

/* 🔥 ROLE DETECT */
$role = $_GET['role'] ?? 'student'; // default student

$message = "";
$success = false;

if(isset($_POST['submit'])){

$email = trim($_POST['email']);

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $message = "Invalid email format.";
} else {

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $token = bin2hex(random_bytes(32));

        $update = $conn->prepare("
        UPDATE users 
        SET reset_token = ?, 
        reset_token_expiry = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
        WHERE email = ?
        ");
        $update->bind_param("ss", $token, $email);
        $update->execute();

        /* 🔥 PASS ROLE IN RESET LINK */
        $reset_link = BASE_URL . "/reset_password.php?token=".$token."&role=".$role;

        // ✅ EMAIL CONTENT
        $subject = "Reset Your Password - CampusConnect";

        $body = "
        <h3>Password Reset Request</h3>
        <p>Hello,</p>
        <p>Click below to reset your password:</p>
        <br>
        <a href='$reset_link' style='
            background:#B14EFF;
            color:white;
            padding:10px 20px;
            text-decoration:none;
            border-radius:5px;
        '>Reset Password</a>
        <br><br>
        <p>This link expires in 30 minutes.</p>
        ";

        // ✅ SEND EMAIL USING BREVO API
        $sent = sendMail($email, "User", $subject, $body);

        if($sent){
            $message = "Reset link sent to your email.";
            $success = true;
        } else {
            $message = "Failed to send reset email.";
        }

        $update->close();

    } else {
        // Security message (do not reveal if email exists)
        $message = "If this email exists, a reset link has been sent.";
        $success = true;
    }

    $stmt->close();
}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

/* BACKGROUND */
body{
margin:0;
height:100vh;
display:flex;
justify-content:center;
align-items:center;
font-family:Arial;
background:#0f0f0f;
color:white;
}

/* CARD */
.card{
background:#121212;
padding:40px;
width:380px;
border-radius:16px;
box-shadow:0 15px 40px rgba(0,0,0,0.8);
}

/* TITLE */
h2{
text-align:center;
margin-bottom:25px;
color:#fff;
}

/* INPUT */
input{
width:100%;
padding:12px;
margin-bottom:15px;
border-radius:10px;
border:1px solid #444;
background:#1a1a1a;
color:#fff;
}

input::placeholder{
color:#aaa;
}

input:focus{
border-color:#B14EFF;
outline:none;
box-shadow:0 0 0 2px rgba(177,78,255,0.2);
}

/* BUTTON */
button{
width:100%;
padding:12px;
background:linear-gradient(90deg,#8A00C4,#B14EFF);
color:white;
border:none;
border-radius:10px;
cursor:pointer;
font-weight:bold;
}

button:hover{
opacity:0.9;
}

/* MESSAGE */
.message{
text-align:center;
margin-bottom:15px;
font-weight:500;
color:<?php echo $success ? '#4caf50' : '#ff5252'; ?>;
}

/* LINK */
.bottom{
text-align:center;
margin-top:15px;
}

.bottom a{
color:#B14EFF;
text-decoration:none;
font-weight:bold;
}

.bottom a:hover{
text-decoration:underline;
}

</style>
</head>

<body>

<div class="card">

<h2><?php echo ($role == 'admin') ? 'Admin Forgot Password' : 'Forgot Password'; ?></h2>

<?php if($message != "") echo "<div class='message'>$message</div>"; ?>

<!-- 🔥 KEEP ROLE IN FORM -->
<form method="POST" action="?role=<?php echo $role; ?>">
<input type="email" name="email" placeholder="Enter your email" required>
<button type="submit" name="submit">Send Reset Link</button>
</form>

<div class="bottom">
Back to 
<a href="<?php echo ($role == 'admin') ? 'admin_login.php' : 'login.php'; ?>">
Login
</a>
</div>

</div>

</body>
</html>
