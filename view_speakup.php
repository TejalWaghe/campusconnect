<?php
session_start();
include("config.php");
include("send_mail.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: login.php");
    exit();
}

/* UPDATE STATUS */
if(isset($_POST['update_status'])){
$id = intval($_POST['id']);
$new_status = strtolower(trim($_POST['status']));

$data = mysqli_fetch_assoc(mysqli_query($conn,"SELECT title,status FROM speakup WHERE id='$id'"));

$title = $data['title'];
$old_status = $data['status'];

if($new_status == "resolved"){
mysqli_query($conn,"UPDATE speakup SET status='resolved', resolved_at=NOW() WHERE id='$id'");
}else{
mysqli_query($conn,"UPDATE speakup SET status='$new_status', resolved_at=NULL WHERE id='$id'");
}

/* EMAIL */
$user = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT users.email, users.fullname
FROM speakup
JOIN users ON speakup.user_id = users.id
WHERE speakup.id='$id'
"));

if($user){
sendComplaintUpdate($user['email'],$user['fullname'],$title,$new_status);
}

/* LOG */
mysqli_query($conn,"
INSERT INTO admin_logs (admin_id,admin_name,action)
VALUES ('".$_SESSION['user_id']."','".$_SESSION['name']."',
'Updated \"$title\" from $old_status to $new_status')
");
}

/* FILTER */
$where="WHERE 1";

$category=$_GET['category'] ?? '';
$status_filter=$_GET['status'] ?? '';
$student=$_GET['student'] ?? '';
$sort=$_GET['sort'] ?? '';

if($category!="") $where.=" AND category='".mysqli_real_escape_string($conn,$category)."'";
if($status_filter!="") $where.=" AND status='".mysqli_real_escape_string($conn,$status_filter)."'";
if($student!="") $where.=" AND users.fullname LIKE '%".mysqli_real_escape_string($conn,$student)."%'";

$order = ($sort=="oldest") ? "ASC" : "DESC";

/* PAGINATION */
$limit=8;
$page=max(1,(int)($_GET['page'] ?? 1));
$offset=($page-1)*$limit;

$count=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total FROM speakup
JOIN users ON speakup.user_id=users.id $where
"))['total'];

$total_pages=ceil($count/$limit);

/* STATS */
$total=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM speakup"))['t'];
$pending=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM speakup WHERE status='pending'"))['t'];
$progress=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM speakup WHERE status='in progress'"))['t'];
$resolved=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM speakup WHERE status='resolved'"))['t'];
$rejected=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM speakup WHERE status='rejected'"))['t'];

/* DATA */
$result=mysqli_query($conn,"
SELECT speakup.*, users.fullname
FROM speakup
JOIN users ON speakup.user_id = users.id
$where
ORDER BY id $order
LIMIT $limit OFFSET $offset
");

include("header.php");
?>

<style>
body{background:#0f0f0f !important;color:white;}

.main-card{
background:#121212;
border-radius:16px;
box-shadow:0 15px 40px rgba(0,0,0,0.8);
}

.stat{
border-radius:12px;
padding:20px;
font-weight:600;
transition:0.3s;
}
.stat:hover{
transform:translateY(-5px);
box-shadow:0 10px 25px rgba(177,78,255,0.3);
}

/* INPUTS */
.form-control{
background:#1a1a1a !important;
border:1px solid #444 !important;
color:#fff !important;
}
.form-control::placeholder{color:#aaa;}

/* DROPDOWN FIX */
.form-select,
.action-dropdown{
background:#1a1a1a !important;
color:#fff !important;
border:1px solid #444 !important;

/*  IMPORTANT: more width + padding */
min-width:140px;
padding:6px 35px 6px 10px;

/* remove default arrow */
appearance:none;
-webkit-appearance:none;
-moz-appearance:none;

/* arrow */
background-image:url("data:image/svg+xml;utf8,<svg fill='%23B14EFF' height='16' viewBox='0 0 20 20' width='16' xmlns='http://www.w3.org/2000/svg'><path d='M5 7l5 6 5-6'/></svg>");
background-repeat:no-repeat;
background-position:right 10px center;
background-size:16px;
}

/* dropdown options */
.form-select option,
.action-dropdown option{
background:#1a1a1a;
color:#fff;
}


/* TABLE */
.table{background:#121212;color:#fff;}
.table th{background:#1a1a1a;border-color:#333;}
.table td{border-color:#333;color:#ddd;}
.table tbody tr:hover{background:#1f1f1f;}

/* BUTTONS */
.btn-primary{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
border:none;
}
.btn-secondary{background:#2a2a2a;border:none;}
.btn-success{border:none;}

.form-select:focus,
.form-control:focus{
border-color:#B14EFF !important;
box-shadow:0 0 0 2px rgba(177,78,255,0.2);
}

/* PAGINATION */
.pagination .page-link{
background:#1a1a1a;
border:1px solid #333;
color:#fff;
}
.pagination .active .page-link{
background:#B14EFF;
border-color:#B14EFF;
}

.modal-content{background:#121212;color:white;border:none;}

h2{
color:white;
}

.table,
.table td,
.table th{
background:#121212 !important;
color:#fff !important;
opacity:1 !important;
}

.table tbody tr{
opacity:1 !important;
}


</style>

<div class="card main-card p-4 mt-4">

<div class="d-flex justify-content-between mb-4">
<h2>All SpeakUp Messages</h2>
<a href="export_speakup.php" class="btn btn-success rounded-pill px-4">
Export Complaints
</a>
</div>

<!-- STATS -->
<div class="row text-center mb-4">
<div class="col-md-2"><div class="stat bg-dark text-white">Total<br><?php echo $total ?></div></div>
<div class="col-md-2"><div class="stat bg-warning text-dark">Pending<br><?php echo $pending ?></div></div>
<div class="col-md-2"><div class="stat bg-primary">In Progress<br><?php echo $progress ?></div></div>
<div class="col-md-2"><div class="stat bg-success">Resolved<br><?php echo $resolved ?></div></div>
<div class="col-md-2"><div class="stat bg-danger">Rejected<br><?php echo $rejected ?></div></div>
</div>

<!-- FILTER -->
<form class="row mb-4 g-3">

<div class="col-md-2">
<select name="category" class="form-select">
<option value="">All Categories</option>
<option <?php if($category=="Academic Issues") echo "selected"; ?>>Academic Issues</option>
<option <?php if($category=="Administrative/Facility Issues") echo "selected"; ?>>Administrative/Facility Issues</option>
<option <?php if($category=="Behavioral Misconduct") echo "selected"; ?>>Behavioral Misconduct</option>
</select>
</div>

<div class="col-md-2">
<select name="status" class="form-select">
<option value="">All Status</option>
<option value="pending" <?php if($status_filter=="pending") echo "selected"; ?>>Pending</option>
<option value="in progress" <?php if($status_filter=="in progress") echo "selected"; ?>>In Progress</option>
<option value="resolved" <?php if($status_filter=="resolved") echo "selected"; ?>>Resolved</option>
<option value="rejected" <?php if($status_filter=="rejected") echo "selected"; ?>>Rejected</option>
</select>
</div>

<div class="col-md-3">
<input type="text" name="student" class="form-control"
value="<?php echo htmlspecialchars($student); ?>"
placeholder="Search student">
</div>

<div class="col-md-2">
<select name="sort" class="form-select">
<option value="">Newest</option>
<option value="oldest" <?php if($sort=="oldest") echo "selected"; ?>>Oldest</option>
</select>
</div>

<div class="col-md-3">
<button class="btn btn-primary w-100">Filter</button>
</div>

</form>

<!-- TABLE -->
<table class="table table-hover">

<tr>
<th>Title</th>
<th>Category</th>
<th>Message</th>
<th>Student</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($row=mysqli_fetch_assoc($result)){
$status=strtolower($row['status']);

$badge = match($status){
"pending"=>"warning text-dark",
"in progress"=>"primary",
"on hold"=>"info text-dark",
"resolved"=>"success",
"rejected"=>"danger",
default=>"secondary"
};
?>
<!-- MODAL -->
<div class="modal fade" id="m<?php echo $row['id']; ?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <?php echo htmlspecialchars($row['title']); ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
      </div>

    </div>
  </div>
</div>
<tr>

<td><?php echo htmlspecialchars($row['title']); ?></td>
<td><?php echo htmlspecialchars($row['category']); ?></td>

<td>
<?php
$msg=htmlspecialchars($row['message']);
echo strlen($msg)>80?substr($msg,0,80)."..." : $msg;
?>
<br>
<button class="btn btn-sm btn-outline-primary mt-1"
data-bs-toggle="modal"
data-bs-target="#m<?php echo $row['id']; ?>">
View
</button>
</td>

<td><?php echo htmlspecialchars($row['fullname']); ?></td>

<td>
<span class="badge bg-<?php echo $badge ?>">
<?php echo ucfirst($status) ?>
</span>
</td>

<td><?php echo $row['message_date']; ?></td>

<td>
<form method="POST" class="d-flex">
<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<select name="status" class="form-select form-select-sm me-2 action-dropdown">
<option value="pending" <?php if($status=="pending") echo "selected"; ?>>Pending</option>
<option value="in progress" <?php if($status=="in progress") echo "selected"; ?>>In Progress</option>
<option value="resolved" <?php if($status=="resolved") echo "selected"; ?>>Resolved</option>
<option value="rejected" <?php if($status=="rejected") echo "selected"; ?>>Rejected</option>
</select>

<button name="update_status" class="btn btn-success btn-sm">
Update
</button>
</form>
</td>

</tr>

<?php } ?>

</table>

<!-- PAGINATION -->
<nav>
<ul class="pagination justify-content-center">
<?php for($i=1;$i<=$total_pages;$i++){ ?>
<li class="page-item <?php if($i==$page) echo 'active'; ?>">
<a class="page-link" href="?page=<?php echo $i ?>"><?php echo $i ?></a>
</li>
<?php } ?>
</ul>
</nav>

<a href="admin_dashboard.php" class="btn btn-secondary w-100">
Back to Dashboard
</a>

</div>

<?php include("footer.php"); ?>
