<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Query untuk mengambil informasi profil pengguna
$sql_user = "SELECT username, profile_picture FROM Users WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

$username = $user['username'];
$profile_picture = $user['profile_picture'];

// Query untuk mengambil daftar new followers yang belum di-follow balik
$sql_new_followers = "SELECT f.follower_user_id AS user_id, u.username 
                      FROM Followers f
                      INNER JOIN Users u ON f.follower_user_id = u.user_id
                      WHERE f.user_id = ? AND f.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) 
                      AND f.follower_user_id NOT IN (SELECT user_id FROM Followers WHERE user_id = f.follower_user_id AND follower_user_id = ?)";
$stmt_new_followers = $conn->prepare($sql_new_followers);
$stmt_new_followers->bind_param("ii", $user_id, $user_id);
$stmt_new_followers->execute();
$result_new_followers = $stmt_new_followers->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/notification.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <div class="row">
        <div class="col-md-4">
            
                <h5>New Followers</h5>
                <ul class="list-group">
                    <?php while ($new_follower = $result_new_followers->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo $new_follower['username']; ?>
                            <button class="btn btn-primary followback-btn" data-user-id="<?php echo $new_follower['user_id']; ?>">Followback</button>
                        </li>
                    <?php endwhile; ?>
                </ul>
            
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
document.querySelectorAll('.followback-btn').forEach(item => {
    item.addEventListener('click', function() {
        var userId = this.getAttribute('data-user-id');
        fetch(`follow.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`,
        })
        .then(response => {
            if (response.ok) {
                this.classList.remove('btn-primary');
                this.classList.add('btn-secondary');
                this.innerText = 'Followed';
                this.parentNode.remove(); // Menghapus elemen li dari daftar new followers
            } else if (response.status === 409) {
                console.log('User already followed.');
                // Mungkin berikan pesan bahwa pengguna sudah di-follow
            } else {
                throw new Error('Failed to follow user.');
            }
        })
        .catch(error => {
            console.error('Error following user:', error);
        });
    });
});
</script>
</body>
</html>
