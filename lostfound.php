<?php
session_start();
include("config.php");
include("send_mail.php");

require 'cloudinary_config.php';

use Cloudinary\Api\Upload\UploadApi;

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

/* Success */
if(isset($_GET['success'])) {
    $success = "Item submitted successfully!";
}

/* FORM SUBMIT */
if(isset($_POST['submit'])) {

    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $user_id = $_SESSION['user_id'];

    $item_date = date("Y-m-d");

    /* IMAGE VALIDATION */

    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_size = $_FILES['image']['size'];

        $allowed_extensions = ['jpg','jpeg','png','webp'];
        $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if(!in_array($file_extension, $allowed_extensions)) {
            $error = "Only JPG, JPEG, PNG, WEBP allowed.";
        }

        $allowed_mime = ['image/jpeg','image/png','image/webp'];
        $file_mime = mime_content_type($image_tmp);

        if(empty($error) && !in_array($file_mime,$allowed_mime)) {
            $error = "Invalid image file.";
        }

        if(empty($error) && $image_size > 2*1024*1024) {
            $error = "Max size 2MB.";
        }

        /* CLOUDINARY UPLOAD */
        if(empty($error)) {

            try {

                $upload = (new UploadApi())->upload($image_tmp, [
                    'folder' => 'campusconnect'
                ]);

                $image_url = $upload['secure_url'];

            } catch (Exception $e){
                $error = "Cloud upload failed.";
            }
        }

    } else {
        $error = "Please upload an image.";
    }

    /* INSERT INTO DATABASE */

    if(empty($error)) {

        $stmt = $conn->prepare("
            INSERT INTO lost_and_found
            (user_id,type,description,item_date,category,image)
            VALUES (?,?,?,?,?,?)
        ");

        $stmt->bind_param("isssss",
            $user_id,
            $type,
            $description,
            $item_date,
            $category,
            $image_url
        );

        if($stmt->execute()){

            /* SEND EMAIL TO ALL USERS */

            $result = $conn->query("SELECT email,fullname FROM users WHERE is_verified = 1");

            while($row = $result->fetch_assoc()){

                sendLostFoundNotification(
                    $row['email'],
                    $row['fullname'],
                    $type,
                    $category,
                    $description
                );
            }

            header("Location: lostfound.php?success=1");
            exit();

        } else {
            $error = "Database error.";
        }

        $stmt->close();
    }
}

include("header.php");
?>

<style>

/* SAME STYLE AS SPEAKUP */

body{
background:#0f0f0f;
color:white;
}

.card{
background:#121212;
border:none;
border-radius:14px;
box-shadow:0 10px 30px rgba(0,0,0,0.7);
}

label{
color:#cccccc;
font-weight:500;
}

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

small{
color:#aaaaaa;
}

h2{
color:white;
}

</style>

<div class="card p-4 mt-4">

<h2 class="mb-4">Lost & Found Portal</h2>

<?php if($success){ ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php } ?>

<?php if($error){ ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php } ?>

<form method="POST" enctype="multipart/form-data">

<!-- TYPE -->
<div class="mb-3">
<label class="form-label">Item Type</label><br>

<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="type" value="Lost" checked>
<label class="form-check-label text-danger">Lost 🔴</label>
</div>

<div class="form-check form-check-inline">
<input class="form-check-input" type="radio" name="type" value="Found">
<label class="form-check-label text-success">Found 🟢</label>
</div>
</div>

<!-- IMAGE -->
<div class="mb-3">
<label class="form-label">Upload Item Image</label>
<input type="file" name="image" class="form-control" id="imageUpload" required>
<img id="preview" width="120" class="mt-2" style="display:none;">
</div>

<!-- DESCRIPTION -->
<div class="mb-3">
<label class="form-label">Item Description</label>
<textarea name="description" class="form-control" id="descBox" maxlength="300" required></textarea>
<small id="descCount">0 / 300 characters</small>
</div>

<!-- CATEGORY -->
<div class="mb-3">
<label class="form-label">Category</label>
<select name="category" class="form-select" required>
<option value="">Select Category</option>
<option value="Electronics">Electronics</option>
<option value="Accessories">Accessories</option>
<option value="Documents">Documents</option>
<option value="Clothing">Clothing</option>
<option value="Books & Stationery">Books & Stationery</option>
<option value="Others">Others</option>
</select>
</div>

<button type="submit" name="submit" class="btn btn-primary w-100 mt-2">
Submit Item
</button>

</form>

<hr>

<a href="student_dashboard.php" class="btn btn-secondary w-100">
Back to Dashboard
</a>

</div>

<script>

/* IMAGE PREVIEW */
document.getElementById("imageUpload").addEventListener("change",function(e){
const preview = document.getElementById("preview");
preview.src = URL.createObjectURL(e.target.files[0]);
preview.style.display = "block";
});

/* COUNTER */
const box = document.getElementById("descBox");
const counter = document.getElementById("descCount");

box.addEventListener("input",function(){
counter.textContent = box.value.length + " / 300 characters";
});

</script>

<?php include("footer.php"); ?>
