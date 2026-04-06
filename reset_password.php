<?php
include("config.php");

$error = "";
$valid = false;
$role = $_GET['role'] ?? 'student'; //  role detect

/* ===============================
   1️ Validate token
================================= */
if(isset($_GET['token'])){

    $token = $_GET['token'];

    $stmt = $conn->prepare("
        SELECT id, role 
        FROM users 
        WHERE reset_token = ? 
        AND reset_token_expiry > NOW()
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $valid = true;
        $user = $result->fetch_assoc();
        $role = $user['role']; // override with real role
    } else {
        $error = "Invalid or expired reset link.";
    }

    $stmt->close();
} else {
    $error = "Invalid reset request.";
}

/* ===============================
   2️ Reset password
================================= */
if(isset($_POST['reset'])){

    $token = $_POST['token'];
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $check = $conn->prepare("
        SELECT id, role 
        FROM users 
        WHERE reset_token = ? 
        AND reset_token_expiry > NOW()
    ");
    $check->bind_param("s", $token);
    $check->execute();
    $check_result = $check->get_result();

    if($check_result->num_rows !== 1){
        $error = "Reset link expired. Please request again.";
        $valid = false;
    } else {

        $row = $check_result->fetch_assoc();
        $user_role = $row['role'];

        if($password !== $confirm_password ||
           !preg_match('/[A-Z]/', $password) ||
           !preg_match('/[a-z]/', $password) ||
           !preg_match('/[0-9]/', $password) ||
           !preg_match('/[\W]/', $password) ||
           strlen($password) < 8
        ){
            $error = "Password must meet all criteria.";
            $valid = true;
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $update = $conn->prepare("
                UPDATE users 
                SET password=?, reset_token=NULL, reset_token_expiry=NULL 
                WHERE reset_token=?
            ");
            $update->bind_param("ss", $hashed, $token);
            $update->execute();

            if($user_role === "admin"){
                header("Location: admin_login.php?reset=success");
            } else {
                header("Location: login.php?reset=success");
            }
            exit();
        }
    }

    $check->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
}

/* INPUT */
.password-field{
position:relative;
}

.password-field input{
width:100%;
padding:12px;
margin-bottom:15px;
border-radius:10px;
border:1px solid #444;
background:#1a1a1a;
color:#fff;
}

.password-field input::placeholder{
color:#aaa;
}

/* FOCUS */
.password-field input:focus{
border-color:#B14EFF;
outline:none;
box-shadow:0 0 0 2px rgba(177,78,255,0.2);
}

/* EYE ICON */
.toggle-eye{
position:absolute;
right:15px;
top:50%;
transform:translateY(-50%);
cursor:pointer;
color:#B14EFF;
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

/* ERROR */
.error{
background:#2a0000;
color:#ff6b6b;
padding:10px;
border-radius:8px;
margin-bottom:15px;
text-align:center;
}

/* RULES */
.password-rules{
display:none;
background:#1a1a1a;
border:1px solid #444;
padding:10px;
border-radius:8px;
margin-bottom:15px;
font-size:14px;
color:#aaa;
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

<h2><?php echo ($role=="admin") ? "Admin Reset Password" : "Reset Password"; ?></h2>

<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

<?php if($valid): ?>

<form method="POST">

<input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">

<div class="password-field">
<input type="password" id="password" name="password" placeholder="New Password" required>
<i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
</div>

<div id="password-rules" class="password-rules">
• At least 8 characters<br>
• One uppercase<br>
• One lowercase<br>
• One number<br>
• One special character
</div>

<div class="password-field">
<input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
<i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('confirm_password', this)"></i>
</div>

<button type="submit" name="reset">Reset Password</button>

</form>

<?php endif; ?>

<div class="bottom">
<a href="<?php echo ($role=='admin') ? 'admin_login.php' : 'login.php'; ?>">
Back to Login
</a>
</div>

</div>

<script>

const passwordInput = document.getElementById("password");
const rules = document.getElementById("password-rules");

if(passwordInput){
passwordInput.addEventListener("focus", () => rules.style.display="block");
passwordInput.addEventListener("blur", () => rules.style.display="none");
}

function togglePassword(id, icon){
const input = document.getElementById(id);

if(input.type === "password"){
input.type = "text";
icon.classList.replace("fa-eye","fa-eye-slash");
}else{
input.type = "password";
icon.classList.replace("fa-eye-slash","fa-eye");
}
}

</script>

</body>
</html>
