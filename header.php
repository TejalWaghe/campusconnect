<!DOCTYPE html>
<html>
<head>
<title>CampusConnect</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* PAGE BACKGROUND */

body{
background:#0f0f0f;
color:white;
}

/* NAVBAR */

.navbar{
background:#000 !important;
border-bottom:1px solid #222;
}

/* BRAND */

.navbar-brand{
color:white !important;
font-size:20px;
}

/* NOTIFICATION BUTTON */

.notification-btn{
background:#1a1a1a;
border:none;
color:white;
position:relative;
}

.notification-btn:hover{
background:#2a2a2a;
}

/* DROPDOWN MENU */

.dropdown-menu{
background:#121212;
border:1px solid #333;
color:white;
}

.dropdown-item{
color:#ddd;
}

.dropdown-item:hover{
background:#1f1f1f;
color:white;
}

.dropdown-header{
color:#aaa;
}

/* LOGOUT BUTTON */

.logout-btn{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
border:none;
color:white;
font-weight:600;
}

.logout-btn:hover{
opacity:0.9;
}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg">
<div class="container">

<a class="navbar-brand fw-bold" href="#">
CampusConnect
</a>

<div class="ms-auto d-flex align-items-center">

<?php
if(isset($_SESSION['role']) && $_SESSION['role'] == "admin"){

$notif_query = "
SELECT speakup.title, users.fullname
FROM speakup
JOIN users ON speakup.user_id = users.id
WHERE speakup.status='pending'
ORDER BY speakup.id DESC
LIMIT 5
";

$notif_result = mysqli_query($conn,$notif_query);
$notif_count = mysqli_num_rows($notif_result);
?>

<!-- Notification Bell -->

<div class="dropdown me-3">

<a class="btn notification-btn dropdown-toggle"
href="#"
role="button"
data-bs-toggle="dropdown">

🔔

<?php if($notif_count > 0){ ?>

<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
<?php echo $notif_count; ?>
</span>

<?php } ?>

</a>

<ul class="dropdown-menu dropdown-menu-end">

<li class="dropdown-header">New Messages</li>

<?php if($notif_count == 0){ ?>

<li class="dropdown-item text-muted">
No new messages
</li>

<?php } ?>

<?php while($notif = mysqli_fetch_assoc($notif_result)){ ?>

<li class="dropdown-item">
<strong><?php echo $notif['fullname']; ?></strong><br>
<small><?php echo $notif['title']; ?></small>
</li>

<?php } ?>

<li><hr class="dropdown-divider"></li>

<li>
<a class="dropdown-item text-center" href="view_speakup.php">
View All Messages
</a>
</li>

</ul>

</div>

<?php } ?>

<!-- Logout Button -->

<?php if(isset($_SESSION['role'])) { ?>
<a href="logout.php" class="btn logout-btn btn-sm">Logout</a>
<?php } ?>

</div>

</div>
</nav>

<div class="container mt-4">