<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: login.php");
    exit();
}

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

.icon{
font-size:30px;
background:linear-gradient(90deg,#8A00C4,#B14EFF);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
}

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
Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>
</h2>

<p class="text-center dashboard-subtitle mb-4">
Student Dashboard
</p>

<div class="row g-4">

<div class="col-md-6">

<a href="speakup.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">🗣️</div>

<h5>SpeakUp Portal</h5>

<p>Submit complaints or suggestions to administration.</p>

</div>
</a>

</div>

<div class="col-md-6">

<a href="lostfound.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">📦</div>

<h5>Lost & Found</h5>

<p>Upload items you lost or found in campus.</p>

</div>
</a>

</div>

<div class="col-md-6">

<a href="view_lostfound.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">🔎</div>

<h5>View Lost Items</h5>

<p>Browse items posted by other students.</p>

</div>
</a>

</div>

<div class="col-md-6">

<a href="vote.php" style="text-decoration:none;">
<div class="feature-card">

<div class="icon">🗳️</div>

<h5>Campus Polls</h5>

<p>Vote in polls created by administration.</p>

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