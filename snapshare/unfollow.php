<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Unauthorized");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $followed_user_id = $_DELETE['user_id'];

    // Query untuk menghapus data follow dari tabel Followers
    $sql_delete_follow = "DELETE FROM Followers WHERE user_id = ? AND follower_user_id = ?";
    $stmt_delete_follow = $conn->prepare($sql_delete_follow);
    $stmt_delete_follow->bind_param("ii", $followed_user_id, $user_id);

    if ($stmt_delete_follow->execute()) {
        http_response_code(200);
        exit("Unfollowed successfully");
    } else {
        http_response_code(500);
        exit("Failed to unfollow user");
    }
} else {
    http_response_code(405);
    exit("Method Not Allowed");
}
?>
