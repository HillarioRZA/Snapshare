<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Query untuk mengambil informasi profil pengguna
$sql_user = "SELECT 
                username, 
                profile_picture, 
                (SELECT COUNT(*) FROM Posts WHERE user_id = ?) AS posts_count,
                (SELECT COUNT(*) FROM Followers WHERE user_id = ?) AS followers_count,
                (SELECT COUNT(*) FROM Followers WHERE follower_user_id = ?) AS following_count
             FROM Users 
             WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

$username = $user['username'];
$profile_picture = $user['profile_picture'];
$posts_count = $user['posts_count'];
$followers_count = $user['followers_count'];
$following_count = $user['following_count'];

// Query untuk mengambil postingan pengguna
$sql_posts = "SELECT post_id, image_url, caption FROM Posts WHERE user_id = ?";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("i", $user_id);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();

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
$is_followed = false;
if (isset($_SESSION['user_id'])) {
    $sql_is_followed = "SELECT COUNT(*) AS is_followed FROM Followers WHERE user_id = ? AND follower_user_id = ?";
    $stmt_is_followed = $conn->prepare($sql_is_followed);
    $stmt_is_followed->bind_param("ii", $user_id, $_SESSION['user_id']);
    $stmt_is_followed->execute();
    $result_is_followed = $stmt_is_followed->get_result();
    $is_followed = (bool) $result_is_followed->fetch_assoc()['is_followed'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile_detail.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
      <div class="profile-header">
        <img
          src="<?php echo $profile_picture; ?>"
          class="card-img-top"
          alt="Profile Picture"
        />
        <div class="profile-info">
          <h1><?php echo $username; ?></h1>
        </div>
      </div>
      <div class="profile-stats">
        <div>
                <h2><?php echo $posts_count; ?></h2>
                <p>Posts</p>
            </div>
            <div>
                <h2><?php echo $following_count; ?></h2>
                <p>Following</p>
            </div>
            <div>
                <h2><?php echo $followers_count; ?></h2>
                <p>Followers</p>
            </div>
      </div>
      <div class="profile-actions">
        <a href="upload.php"><button>Upload</button></a>
        <button onclick="window.location.href = 'edit_profile.php';">Edit Profile</button>
      </div>
      <hr>
      <div class="profile-posts">
        <?php if ($result_posts->num_rows > 0): ?>
        <?php while ($post = $result_posts->fetch_assoc()): ?>
        <a href="post_detail.php?post_id=<?php echo $post['post_id']; ?>"
          ><img
            src="images/<?php echo $post['image_url']; ?>"
        /></a>
        <?php endwhile; ?>
        <?php else: ?>
        <p>Tidak memiliki post.</p>
        <?php endif; ?>
      </div>
    </div>
<?php include 'includes/footer.php'; ?>
<script>
    document.getElementById('followBtn').addEventListener('click', function() {
        fetch('follow.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=<?php echo $user_id; ?>`,
        })
        .then(response => {
            if (response.ok) {
                document.getElementById('followBtn').classList.remove('btn-primary');
                document.getElementById('followBtn').classList.add('btn-secondary');
                document.getElementById('followBtn').innerText = 'Followed';
                document.getElementById('followBtn').disabled = true;
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
</script>
</body>
</html>
