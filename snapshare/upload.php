<?php
session_start();
require_once 'db.php';

// Periksa apakah pengguna telah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses form upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file_name = $_FILES['image']['name'];
    $file_tmp = $_FILES['image']['tmp_name'];
    
    // Ambil nilai URL lokasi dari form
    $location_url = $_POST['location_url'];

    // Memindahkan file yang diunggah ke direktori yang diinginkan
    $upload_dir = 'images/';
    $target_file = $upload_dir . basename($file_name);

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Simpan informasi postingan ke database
        $sql = "INSERT INTO Posts (user_id, image_url, location_url, caption) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $file_name, $location_url, $description);
        $stmt->execute();
        $stmt->close();

        // Redirect ke halaman profil setelah berhasil mengunggah
        header("Location: profile.php");
        exit;
    } else {
        $upload_error = "Error uploading file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Image - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/upload.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2>Upload Image</h2>
            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="location_url">Location URL:</label>
                    <input type="text" class="form-control" id="location_url" name="location_url">
                </div>
                <div class="form-group">
                    <label for="image">Select Image:</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                </div>
                <!-- Input untuk memasukkan URL lokasi -->
                <!-- Tampilkan pratinjau gambar -->
                <div class="form-group">
                    <h5>Preview:</h5>
                    <img id="previewImage" class="img-thumbnail" src="#" alt="Preview Image" style="display: none;">
                </div>
                <?php if (isset($upload_error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $upload_error; ?>
                    </div>
                <?php endif; ?>
                <button class="btn btn-primary" onclick="window.location.href = 'apply_filters.php';">Add Filter</button>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Fungsi untuk menampilkan pratinjau gambar sebelum diunggah
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var preview = document.getElementById('previewImage');
        preview.src = reader.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Event listener untuk input gambar
document.getElementById('image').addEventListener('change', function(event) {
    previewImage(event);
});
</script>
</body>
</html>
