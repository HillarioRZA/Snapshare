<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $capture_from_camera = isset($_POST['capture_from_camera']) && $_POST['capture_from_camera'] === 'true';

    if ($capture_from_camera) {
        // Handling image data from camera capture
        $image_data = $_POST['image_data'];

        // Convert base64 image data to JPEG file
        $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
        $image_data = str_replace(' ', '+', $image_data);
        $image_data = base64_decode($image_data);

        // Save image data as photo.jpg
        $target_file = 'profile_images/photo.jpg';

        // Remove existing photo.jpg if exists
        if (file_exists($target_file)) {
            unlink($target_file);
        }

        // Save the new photo.jpg
        file_put_contents($target_file, $image_data);

        // Update profile information in database
        $sql_update = "UPDATE Users SET username = ?, profile_picture = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssi", $username, $target_file, $user_id);
        $stmt_update->execute();
        $stmt_update->close();

        $_SESSION['message'] = "Profile updated successfully.";
        header("Content-type: application/json");
        echo json_encode(array('success' => true));
        exit;
    } else {
        // Handling file upload scenario
        $profile_picture = $_FILES['profile_picture'];

        if ($profile_picture['error'] === UPLOAD_ERR_OK) {
            $file_name = $profile_picture['name'];
            $file_tmp = $profile_picture['tmp_name'];

            // Validate file type (allowing only images)
            $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            $file_type = mime_content_type($file_tmp); // Get mime type of uploaded file

            if (!in_array($file_type, $allowed_types)) {
                $_SESSION['error'] = "Only JPG, JPEG, PNG & GIF files are allowed.";
                header("Location: edit_profile.php");
                exit;
            }

            // Save uploaded file with original name to profile_images directory
            $target_file = 'profile_images/' . basename($file_name);

            // Move uploaded file to desired directory
            if (move_uploaded_file($file_tmp, $target_file)) {
                // Update profile picture in database
                $sql_update = "UPDATE Users SET username = ?, profile_picture = ? WHERE user_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssi", $username, $target_file, $user_id);
                $stmt_update->execute();
                $stmt_update->close();

                $_SESSION['message'] = "Profile updated successfully.";
                header("Content-type: application/json");
                header("Location: profile.php");
                exit;
            } else {
                $_SESSION['error'] = "Error uploading file.";
                header("Content-type: application/json");
                echo json_encode(array('success' => false));
                exit;
            }
        }
    }
}
?>
