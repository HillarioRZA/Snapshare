<?php
session_start();
require_once 'db.php';

// Periksa apakah pengguna telah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil post_id dari parameter URL
if (!isset($_GET['post_id'])) {
    header("Location: profile.php"); // Ganti dengan halaman yang sesuai jika post_id tidak ada
    exit;
}
$post_id = $_GET['post_id'];

// Query untuk mengambil informasi postingan
$sql_post = "SELECT * FROM Posts WHERE post_id = ?";
$stmt_post = $conn->prepare($sql_post);
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$result_post = $stmt_post->get_result();
if ($result_post->num_rows == 0) {
    header("Location: profile.php"); // Ganti dengan halaman yang sesuai jika postingan tidak ditemukan
    exit;
}
$post = $result_post->fetch_assoc();

// Pastikan pengguna yang sedang login adalah pemilik postingan
if ($user_id != $post['user_id']) {
    header("Location: post_detail.php?post_id=$post_id"); // Redirect ke halaman detail postingan jika bukan pemiliknya
    exit;
}

// Proses update caption
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_caption = $_POST['new_caption'];

    // Lakukan update caption di database
    $sql_update = "UPDATE Posts SET caption = ? WHERE post_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("si", $new_caption, $post_id);
    if ($stmt_update->execute()) {
        header("Location: post_detail.php?post_id=$post_id"); // Redirect kembali ke halaman detail postingan setelah berhasil update
        exit;
    } else {
        echo "Error updating post.";
    }
}

// Proses update location_url
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_location_url = $_POST['new_location_url'];

    // Lakukan update location_url di database
    $sql_update_location = "UPDATE Posts SET location_url = ? WHERE post_id = ?";
    $stmt_update_location = $conn->prepare($sql_update_location);
    $stmt_update_location->bind_param("si", $new_location_url, $post_id);
    if ($stmt_update_location->execute()) {
        header("Location: post_detail.php?post_id=$post_id"); // Redirect kembali ke halaman detail postingan setelah berhasil update
        exit;
    } else {
        echo "Error updating location URL.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Post - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/update_post.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <img src="images/<?php echo $post['image_url']; ?>" class="card-img-top" alt="...">
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-group">
                            <label for="new_caption">Update Caption:</label>
                            <input type="text" class="form-control" id="new_caption" name="new_caption" value="<?php echo htmlspecialchars($post['caption']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="new_location_url">Update Location URL:</label>
                            <input type="text" class="form-control" id="new_location_url" name="new_location_url" value="<?php echo htmlspecialchars($post['location_url']); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
