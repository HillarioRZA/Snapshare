<?php
session_start();
require_once 'db.php';

if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    header("Location: index.php"); // Redirect ke halaman utama jika parameter tidak valid
    exit;
}

$user_id = $_GET['user_id'];

// Query untuk mengambil informasi profil pengguna
$sql_user = "SELECT 
                user_id,
                username, 
                profile_picture, 
                (SELECT COUNT(*) FROM Posts WHERE user_id = Users.user_id) AS posts_count,
                (SELECT COUNT(*) FROM Followers WHERE user_id = Users.user_id) AS followers_count,
                (SELECT COUNT(*) FROM Followers WHERE follower_user_id = Users.user_id) AS following_count
             FROM Users 
             WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

if (!$user) {
    // Redirect jika user_id tidak ditemukan
    header("Location: index.php");
    exit;
}

$username = $user['username'];
$profile_picture = $user['profile_picture'];
$posts_count = $user['posts_count'];
$followers_count = $user['followers_count'];
$following_count = $user['following_count'];
$visited_user_id = $user['user_id'];

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
            src="<?php echo $user['profile_picture']; ?>"
            class="card-img-top"
            alt="Profile Picture"
        />
        <div class="profile-info">
            <h1><?php echo $username; ?></h1>
        </div>
    </div>
    <div class="profile-stats">
    <div>
        <h2 id="postsCount"><?php echo $posts_count; ?></h2>
        <p>Posts</p>
    </div>
    <div>
        <h2 id="followingCount"><?php echo $following_count; ?></h2>
        <p>Following</p>
    </div>
    <div>
        <h2 id="followersCount"><?php echo $followers_count; ?></h2>
        <p>Followers</p>
    </div>
    </div>
    <div class="profile-actions">
        <?php
        // Query untuk mengecek apakah pengguna sudah memfollow pengguna yang dikunjungi
        $sql_check_follow = "SELECT COUNT(*) AS is_following FROM Followers WHERE user_id = ? AND follower_user_id = ?";
        $stmt_check_follow = $conn->prepare($sql_check_follow);
        $stmt_check_follow->bind_param("ii", $visited_user_id, $_SESSION['user_id']);
        $stmt_check_follow->execute();
        $result_check_follow = $stmt_check_follow->get_result();
        $is_following = $result_check_follow->fetch_assoc()['is_following'];

        if ($is_following) {
            // Jika sudah follow
            echo '<button class="btn btn-danger" id="followBtn">Unfollow</button>';
        } else {
            // Jika belum follow
            echo '<button class="btn btn-primary" id="followBtn">Follow</button>';
        }
        ?>
    </div>
    <hr>
    <div class="profile-posts">
        <?php if ($result_posts->num_rows > 0): ?>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
                <a href="post_detail.php?post_id=<?php echo $post['post_id']; ?>">
                    <img src="images/<?php echo $post['image_url']; ?>"/>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak memiliki post.</p>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
    document.getElementById('followBtn').addEventListener('click', function() {
        var action = '';
        var button = this;
        if (button.classList.contains('btn-primary')) {
            action = 'follow';
        } else if (button.classList.contains('btn-danger')) {
            action = 'unfollow';
        }

        var userId = <?php echo $visited_user_id; ?>;
        fetch(action + '.php', {
            method: action.toUpperCase() === 'UNFOLLOW' ? 'DELETE' : 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`,
        })
        .then(response => {
            if (response.ok) {
                if (action === 'follow') {
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-danger');
                    button.innerText = 'Unfollow';
                    // Update jumlah followers
                    var followersCountElem = document.getElementById('followersCount');
                    followersCountElem.textContent = parseInt(followersCountElem.textContent) + 1;
                } else if (action === 'unfollow') {
                    button.classList.remove('btn-danger');
                    button.classList.add('btn-primary');
                    button.innerText = 'Follow';
                    // Update jumlah followers
                    var followersCountElem = document.getElementById('followersCount');
                    followersCountElem.textContent = parseInt(followersCountElem.textContent) - 1;
                }
            } else {
                throw new Error('Failed to ' + action);
            }
        })
        .catch(error => {
            console.error('Error ' + action + 'ing user:', error);
        });
    });
</script>

</body>
</html>
