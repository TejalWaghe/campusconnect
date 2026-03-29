<?php
session_start();
include("config.php");
include("send_mail.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";
$poll_id = "";

if(isset($_POST['create_poll'])) {

    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $option1 = mysqli_real_escape_string($conn, $_POST['option1']);
    $option2 = mysqli_real_escape_string($conn, $_POST['option2']);
    $option3 = mysqli_real_escape_string($conn, $_POST['option3']);

    $option4 = !empty($_POST['option4']) ? mysqli_real_escape_string($conn,$_POST['option4']) : NULL;
    $option5 = !empty($_POST['option5']) ? mysqli_real_escape_string($conn,$_POST['option5']) : NULL;

    $expiry_date = $_POST['expiry_date'];
    $today = date('Y-m-d');

    if($expiry_date < $today){
        $error = "Expiry date cannot be in the past.";
    } else {

        $admin_id = $_SESSION['user_id'];
        $admin_name = $_SESSION['name'];

        $sql = "INSERT INTO polls 
                (question, option1, option2, option3, option4, option5, expiry_date, created_by)
                VALUES 
                ('$question', '$option1', '$option2', '$option3', "
                .($option4 ? "'$option4'" : "NULL").", "
                .($option5 ? "'$option5'" : "NULL").", 
                '$expiry_date', '$admin_name')";

        if(mysqli_query($conn, $sql)) {

            $last_id = mysqli_insert_id($conn);
            $poll_id = "POLL-" . str_pad($last_id,3,"0",STR_PAD_LEFT);

            mysqli_query($conn,"UPDATE polls SET poll_id='$poll_id' WHERE id='$last_id'");

            mysqli_query($conn,"
                INSERT INTO admin_logs (admin_id, admin_name, action)
                VALUES ('$admin_id','$admin_name','Created poll: $question')
            ");

            /* GET ALL VERIFIED STUDENTS */

$result = $conn->query("SELECT email,fullname FROM users WHERE role='student' AND is_verified=1");

while($row = $result->fetch_assoc()){

$email = $row['email'];
$name = $row['fullname'];

sendPollNotification(
$email,
$name,
$question,
$expiry_date
);

}

$success = "Poll created successfully! Poll ID: $poll_id";

        } else {
            die("Error: " . mysqli_error($conn));
        }
    }
}

include("header.php");
?>

<style>

/* DARK THEME (same as SpeakUp) */
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
color:#ccc;
}

/* INPUTS */
.form-control{
background:#1a1a1a !important;
border:1px solid #444 !important;
color:white !important;
}

.form-control::placeholder{
color:#888;
}

.form-control:focus{
border-color:#B14EFF;
box-shadow:none;
}

/* BUTTON */
.btn-primary{
background:linear-gradient(90deg,#8A00C4,#B14EFF);
border:none;
font-weight:600;
}

.btn-secondary{
background:#2a2a2a;
border:none;
}

/* TEXT */
small{
color:#aaa;
}

h2{
color:white;
font-weight:600;
}

</style>

<div class="card p-4 mt-4">

<h2 class="mb-4">Create Poll</h2>

<?php if($success != "") { ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<?php if($error != "") { ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="POST">

<!-- QUESTION -->

<div class="mb-3">
<label class="form-label">Poll Question</label>
<textarea name="question" class="form-control" id="questionBox" maxlength="150" required placeholder="Enter poll question..."></textarea>
<small id="questionCount">0 / 150 characters</small>
</div>

<!-- OPTIONS -->

<div class="mb-3">
<label>Option 1</label>
<input type="text" name="option1" class="form-control" placeholder="Enter option 1" required>
</div>

<div class="mb-3">
<label>Option 2</label>
<input type="text" name="option2" class="form-control" placeholder="Enter option 2" required>
</div>

<div class="mb-3">
<label>Option 3</label>
<input type="text" name="option3" class="form-control" placeholder="Enter option 3" required>
</div>

<div class="mb-3">
<label>Option 4 (Optional)</label>
<input type="text" name="option4" class="form-control" placeholder="Optional">
</div>

<div class="mb-3">
<label>Option 5 (Optional)</label>
<input type="text" name="option5" class="form-control" placeholder="Optional">
</div>

<!-- DATE -->

<div class="mb-3">
<label>Poll Expiry Date</label>
<input type="date"
name="expiry_date"
class="form-control"
min="<?php echo date('Y-m-d'); ?>"
value="<?php echo date('Y-m-d'); ?>"
required>
</div>

<!-- BUTTON -->

<button type="submit" name="create_poll" class="btn btn-primary w-100 mt-2">
Create Poll
</button>

</form>

<hr>

<a href="admin_dashboard.php" class="btn btn-secondary w-100">
Back to Dashboard
</a>

</div>

<script>

/* CHARACTER COUNTER */

const box = document.getElementById("questionBox");
const count = document.getElementById("questionCount");

box.addEventListener("input", () => {
count.textContent = box.value.length + " / 150 characters";
});

</script>

<?php include("footer.php"); ?>