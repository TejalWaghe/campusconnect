<?php
include("config.php");

$message = "";
$success = false;
$login_page = "login.php";

if(isset($_GET['token'])) {

    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id, role FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){

        $row = $result->fetch_assoc();
        $role = $row['role'];

        $login_page = ($role == "admin") ? "admin_login.php" : "login.php";

        $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
        $update->bind_param("s", $token);
        $update->execute();

        $message = "Email verified successfully!";
        $success = true;

    } else {
        $message = "Invalid or expired verification link.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Email Verification</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<?php if($success): ?>
<meta http-equiv="refresh" content="4;url=<?php echo $login_page; ?>">
<?php endif; ?>

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
text-align:center;
}

/* TITLE */
h2{
margin-bottom:20px;
}

/* SUCCESS */
.success{
background:#0d2f1f;
color:#4caf50;
padding:12px;
border-radius:10px;
margin-bottom:15px;
}

/* ERROR */
.error{
background:#2a0000;
color:#ff5252;
padding:12px;
border-radius:10px;
margin-bottom:15px;
}

/* BUTTON */
button{
padding:12px 20px;
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

/* SUBTEXT */
.subtext{
color:#aaa;
font-size:14px;
margin-bottom:15px;
}

</style>
</head>

<body>

<div class="card">

<h2>Email Verification</h2>

<?php if($success): ?>
    <div class="success"><?php echo $message; ?></div>
    <div class="subtext">Redirecting to login in a few seconds...</div>

    <a href="<?php echo $login_page; ?>">
        <button>Go to Login</button>
    </a>

<?php else: ?>
    <div class="error"><?php echo $message; ?></div>

    <a href="login.php">
        <button>Back to Login</button>
    </a>
<?php endif; ?>

</div>

</body>
</html>