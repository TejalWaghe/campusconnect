<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>CampusConnect</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>

*{
box-sizing:border-box;
font-family:'Segoe UI', sans-serif;
}

/* Page Background (same as register) */
body{
margin:0;
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(135deg,#8A00C4,#B14EFF);
}

/* Vertical Rectangle Panel */
.panel{
background:#121212; /* EXACT same black as register */
width:420px;
height:520px;
border-radius:20px;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
box-shadow:0 25px 50px rgba(0,0,0,0.4);
text-align:center;
}

/* Title */
.panel h1{
color:white;
font-size:34px;
font-weight:700;
margin-bottom:40px;
line-height:1.4;
}

/* Buttons */
.btn{
width:230px;
padding:14px;
margin:12px;
border-radius:8px;
border:none;
font-size:16px;
font-weight:600;
cursor:pointer;
text-decoration:none;
color:white;
transition:0.25s;
}

/* Student Button */
.student-btn{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
}

.student-btn:hover{
transform:translateY(-3px);
box-shadow:0 10px 25px rgba(0,0,0,0.35);
}

/* Admin Button */
.admin-btn{
background:#3b0061;
}

.admin-btn:hover{
background:#2a0046;
transform:translateY(-3px);
box-shadow:0 10px 25px rgba(0,0,0,0.35);
}

</style>

</head>

<body>

<div class="panel">

<h1>
Welcome to<br>
CampusConnect
</h1>

<a href="register.php" class="btn student-btn">
I'm a Student
</a>

<a href="admin_login.php" class="btn admin-btn">
I'm an Admin
</a>

</div>

</body>
</html>