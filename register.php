<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi.";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } else {
        // Melakukan query untuk memeriksa apakah username atau email sudah ada
        $sql = "SELECT * FROM Users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username atau email sudah terdaftar.";
        } else {
            // Menyimpan data pengguna baru tanpa enkripsi password
            $sql = "INSERT INTO Users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $password);
            
            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SnapShare</title>
    <link rel="stylesheet" href="css/styles1.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="login-container">
      <h2>Register</h2>
      <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; header("Location: login.php");?></div>
      <?php endif; ?>
      <form action="register.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required />
        </div>
        <div class="form-group">
          <label for="password">Email</label>
          <input type="email" id="email" name="email" required />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required />
        </div>
        <div class="form-group">
          <label for="confirm_password">Konfirmasi Password</label>
          <input
            type="password"
            class="form-control"
            id="confirm_password"
            name="confirm_password"
            required
          />
        </div>
        <button type="submit">Register</button>
      </form>
    </div>
</body>
</html>
