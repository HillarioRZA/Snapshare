<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = $_POST['comment'];

// Simpan komentar ke database
$sql_add_comment = "INSERT INTO Comments (post_id, user_id, comment) VALUES (?, ?, ?)";
$stmt_add_comment = $conn->prepare($sql_add_comment);
$stmt_add_comment->bind_param("iis", $post_id, $user_id, $comment);
$stmt_add_comment->execute();

header("Location: post_detail.php?post_id=$post_id");
exit;
?>
