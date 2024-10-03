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
$sql_post = "SELECT p.post_id, p.image_url, p.caption, p.location_url, u.username, u.user_id AS owner_id
             FROM Posts p
             JOIN Users u ON p.user_id = u.user_id
             WHERE p.post_id = ?";
$stmt_post = $conn->prepare($sql_post);
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$result_post = $stmt_post->get_result();
if ($result_post->num_rows == 0) {
    header("Location: profile.php"); // Ganti dengan halaman yang sesuai jika postingan tidak ditemukan
    exit;
}
$post = $result_post->fetch_assoc();

// Query untuk mengecek apakah pengguna sudah like postingan ini
$sql_check_like = "SELECT * FROM Likes WHERE post_id = ? AND user_id = ?";
$stmt_check_like = $conn->prepare($sql_check_like);
$stmt_check_like->bind_param("ii", $post_id, $user_id);
$stmt_check_like->execute();
$result_check_like = $stmt_check_like->get_result();
$is_liked = ($result_check_like->num_rows > 0);

// Ambil jumlah total likes
$sql_count_likes = "SELECT COUNT(*) AS total_likes FROM Likes WHERE post_id = ?";
$stmt_count_likes = $conn->prepare($sql_count_likes);
$stmt_count_likes->bind_param("i", $post_id);
$stmt_count_likes->execute();
$result_count_likes = $stmt_count_likes->get_result();
$total_likes = $result_count_likes->fetch_assoc()['total_likes'];

// Cek apakah pengguna adalah pemilik postingan
$is_owner = ($user_id == $post['owner_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Detail - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/post_detail.css">
    <script>
        function deletePost() {
            if (confirm("Are you sure you want to delete this post?")) {
                // User clicked "OK", proceed with deletion via AJAX
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // Berhasil menghapus, refresh halaman atau lakukan tindakan lain yang diperlukan
                            alert("Post deleted successfully.");
                            window.location.reload(); // Refresh halaman untuk memperbarui konten
                        } else {
                            alert("Failed to delete post.");
                        }
                    }
                };
                xhr.open("POST", "delete_post.php");
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.send("post_id=<?php echo $post_id; ?>");
            }
        }
    </script>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <img src="images/<?php echo $post['image_url']; ?>" alt="...">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $post['username']; ?></h5>
                    <p class="card-text"><?php echo $post['caption']; ?></p>
                    <div class="mt-3">
                        <a href="<?php echo $post['location_url']; ?>" class="btn btn-info btn-sm" target="_blank">View Location</a>
                    </div>
                    <div class="text-center">
                        <?php if ($is_liked): ?>
                            <a href="unlike_post.php?post_id=<?php echo $post_id; ?>" class="btn btn-sm btn-danger">Unlike</a>
                        <?php else: ?>
                            <a href="like_post.php?post_id=<?php echo $post_id; ?>" class="btn btn-sm btn-outline-danger">Like</a>
                        <?php endif; ?>
                        <span><?php echo $total_likes; ?> Likes</span>
                    </div>
                    <?php if ($is_owner): ?>
                        <!-- Tombol untuk update dan delete -->
                        <div class="mt-3">
                            <a href="update_post.php?post_id=<?php echo $post_id; ?>" class="btn btn-warning btn-sm">Update</a>
                            <button onclick="deletePost()" class="btn btn-danger btn-sm">Delete</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-4">
                <h4>Comments</h4>
                <!-- Form untuk menambahkan komentar -->
                <form action="add_comment.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <textarea name="comment" class="form-control mb-2" placeholder="Add a comment..." required></textarea>
                    <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                </form>
                <!-- Daftar komentar -->
                <?php
                $sql_comments = "SELECT c.comment_id, c.comment, u.username
                                 FROM Comments c
                                 JOIN Users u ON c.user_id = u.user_id
                                 WHERE c.post_id = ?
                                 ORDER BY c.created_at DESC";
                $stmt_comments = $conn->prepare($sql_comments);
                $stmt_comments->bind_param("i", $post_id);
                $stmt_comments->execute();
                $result_comments = $stmt_comments->get_result();

                if ($result_comments->num_rows > 0) {
                    while ($comment = $result_comments->fetch_assoc()) {
                        echo '<div class="card mt-2">';
                        echo '<div class="card-body">';
                        echo '<h6 class="card-title">'.$comment['username'].'</h6>';
                        echo '<p class="card-text">'.$comment['comment'].'</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No comments yet.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
