<?php
session_start();
require_once 'db.php';

// Query untuk mengambil postingan populer berdasarkan jumlah like terbanyak dan jumlah komentar
$sql_popular_posts = "
    SELECT p.post_id, p.image_url, p.caption, u.username, u.profile_picture, p.created_at, COUNT(l.like_id) AS like_count,
    (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN likes l ON p.post_id = l.post_id
    GROUP BY p.post_id
    ORDER BY like_count DESC
    LIMIT 10
";
$result_popular_posts = $conn->query($sql_popular_posts);

// Fungsi untuk menghitung selisih waktu
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'tahun',
        'm' => 'bulan',
        'w' => 'minggu',
        'd' => 'hari',
        'h' => 'jam',
        'i' => 'menit',
        's' => 'detik',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' yang lalu' : 'baru saja';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SnapShare - Home</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="post-container">
    <h2>Popular Posts</h2>
    <?php if ($result_popular_posts->num_rows > 0): ?>
        <?php while ($post = $result_popular_posts->fetch_assoc()): ?>
            <a href="post_detail.php?post_id=<?php echo $post['post_id']; ?>" class="post-link">
                <div class="post-item">
                    <div class="post-header">
                        <div class="profile-pic">
                            <img src="<?php echo htmlspecialchars($post['profile_picture']); ?>" alt="Profile Picture">
                        </div>
                        <div class="username-location">
                            <span class="username"><?php echo htmlspecialchars($post['username']); ?></span>
                            <!-- You can add location data here if available -->
                        </div>
                    </div>
                    <div class="post-image">
                        <img src="images/<?php echo htmlspecialchars($post['image_url']); ?>" alt="Post Image">
                    </div>
                    <br>
                    <div class="post-likes">
                        <?php echo htmlspecialchars($post['like_count']); ?> likes
                    </div>
                    <div class="post-description">
                        <?php echo htmlspecialchars($post['caption']); ?>
                        <!-- You can parse and format hashtags if needed -->
                    </div>
                    <div class="post-comments">
                        <span class="comment-count">View all <?php echo htmlspecialchars($post['comment_count']); ?> comments</span>
                    </div>
                    <div class="post-time">
                        <?php echo time_elapsed_string($post['created_at']); ?>
                    </div>
                </div>
            </a>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No popular posts found.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
