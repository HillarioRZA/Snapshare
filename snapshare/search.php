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

// Query untuk mencari pengguna berdasarkan username
if (isset($_GET['search'])) {
    $search_query = '%' . $_GET['search'] . '%';
    $sql_search_users = "SELECT u.user_id, u.username, 
                                CASE WHEN f.follower_id IS NULL THEN 0 ELSE 1 END AS is_followed
                         FROM Users u
                         LEFT JOIN Followers f ON u.user_id = f.user_id AND f.follower_user_id = ?
                         WHERE u.username LIKE ?";
    $stmt_search_users = $conn->prepare($sql_search_users);
    $stmt_search_users->bind_param("is", $user_id, $search_query);
    $stmt_search_users->execute();
    $result_search_users = $stmt_search_users->get_result();
} else {
    $result_search_users = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/search.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h2>Search Users</h2>
            <form action="search.php" method="GET">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search users by username" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <?php if ($result_search_users && $result_search_users->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($user = $result_search_users->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="profile_user.php?user_id=<?php echo $user['user_id']; ?>"><?php echo $user['username']; ?></a>
                            <?php if ($user['is_followed'] == 1): ?>
                                <button type="button" class="btn btn-secondary unfollow-btn" data-user-id="<?php echo $user['user_id']; ?>">Followed</button>
                                <button type="button" class="btn btn-danger ml-2 d-none confirm-unfollow-btn" data-user-id="<?php echo $user['user_id']; ?>">Unfollow</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary follow-btn" data-user-id="<?php echo $user['user_id']; ?>">Follow</button>
                                <button type="button" class="btn btn-danger ml-2 d-none confirm-unfollow-btn" data-user-id="<?php echo $user['user_id']; ?>">Unfollow</button>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
document.querySelectorAll('.follow-btn').forEach(item => {
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
                this.nextElementSibling.classList.remove('d-none'); // Menampilkan tombol Unfollow
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

document.querySelectorAll('.unfollow-btn').forEach(item => {
    item.addEventListener('click', function() {
        var userId = this.getAttribute('data-user-id');
        this.classList.add('d-none'); // Menyembunyikan tombol Unfollow sementara
        this.nextElementSibling.classList.remove('d-none'); // Menampilkan tombol konfirmasi Unfollow

        // Fungsi konfirmasi unfollow
        var confirmUnfollowBtn = this.nextElementSibling;
        confirmUnfollowBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to unfollow this user?')) {
                fetch(`unfollow.php`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`,
                })
                .then(response => {
                    if (response.ok) {
                        confirmUnfollowBtn.classList.add('d-none'); // Menyembunyikan tombol konfirmasi Unfollow
                        item.classList.remove('btn-secondary');
                        item.classList.add('btn-primary');
                        item.innerText = 'Follow';
                    } else {
                        throw new Error('Failed to unfollow user.');
                    }
                })
                .catch(error => {
                    console.error('Error unfollowing user:', error);
                });
            } else {
                confirmUnfollowBtn.classList.add('d-none'); // Menyembunyikan tombol konfirmasi Unfollow jika dibatalkan
                item.classList.remove('d-none'); // Menampilkan kembali tombol Unfollow
            }
        });
    });
});
</script>
</body>
</html>
