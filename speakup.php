<?php
session_start();
include("config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: login.php");
    exit();
}

$success = "";

/* DELETE MESSAGE */

if(isset($_GET['delete'])){

$id = intval($_GET['delete']);
$user_id = $_SESSION['user_id'];

$check = mysqli_query($conn,"
SELECT * FROM speakup
WHERE id='$id'
AND user_id='$user_id'
AND status='Pending'
");

if(mysqli_num_rows($check) > 0){

mysqli_query($conn,"DELETE FROM speakup WHERE id='$id'");
header("Location: speakup.php");
exit();

}else{
echo "<div class='alert alert-danger'>You cannot delete this message.</div>";
}

}

if(isset($_GET['success']) && $_GET['success'] == 1) {
$success = "Message submitted successfully!";
}

/* HANDLE FORM */

if(isset($_POST['submit'])) {

$title = mysqli_real_escape_string($conn, $_POST['title']);
$message = mysqli_real_escape_string($conn, $_POST['message']);
$category = mysqli_real_escape_string($conn, $_POST['category']);

$message_date = date("Y-m-d");

$user_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'];

$sql = "INSERT INTO speakup
(user_id, title, category, message_date, message, status, created_at)
VALUES
('$user_id', '$title', '$category', '$message_date', '$message', 'Pending', NOW())";

if(mysqli_query($conn, $sql)) {

require "send_mail.php";

$admin = mysqli_fetch_assoc(mysqli_query($conn,"SELECT email,fullname FROM users WHERE role='admin' LIMIT 1"));

if($admin){
    try {
        sendAdminNotification(
            $admin['email'],
            $admin['fullname'],
            $student_name,
            $title,
            $category,
            $message_date
        );
    } catch (Exception $e){
        // DO NOTHING → prevents crash
    }
}

header("Location: speakup.php?success=1");
exit();;

} else {
die("Error: " . mysqli_error($conn));
}

}

include("header.php");
?>

<style>

/* PAGE */

body{
background:#0f0f0f;
color:white;
}

/* CARD */

.card{
background:#121212;
border:none;
border-radius:14px;
box-shadow:0 10px 30px rgba(0,0,0,0.7);
}

/* LABELS */

label{
color:#cccccc;
font-weight:500;
}

/* INPUTS */

.form-control,
.form-select,
textarea{
background:#1a1a1a !important;
border:1px solid #444 !important;
color:#ffffff !important;
}

.form-control::placeholder{
color:#888;
}

.form-control:focus,
.form-select:focus,
textarea:focus{
background:#1a1a1a;
border-color:#B14EFF;
box-shadow:none;
color:white;
}

/* CHAR COUNTER */

#charCount{
color:#aaa;
font-size:13px;
}

/* BUTTON */

.btn-primary{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
border:none;
font-weight:600;
}

.btn-primary:hover{
opacity:0.9;
}

/* TABLE */

.table{
color:white;
}

.table th{
background:#1a1a1a;
border-color:#333;
}

.table td{
border-color:#333;
color:#ddd;
}

/* SECONDARY BUTTON */

.btn-secondary{
background:#2a2a2a;
border:none;
}

.btn-secondary:hover{
background:#3a3a3a;
}

/* BADGES */

.badge{
font-size:13px;
padding:6px 10px;
}
h2, h4{
color:#ffffff;
font-weight:600;
}

.form-label{
color:#cccccc;
}

small{
color:#aaaaaa;
}
</style>


<div class="card p-4 mt-4">

<h2 class="mb-4">SpeakUp Portal</h2>

<?php if($success != "") { ?>
<div class="alert alert-success">
<?php echo $success; ?>
</div>
<?php } ?>

<form method="POST">

<div class="mb-3">
<label class="form-label">Title</label>
<input type="text" name="title" class="form-control" placeholder="Short title of your message" required>
</div>

<div class="mb-3">
<label class="form-label">Your Message</label>
<textarea name="message" class="form-control" id="messageBox" rows="4" maxlength="500" required></textarea>
<small id="charCount">0 / 500 characters</small>
</div>

<div class="mb-3">
<label class="form-label">Category</label>
<select name="category" class="form-select" required>
<option value="">Select Category</option>
<option value="Academic Issues">Academic Issues</option>
<option value="Administrative/Facility Issues">Administrative/Facility Issues</option>
<option value="Behavioral Misconduct">Behavioral Misconduct</option>
</select>
</div>

<button type="submit" name="submit" class="btn btn-primary w-100 mt-2">
Submit Message
</button>

</form>

<hr>

<h4>Your Submitted Messages</h4>

<?php
$student_id = $_SESSION['user_id'];

$active_query = "
SELECT * FROM speakup
WHERE user_id = '$student_id'
AND (
status != 'Resolved'
OR resolved_at >= NOW() - INTERVAL 15 DAY
OR resolved_at IS NULL
)
ORDER BY id DESC
";

$active_result = mysqli_query($conn, $active_query);
?>

<table class="table table-dark table-hover mt-3">

<tr>
<th>ID</th>
<th>Title</th>
<th>Category</th>
<th>Message</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($active_result)) { ?>

<tr>

<td><?php echo "CC-".str_pad($row['id'],4,"0",STR_PAD_LEFT); ?></td>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo htmlspecialchars($row['category']); ?></td>

<td>
<?php
$msg = htmlspecialchars($row['message']);
echo strlen($msg) > 80 ? substr($msg,0,80)."..." : $msg;
?>
</td>

<td>

<?php
$status = strtolower($row['status']);

if($status=="pending") $badge="warning text-dark";
elseif($status=="in progress") $badge="primary";
elseif($status=="on hold") $badge="info text-dark";
elseif($status=="resolved") $badge="success";
elseif($status=="rejected") $badge="danger";
else $badge="secondary";
?>

<span class="badge bg-<?php echo $badge; ?>">
<?php echo ucwords($status); ?>
</span>

</td>

<td><?php echo htmlspecialchars($row['message_date']); ?></td>

<td>

<?php if($status == "pending"){ ?>

<a href="speakup.php?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm rounded-pill"
onclick="return confirm('Are you sure you want to delete this message?');">
Delete
</a>

<?php } else { ?>

<span class="text-muted">Locked</span>

<?php } ?>

</td>

</tr>

<?php } ?>

</table>

<button onclick="toggleArchive()" class="btn btn-secondary w-100 mb-3">
View Archived Messages
</button>

<?php

$archive_query = "
SELECT * FROM speakup
WHERE user_id = '$student_id'
AND status = 'Resolved'
AND resolved_at < NOW() - INTERVAL 15 DAY
ORDER BY resolved_at DESC
";

$archive_result = mysqli_query($conn, $archive_query);

?>

<div id="archiveSection" style="display:none;">

<h4>Archived Messages</h4>

<table class="table table-dark table-hover mt-3">

<tr>
<th>ID</th>
<th>Title</th>
<th>Category</th>
<th>Message</th>
<th>Status</th>
<th>Resolved On</th>
</tr>

<?php while($row = mysqli_fetch_assoc($archive_result)) { ?>

<tr>

<td><?php echo "CC-".str_pad($row['id'],4,"0",STR_PAD_LEFT); ?></td>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo htmlspecialchars($row['category']); ?></td>

<td>
<?php
$msg = htmlspecialchars($row['message']);
echo strlen($msg) > 80 ? substr($msg,0,80)."..." : $msg;
?>
</td>

<td>
<span class="badge bg-success">Resolved</span>
</td>

<td><?php echo date("d M Y", strtotime($row['resolved_at'])); ?></td>

</tr>

<?php } ?>

</table>

</div>

<a href="student_dashboard.php" class="btn btn-secondary w-100">
Back to Dashboard
</a>

</div>

<script>

function toggleArchive(){
var section = document.getElementById("archiveSection");
section.style.display = (section.style.display === "none") ? "block" : "none";
}

const textarea = document.getElementById("messageBox");
const counter = document.getElementById("charCount");

textarea.addEventListener("input", function(){
counter.textContent = textarea.value.length + " / 500 characters";
});

</script>

<?php include("footer.php"); ?>