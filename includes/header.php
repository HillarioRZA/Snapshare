<?php
require_once 'db.php';

// Periksa apakah pengguna telah login
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Query untuk mengambil URL gambar profil
    $sql = "SELECT profile_picture FROM Users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $profile_picture = $user['profile_picture'];
} else {
    // Default gambar profil jika pengguna belum login
    $profile_picture = "https://i.imgur.com/hczKIze.jpg";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SnapShare</title>
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
</head>
<body id="body-pd">
    <header class="header" id="header">
        <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i> </div>
        <div class="header_img"> <img src="<?php echo $profile_picture; ?>" alt="Profile Picture"> </div>
    </header>
    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div> 
                <a href="index.php" class="nav_logo"> 
                    <i class='bx bx-layer nav_logo-icon'></i> 
                    <span class="nav_logo-name">SnapShare</span> 
                </a>
                <div class="nav_list"> 
                    <a href="index.php" class="nav_link active"> 
                        <i class='bx bx-grid-alt nav_icon'></i> 
                        <span class="nav_name">Home</span> 
                    </a> 
                    <a href="profile.php" class="nav_link"> 
                        <i class='bx bx-user nav_icon'></i> 
                        <span class="nav_name">Profile</span> 
                    </a> 
                    <a href="search.php" class="nav_link"> 
                        <i class='bx bx-message-square-detail nav_icon'></i> 
                        <span class="nav_name">Search</span> 
                    </a> 
                    <a href="notification.php" class="nav_link"> 
                        <i class='bx bx-message-square-detail nav_icon'></i> 
                        <span class="nav_name">Notification</span> 
                    </a> 
                </div>
            </div> 
            <a href="logout.php" class="nav_link"> 
                <i class='bx bx-log-out nav_icon'></i> 
                <span class="nav_name">Logout</span> 
            </a>
        </nav>
    </div>
    <!--Container Main end-->
 
