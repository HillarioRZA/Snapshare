<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $follower_user_id = $_SESSION['user_id'];
    $user_id = $_POST['user_id'];

    // Cek apakah pengguna sudah memfollow user ini sebelumnya
    $sql_check_follow = "SELECT follower_id FROM Followers WHERE user_id = ? AND follower_user_id = ?";
    $stmt_check_follow = $conn->prepare($sql_check_follow);
    $stmt_check_follow->bind_param("ii", $user_id, $follower_user_id);
    $stmt_check_follow->execute();
    $result_check_follow = $stmt_check_follow->get_result();

    if ($result_check_follow->num_rows == 0) {
        // Insert into Followers table
        $sql_insert_follow = "INSERT INTO Followers (user_id, follower_user_id) VALUES (?, ?)";
        $stmt_insert_follow = $conn->prepare($sql_insert_follow);
        $stmt_insert_follow->bind_param("ii", $user_id, $follower_user_id);
        $stmt_insert_follow->execute();

        // Check if insertion was successful
        if ($stmt_insert_follow->affected_rows > 0) {
            http_response_code(200);
            exit;
        } else {
            http_response_code(500);
            exit;
        }
    } else {
        // Jika pengguna sudah memfollow sebelumnya, kirim kode status 409 Conflict
        http_response_code(409);
        exit;
    }
} else {
    http_response_code(400);
    exit;
}
?>
