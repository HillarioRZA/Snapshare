<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'];

// Cek apakah pengguna sudah like postingan ini
$sql_check_like = "SELECT * FROM Likes WHERE post_id = ? AND user_id = ?";
$stmt_check_like = $conn->prepare($sql_check_like);
$stmt_check_like->bind_param("ii", $post_id, $user_id);
$stmt_check_like->execute();
$result_check_like = $stmt_check_like->get_result();

if ($result_check_like->num_rows == 0) {
    // Jika belum like, tambahkan ke database
    $sql_add_like = "INSERT INTO Likes (post_id, user_id) VALUES (?, ?)";
    $stmt_add_like = $conn->prepare($sql_add_like);
    $stmt_add_like->bind_param("ii", $post_id, $user_id);
    $stmt_add_like->execute();
}

header("Location: post_detail.php?post_id=$post_id");
exit;
?>
