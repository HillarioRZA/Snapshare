<?php
session_start();
require_once 'db.php';

// Periksa apakah pengguna telah login
if (!isset($_SESSION['user_id'])) {
    header("Content-Type: application/json");
    echo json_encode(array("success" => false, "message" => "User not logged in."));
    exit;
}

$user_id = $_SESSION['user_id'];

// Pastikan hanya request POST yang diterima
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Content-Type: application/json");
    echo json_encode(array("success" => false, "message" => "Method not allowed."));
    exit;
}

// Ambil post_id dari data POST
$post_id = $_POST['post_id'] ?? null;

// Validasi post_id
if (!$post_id) {
    header("Content-Type: application/json");
    echo json_encode(array("success" => false, "message" => "Post ID is required."));
    exit;
}

// Query untuk mengambil informasi postingan berdasarkan post_id
$sql_post = "SELECT * FROM Posts WHERE post_id = ?";
$stmt_post = $conn->prepare($sql_post);
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$result_post = $stmt_post->get_result();

if ($result_post->num_rows == 0) {
    header("Content-Type: application/json");
    echo json_encode(array("success" => false, "message" => "Post not found."));
    exit;
}

$post = $result_post->fetch_assoc();

// Pastikan pengguna yang sedang login adalah pemilik postingan yang ingin dihapus
if ($user_id != $post['user_id']) {
    header("Content-Type: application/json");
    echo json_encode(array("success" => false, "message" => "You are not authorized to delete this post."));
    exit;
}

// Proses penghapusan postingan beserta komentar dan likes
$delete_success = false;
$upload_dir = 'images/';
$image_path = $upload_dir . $post['image_url'];

// Hapus komentar terkait postingan dari database
$sql_delete_comments = "DELETE FROM Comments WHERE post_id = ?";
$stmt_delete_comments = $conn->prepare($sql_delete_comments);
$stmt_delete_comments->bind_param("i", $post_id);

if ($stmt_delete_comments->execute()) {
    // Hapus likes terkait postingan dari database
    $sql_delete_likes = "DELETE FROM Likes WHERE post_id = ?";
    $stmt_delete_likes = $conn->prepare($sql_delete_likes);
    $stmt_delete_likes->bind_param("i", $post_id);

    if ($stmt_delete_likes->execute()) {
        // Hapus postingan dari database
        $sql_delete_post = "DELETE FROM Posts WHERE post_id = ?";
        $stmt_delete_post = $conn->prepare($sql_delete_post);
        $stmt_delete_post->bind_param("i", $post_id);

        if ($stmt_delete_post->execute()) {
            $delete_success = true;
        } else {
            header("Content-Type: application/json");
            echo json_encode(array("success" => false, "message" => "Failed to delete post from database."));
            exit;
        }
    } else {
        header("Content-Type: application/json");
        echo json_encode(array("success" => false, "message" => "Failed to delete likes from database."));
        exit;
    }
} else {
    header("Content-Type: application/json");
    echo json_encode(array("success" => false, "message" => "Failed to delete comments from database."));
    exit;
}

// Hapus file gambar terkait dari server jika penghapusan dari database sukses
if ($delete_success && file_exists($image_path)) {
    if (!unlink($image_path)) {
        header("Content-Type: application/json");
        echo json_encode(array("success" => false, "message" => "Failed to delete image file from server."));
        exit;
    }
}

// Kirim respons JSON jika berhasil menghapus
header("Content-Type: application/json");
echo json_encode(array("success" => true));
?>
