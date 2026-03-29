<?php
session_start();
include("config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: login.php");
    exit();
}

/* Get Admin Name */
$admin_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "SELECT fullname FROM users WHERE id='$admin_id'");
$admin = mysqli_fetch_assoc($query);

$admin_name = $admin['fullname'];

include("header.php");
?>

<style>

.dashboard-card{
background:#121212;
border:none;
border-radius:14px;
box-shadow:0 10px 30px rgba(0,0,0,0.7);
}

.dashboard-title{
color:white;
font-weight:600;
}

.dashboard-subtitle{
color:#aaa;
}

/* FEATURE CARDS */

.feature-card{
background:#1a1a1a;
border-radius:12px;
padding:25px;
text-align:center;
transition:0.25s;
cursor:pointer;
border:1px solid #2a2a2a;
}

.feature-card:hover{
transform:translateY(-4px);
border-color:#8A00C4;
box-shadow:0 6px 20px rgba(138,0,196,0.3);
}

.feature-card h5{
color:white;
margin-top:10px;
}

.feature-card p{
color:#aaa;
font-size:14px;
}

/* ICON STYLE */

.icon{
font-size:30px;
background:linear-gradient(90deg,#8A00C4,#B14EFF);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

/* LOGOUT BUTTON */

.logout-dashboard{
background:#2a2a2a;
border:none;
color:white;
}

.logout-dashboard:hover{
background:#3a3a3a;
}

</style>


<div class="card dashboard-card p-4">

<h2 class="text-center dashboard-title mb-1">
Welcome, <?php echo htmlspecialchars($admin_name); ?>
</h2>

<p class="text-center dashboard-subtitle mb-4">
Admin Dashboard
</p>

<div class="row g-4">

<div class="col-md-6">

<a href="view_speakup.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">📩</div>

<h5>SpeakUp Messages</h5>

<p>Review and manage student complaints.</p>

</div>
</a>

</div>

<div class="col-md-6">

<a href="view_lostfound.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">📦</div>

<h5>Lost & Found</h5>

<p>View and manage lost items on campus.</p>

</div>
</a>

</div>

<div class="col-md-6">

<a href="create_poll.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">🗳️</div>

<h5>Create Poll</h5>

<p>Create polls for students to vote.</p>

</div>
</a>

</div>

<div class="col-md-6">

<a href="view_results.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">📊</div>

<h5>Poll Results</h5>

<p>View voting results and statistics.</p>

</div>
</a>

</div>

<div class="col-md-12">

<a href="admin_logs.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">📜</div>

<h5>Admin Activity Logs</h5>

<p>Track administrative actions performed in the system.</p>

</div>
</a>

</div>

</div>

<hr class="mt-4">

<a href="logout.php" class="btn logout-dashboard w-100 mt-2">
Logout
</a>

</div>

<?php include("footer.php"); ?>