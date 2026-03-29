<?php
session_start();
include("config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: login.php");
    exit();
}

$poll_query = "
SELECT * FROM polls
WHERE expiry_date >= CURDATE()
ORDER BY created_at DESC
";

$poll_result = mysqli_query($conn,$poll_query);

include("header.php");
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* DARK THEME */
body{
background:#0f0f0f;
color:white;
}

/* MAIN CARD */
.card{
background:#121212;
border:none;
border-radius:14px;
box-shadow:0 10px 30px rgba(0,0,0,0.7);
}

/* TEXT */
h2,h4,h5{
color:white;
}

.text-muted{
color:#aaa !important;
}

/* BADGES */
.badge{
font-size:12px;
padding:6px 10px;
}

/* PROGRESS */
.progress{
height:12px;
background:#1a1a1a;
border-radius:10px;
}

.progress-bar{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
}

/* TABLE */
.table{
color:#ffffff !important;
background:#121212 !important;
}

.table th{
background:#1a1a1a !important;
color:#ffffff !important;
border-color:#333 !important;
}

.table td{
background:#121212 !important;
color:#dddddd !important;
border-color:#333 !important;
}

.table tbody tr:hover{
background:#1a1a1a !important;
}

h5{
color:#ffffff;
font-weight:600;
}

/* BUTTON */
.btn-secondary{
background:#2a2a2a;
border:none;
}

.progress span{
color:#fff;
font-size:12px;
}

/* OPTION LABEL TEXT */
.d-flex strong{
color:#ffffff !important;
font-weight:500;
}

/* RIGHT SIDE TEXT (votes %) */
.d-flex span{
color:#cccccc !important;
}

</style>

<div class="card p-4 mt-4">

<h2 class="text-center mb-4">Poll Results</h2>

<?php
if(mysqli_num_rows($poll_result) == 0){
echo "<div class='alert alert-info text-center'>No active poll results available.</div>";
}
?>

<?php while($poll = mysqli_fetch_assoc($poll_result)){

$poll_id = $poll['id'];

/* COUNT VOTES */
$count_query = "
SELECT selected_option, COUNT(*) as total
FROM votes
WHERE poll_id='$poll_id'
GROUP BY selected_option
";

$count_result = mysqli_query($conn,$count_query);

$count = [1=>0,2=>0,3=>0,4=>0,5=>0];

while($row = mysqli_fetch_assoc($count_result)){
$count[$row['selected_option']] = $row['total'];
}

$total_votes = array_sum($count);

$percent = [];
foreach($count as $k=>$v){
$percent[$k] = $total_votes > 0 ? round(($v/$total_votes)*100) : 0;
}

/* VOTERS */
$voters_query = "
SELECT users.fullname, votes.selected_option
FROM votes
JOIN users ON votes.user_id = users.id
WHERE votes.poll_id='$poll_id'
";

$voters_result = mysqli_query($conn,$voters_query);

$options = [
1=>$poll['option1'],
2=>$poll['option2'],
3=>$poll['option3'],
4=>$poll['option4'],
5=>$poll['option5']
];
?>

<hr class="my-4">

<div class="text-center mb-4">

<h4><?php echo htmlspecialchars($poll['question']); ?></h4>

<div class="mt-2">

<span class="badge bg-primary">
Created by: <?php echo htmlspecialchars($poll['created_by']); ?>
</span>

<span class="badge bg-secondary">
Created: <?php echo date("d M Y",strtotime($poll['created_at'])); ?>
</span>

<span class="badge bg-danger">
Expires: <?php echo date("d M Y",strtotime($poll['expiry_date'])); ?>
</span>

</div>

<p class="text-muted mt-2">
Total Votes: <strong><?php echo $total_votes; ?></strong>
</p>

</div>

<!-- PROGRESS BARS -->

<?php foreach($options as $i=>$opt){
if(empty($opt)) continue;
?>

<div class="mb-3">

<div class="d-flex justify-content-between">
<strong><?php echo htmlspecialchars($opt); ?></strong>
<span><?php echo $count[$i]; ?> votes (<?php echo $percent[$i]; ?>%)</span>
</div>

<div class="progress mt-1">
<div class="progress-bar"
style="width:<?php echo $percent[$i]; ?>%">
</div>
</div>

</div>

<?php } ?>

<!-- PIE CHART -->

<div class="my-4 text-center">

<div style="max-width:300px;margin:auto;">
<canvas id="chart_<?php echo $poll_id; ?>"></canvas>
</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

new Chart(document.getElementById('chart_<?php echo $poll_id; ?>'), {

type: 'pie',

data: {
labels: [
<?php
foreach($options as $opt){
if(!empty($opt)){
echo "'".addslashes($opt)."',";
}
}
?>
],
datasets: [{
data: [
<?php
foreach($options as $i=>$opt){
if(!empty($opt)){
echo $count[$i].",";
}
}
?>
],
backgroundColor:[
'#B14EFF',
'#8A00C4',
'#0dcaf0',
'#ffc107',
'#dc3545'
]
}]
},

options:{
plugins:{
legend:{
position:'bottom',
labels:{
color:'#ffffff',
font:{
size:13
}
}
}
}
}

});

});
</script>

<!-- VOTERS -->

<h5 class="mt-4">Voter List</h5>

<?php if(mysqli_num_rows($voters_result) > 0){ ?>

<div class="table-responsive">

<table class="table table-hover text-center mt-2">

<tr>
<th>Student Name</th>
<th>Selected Option</th>
</tr>

<?php while($row = mysqli_fetch_assoc($voters_result)){ ?>

<tr>
<td><?php echo htmlspecialchars($row['fullname']); ?></td>
<td><?php echo htmlspecialchars($options[$row['selected_option']]); ?></td>
</tr>

<?php } ?>

</table>

</div>

<?php } else { ?>

<div class="alert alert-info text-center">
No votes yet.
</div>

<?php } ?>

<?php } ?>

<a href="admin_dashboard.php" class="btn btn-secondary w-100 mt-4">
Back to Dashboard
</a>

</div>

<?php include("footer.php"); ?>