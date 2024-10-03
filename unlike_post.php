<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'];

// Hapus like dari database
$sql_remove_like = "DELETE FROM Likes WHERE post_id = ? AND user_id = ?";
$stmt_remove_like = $conn->prepare($sql_remove_like);
$stmt_remove_like->bind_param("ii", $post_id, $user_id);
$stmt_remove_like->execute();

header("Location: post_detail.php?post_id=$post_id");
exit;
?>
