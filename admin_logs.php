<?php
session_start();
include("config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
header("Location: login.php");
exit();
}

$query = "SELECT * FROM admin_logs ORDER BY created_at DESC";
$result = mysqli_query($conn,$query);

include("header.php");
?>

<style>

/* FORCE DARK TABLE FIX */

.table{
    color:#ffffff !important;
    background:#121212 !important;
}

/* HEADER */
.table thead th{
    background:#1a1a1a !important;
    color:#ffffff !important;
    border-color:#333 !important;
    font-weight:600;
}

/* BODY CELLS */
.table tbody td{
    background:#121212 !important;
    color:#e6e6e6 !important;
    border-color:#333 !important;
}

/* STRONG TEXT (ADMIN NAME) */
.table tbody td strong{
    color:#ffffff !important;
}

/* HOVER */
.table tbody tr:hover td{
    background:#1f1f1f !important;
    color:#ffffff !important;
}

/* ZEBRA ROWS */
.table tbody tr:nth-child(even) td{
    background:#151515 !important;
}

/* DATE + TIME */
.table td:nth-last-child(1),
.table td:nth-last-child(2){
    color:#bbbbbb !important;
    font-size:14px;
}

/* REMOVE BOOTSTRAP LIGHT EFFECT */
.table-striped>tbody>tr:nth-of-type(odd)>*{
    background-color:#121212 !important;
}

/* ROW HOVER */
.table tbody tr:hover{
background:#1a1a1a;
}

/* SCROLLABLE TABLE */
.table-container{
max-height:450px;
overflow-y:auto;
border-radius:10px;
}

/* SCROLLBAR */
.table-container::-webkit-scrollbar{
width:6px;
}
.table-container::-webkit-scrollbar-thumb{
background:#444;
border-radius:10px;
}

/* BUTTON */
.btn-secondary{
background:#2a2a2a;
border:none;
}

.btn-secondary:hover{
background:#3a3a3a;
}

/* EMPTY STATE */
.alert{
background:#1a1a1a;
border:none;
color:#ccc;
}

.card{
background:#121212 !important;
border:none;
color:white;
}
</style>

<div class="card p-4 mt-4">

<h2 class="mb-4">Admin Activity Logs</h2>

<?php if(mysqli_num_rows($result) > 0){ ?>

<div class="table-container">

<table class="table table-hover">

<thead>
<tr>
<th>Admin</th>
<th>Action</th>
<th>Date</th>
<th>Time</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($result)) { ?>

<tr>

<td>
<strong><?php echo htmlspecialchars($row['admin_name']); ?></strong>
</td>

<td>
<?php echo htmlspecialchars($row['action']); ?>
</td>

<td>
<?php echo date("d M Y",strtotime($row['created_at'])); ?>
</td>

<td>
<?php echo date("H:i",strtotime($row['created_at'])); ?>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } else { ?>

<div class="alert text-center">
No admin activity found.
</div>

<?php } ?>

<br>

<a href="admin_dashboard.php" class="btn btn-secondary w-100">
Back to Dashboard
</a>

</div>

<?php include("footer.php"); ?>