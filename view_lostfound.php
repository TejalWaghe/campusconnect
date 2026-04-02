<?php
session_start();
include("config.php");

if(!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

/* PAGINATION */
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

/* FILTER */
$where = [];

if(!empty($_GET['category'])){
    $category = mysqli_real_escape_string($conn,$_GET['category']);
    $where[] = "lost_and_found.category='$category'";
}

if(!empty($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
    $where[] = "lost_and_found.description LIKE '%$search%'";
}

$where_sql = count($where) ? "WHERE ".implode(" AND ",$where) : "";

/* TOTAL COUNT */
$count_query = "
SELECT COUNT(*) as total
FROM lost_and_found
JOIN users ON lost_and_found.user_id = users.id
$where_sql
";

$count_result = mysqli_query($conn,$count_query);
$total = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total / $limit);

/* MAIN QUERY */
$sql = "
SELECT lost_and_found.*, users.fullname
FROM lost_and_found
JOIN users ON lost_and_found.user_id = users.id
$where_sql
ORDER BY lost_and_found.created_at DESC
LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn,$sql);

include("header.php");
?>

<style>

/* BACKGROUND */
body{
background:#0f0f0f;
color:white;
}

/* OUTER CARD (CONTAINER) */
.main-card{
background:#121212;
border:none;
border-radius:16px;
box-shadow:0 15px 40px rgba(0,0,0,0.8);
}

/* INNER CARDS (ITEMS) */
.item-card{
background:#181818;
border:none;
border-radius:14px;
overflow:hidden;
transition:all 0.25s ease;
}

/* HOVER EFFECT */
.item-card:hover{
transform:translateY(-5px) scale(1.02);
box-shadow:0 10px 30px rgba(177,78,255,0.2);
}

/* IMAGE */
.item-card img{
border-radius:14px 14px 0 0;
}

/* TEXT */
.card-body{
color:white;
}

.card-body p,
.card-body small{
color:#cccccc;
}

.text-muted{
color:#aaaaaa !important;
}

/* BADGE */
.badge{
font-size:12px;
padding:6px 10px;
border-radius:20px;
}

/* INPUT */
.form-control,
.form-select{
background:#1a1a1a !important;
border:1px solid #444 !important;
color:white !important;
}

/* FOCUS */
.form-control:focus,
.form-select:focus{
border-color:#B14EFF;
box-shadow:none;
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

/* PAGINATION */
.pagination .page-link{
background:#1a1a1a;
border:1px solid #444;
color:white;
}

.pagination .active .page-link{
background:#B14EFF;
border:none;
}

/* TITLE */
h2{
color:white;
}

.form-control::placeholder{
color:#bbb !important;
opacity:1;
}
</style>

<div class="card main-card p-4 mt-4">

<h2 class="text-center mb-4">All Lost & Found Items</h2>

<!-- SEARCH -->
<form method="GET" class="text-center mb-4">

<input type="text" name="search"
placeholder="Search items..."
class="form-control w-50 d-inline"
value="<?php echo $_GET['search'] ?? ''; ?>">

<br><br>

<select name="category" class="form-select w-50 d-inline">
<option value="">All Categories</option>
<option value="Electronics">Electronics</option>
<option value="Accessories">Accessories</option>
<option value="Documents">Documents</option>
<option value="Clothing">Clothing</option>
<option value="Books & Stationery">Books & Stationery</option>
<option value="Others">Others</option>
</select>

<br>

<button type="submit" class="btn btn-primary mt-2">
Search / Filter
</button>

</form>

<p class="text-center text-muted">
Showing <?php echo mysqli_num_rows($result); ?> of <?php echo $total; ?> items
</p>

<?php if(mysqli_num_rows($result) > 0){ ?>

<div class="row">

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<?php
$type = $row['item_type']=="Lost" ? "danger" : "success";
$icon = $row['item_type']=="Lost" ? "🔴" : "🟢";

$image = $row['image'];

$desc = htmlspecialchars($row['description']);
$desc = strlen($desc)>120 ? substr($desc,0,120)."..." : $desc;
?>

<div class="col-md-4 mb-4">

<div class="card item-card h-100">

<div class="position-absolute m-2">
<span class="badge bg-<?php echo $type; ?>">
<?php echo $icon." ".$row['item_type']; ?>
</span>
</div>

<img src="<?php echo !empty($row['image']) ? $row['image'] : 'https://via.placeholder.com/400x220'; ?>"
style="height:220px; object-fit:cover;">

<div class="card-body d-flex flex-column">

<small class="text-muted">
ID: <?php echo "LF-".str_pad($row['id'],3,"0",STR_PAD_LEFT); ?>
</small>

<h5><?php echo htmlspecialchars($row['category']); ?></h5>

<p><?php echo nl2br($desc); ?></p>

<p class="text-muted">
Date: <?php echo date("d M Y",strtotime($row['item_date'])); ?>
</p>

<p class="text-muted mt-auto">
Uploaded By: <?php echo htmlspecialchars($row['fullname']); ?>
</p>

</div>

</div>

</div>

<?php } ?>

</div>

<!-- PAGINATION -->
<nav>
<ul class="pagination justify-content-center">

<?php for($i=1;$i<=$total_pages;$i++){ ?>

<li class="page-item <?php if($i==$page) echo 'active'; ?>">
<a class="page-link"
href="?page=<?php echo $i; ?>&search=<?php echo $_GET['search'] ?? ''; ?>&category=<?php echo $_GET['category'] ?? ''; ?>">
<?php echo $i; ?>
</a>
</li>

<?php } ?>

</ul>
</nav>

<?php } else { ?>

<div class="alert alert-warning text-center">
No items found.
</div>

<?php } ?>

<a href="<?php echo $_SESSION['role']=="admin" ? 'admin_dashboard.php':'student_dashboard.php'; ?>"
class="btn btn-secondary w-100 mt-3">
Back to Dashboard
</a>

</div>

<?php include("footer.php"); ?>
