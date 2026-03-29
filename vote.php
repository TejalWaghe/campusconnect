<?php
session_start();
include("config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* HANDLE VOTE */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    foreach($_POST as $key => $value){

        if(strpos($key,'vote_') === 0){

            $poll_id = intval(str_replace('vote_','',$key));
            $selected_option = intval($_POST['option_'.$poll_id]);

            $check = mysqli_query($conn,
            "SELECT * FROM votes WHERE poll_id='$poll_id' AND user_id='$user_id'");

            if(mysqli_num_rows($check) == 0){

                mysqli_query($conn,"
                INSERT INTO votes (poll_id,user_id,selected_option)
                VALUES ('$poll_id','$user_id','$selected_option')
                ");
            }

            header("Location: vote.php?voted=1");
            exit();
        }
    }
}

include("header.php");

/* GET POLLS */

$poll_query = "
SELECT * FROM polls
WHERE expiry_date >= CURDATE()
AND id NOT IN (
    SELECT poll_id FROM votes WHERE user_id='$user_id'
)
ORDER BY created_at DESC
";

$poll_result = mysqli_query($conn,$poll_query);
?>

<style>

/* SAME SPEAKUP THEME */

body{
background:#0f0f0f;
color:white;
}

.main-card{
background:#121212;
border:none;
border-radius:16px;
box-shadow:0 15px 40px rgba(0,0,0,0.8);
}

/* POLL CARDS */
.poll-card{
background:#181818;
border:none;
border-radius:14px;
padding:20px;
transition:0.25s;
}

.poll-card:hover{
transform:translateY(-4px);
box-shadow:0 10px 30px rgba(177,78,255,0.2);
}

/* TEXT */
h3, h4{
color:white;
}

.form-check-label{
color:#ddd;
}

/* RADIO */
.form-check-input{
background:#1a1a1a;
border:1px solid #555;
}

.form-check-input:checked{
background-color:#B14EFF;
border-color:#B14EFF;
}

/* BADGES */
.badge{
font-size:12px;
padding:6px 10px;
border-radius:20px;
}

/* BUTTONS */
.btn-primary{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
border:none;
font-weight:600;
}

.btn-primary:hover{
opacity:0.9;
}

.btn-secondary{
background:#2a2a2a;
border:none;
}

.btn-secondary:hover{
background:#3a3a3a;
}

/* ALERT */
.alert{
border:none;
}

</style>

<div class="card main-card p-4 mt-4">

<h3 class="mb-4">Available Polls</h3>

<?php
if(isset($_GET['voted'])){
    echo "<div class='alert alert-success'>Vote submitted successfully!</div>";
}

if(mysqli_num_rows($poll_result) == 0){
    echo "<div class='alert alert-info'>No polls available right now.</div>";
    echo "<a href='student_dashboard.php' class='btn btn-secondary w-100'>Back to Dashboard</a>";
    echo "</div>";
    include("footer.php");
    exit();
}

while($poll = mysqli_fetch_assoc($poll_result)){

$poll_id = $poll['id'];
?>

<div class="poll-card mb-4">

<h4><?php echo htmlspecialchars($poll['question']); ?></h4>

<div class="mb-3">

<span class="badge bg-primary">
Created by: <?php echo htmlspecialchars($poll['created_by']); ?>
</span>

<span class="badge bg-secondary">
<?php echo date("d M Y", strtotime($poll['created_at'])); ?>
</span>

<span class="badge bg-danger">
Expires: <?php echo date("d M Y", strtotime($poll['expiry_date'])); ?>
</span>

</div>

<form method="POST">

<div class="form-check mb-2">
<input class="form-check-input" type="radio"
name="option_<?php echo $poll_id ?>" value="1" required>
<label class="form-check-label">
<?php echo htmlspecialchars($poll['option1']); ?>
</label>
</div>

<div class="form-check mb-2">
<input class="form-check-input" type="radio"
name="option_<?php echo $poll_id ?>" value="2">
<label class="form-check-label">
<?php echo htmlspecialchars($poll['option2']); ?>
</label>
</div>

<?php if(!empty($poll['option3'])){ ?>
<div class="form-check mb-2">
<input class="form-check-input" type="radio"
name="option_<?php echo $poll_id ?>" value="3">
<label class="form-check-label">
<?php echo htmlspecialchars($poll['option3']); ?>
</label>
</div>
<?php } ?>

<?php if(!empty($poll['option4'])){ ?>
<div class="form-check mb-2">
<input class="form-check-input" type="radio"
name="option_<?php echo $poll_id ?>" value="4">
<label class="form-check-label">
<?php echo htmlspecialchars($poll['option4']); ?>
</label>
</div>
<?php } ?>

<?php if(!empty($poll['option5'])){ ?>
<div class="form-check mb-2">
<input class="form-check-input" type="radio"
name="option_<?php echo $poll_id ?>" value="5">
<label class="form-check-label">
<?php echo htmlspecialchars($poll['option5']); ?>
</label>
</div>
<?php } ?>

<br>

<button type="submit"
name="vote_<?php echo $poll_id ?>"
class="btn btn-primary w-100">
Submit Vote
</button>

</form>

</div>

<?php } ?>

<a href="student_dashboard.php" class="btn btn-secondary w-100">
Back to Dashboard
</a>

</div>

<?php include("footer.php"); ?>