<?php
session_start();
include("config.php");

$error = "";

if(isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password, is_verified FROM users WHERE email=? AND role='student'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){

        $row = $result->fetch_assoc();

        if(password_verify($password, $row['password'])){

            if($row['is_verified'] == 0){
                $error = "Please verify your email before logging in.";
            } else {

                $_SESSION['user_id'] = $row['id'];
                $_SESSION['name'] = $row['fullname'];
                $_SESSION['role'] = "student";

                header("Location: student_dashboard.php");
                exit();
            }

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "Student account not found!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Login</title>
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

/* LEFT PANEL */

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

/* BOTTOM */

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

/* FORGOT */

.forgot{
margin-top:-10px;
margin-bottom:15px;
}

.forgot a{
font-size:13px;
color:#888;
text-decoration:none;
}

.forgot a:hover{
text-decoration:underline;
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

/* ERROR */

.error{
color:#ff7b7b;
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

<!-- LEFT -->

<div class="left">

<div class="form-box">

<h2>Login</h2>
<p>Student Login</p>

<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">

<input type="email" name="email" placeholder="Email" required>

<div class="password-wrapper">
<input type="password" id="password" name="password" placeholder="Password" required>
<i class="fa-solid fa-eye" onclick="togglePassword()"></i>
</div>

<div class="forgot">
<a href="forgot_password.php?role=student">Forgot Password?</a>
</div>

<button type="submit" name="login">
Login
</button>

</form>

<div class="bottom">
Don't have an account? <a href="register.php">Register</a>
</div>

</div>

</div>

<!-- RIGHT -->

<div class="right">

<h1>Welcome to<br>student portal</h1>

<p>Login to access your CampusConnect account</p>

</div>

<script>

function togglePassword(){

const field = document.getElementById("password");
const icon = document.querySelector(".password-wrapper i");

if(field.type==="password"){
field.type="text";
icon.classList.replace("fa-eye","fa-eye-slash");
}else{
field.type="password";
icon.classList.replace("fa-eye-slash","fa-eye");
}

}

</script>

</body>
</html>