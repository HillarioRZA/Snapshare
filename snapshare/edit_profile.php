<?php
session_start();
require_once 'db.php';

// Periksa apakah pengguna telah login
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="container">
    <div class="row">
        <div class="col-md-4 offset-md-3">
            <h2>Edit Profile</h2>
            <form action="process_edit_profile.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="form-group">
                    <label for="profile_picture">Profile Picture</label><br>
                    <img src="<?php echo $profile_picture; ?>" id="preview" class="profile-preview img-thumbnail" alt="Current Profile Picture"><br><br>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="profile_picture_file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                        <label class="custom-file-label" for="profile_picture_file">Choose file</label>
                    </div>
                    <small id="profile_picture_help" class="form-text text-muted">Upload or take a photo for your profile picture.</small>
                    <br>
                    <div class="camera-container">
                        <video id="camera-preview" style="width: 100%; max-width: 600px; display: none;"></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                    </div>
                    <div id="camera-controls" class="mt-2">
                        <button type="button" class="btn btn-secondary" id="btnOpenCamera" onclick="openCamera()">Take Photo</button>
                        <button type="button" class="btn btn-danger ml-2" id="btnCancelCapture" onclick="cancelCapture()" style="display: none;">Cancel</button>
                        <button type="button" class="btn btn-primary ml-2" id="btnCapture" onclick="captureImage()" style="display: none;">Capture</button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" name="save_changes">Save Changes</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>

<script>
// Function to preview selected image
function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('preview');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

// Function to open camera and start preview
function openCamera() {
    var video = document.getElementById('camera-preview');
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(function(stream) {
            video.style.display = 'block';
            video.srcObject = stream;
            video.play();

            // Show capture and cancel buttons after opening camera
            document.getElementById('btnOpenCamera').style.display = 'none';
            document.getElementById('btnCapture').style.display = 'inline-block';
            document.getElementById('btnCancelCapture').style.display = 'inline-block';
        })
        .catch(function(error) {
            console.error('Error accessing the camera: ', error);
        });
}

// Function to capture image from camera
function captureImage() {
    var video = document.getElementById('camera-preview');
    var canvas = document.createElement('canvas');
    var context = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Convert canvas image to base64 encoded JPEG data
    var imageDataURL = canvas.toDataURL('image/jpeg');

    // Stop video stream and hide camera preview
    video.pause();
    video.srcObject.getTracks().forEach(function(track) {
        track.stop();
    });
    video.style.display = 'none';

    // Remove capture and cancel buttons after capturing image
    document.getElementById('btnCapture').style.display = 'none';
    document.getElementById('btnCancelCapture').style.display = 'none';
    document.getElementById('btnOpenCamera').style.display = 'inline-block';

    // Prepare form data to submit to server via fetch API
    var formData = new FormData();
    formData.append('username', document.getElementById('username').value);
    formData.append('capture_from_camera', 'true'); // Add flag to identify capture from camera
    formData.append('image_data', imageDataURL); // Send base64 image data

    // Submit form data using fetch API to process_edit_profile.php
    fetch('process_edit_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            return response.json(); // Assuming process_edit_profile.php returns JSON response
        } else {
            throw new Error('Failed to save profile changes.');
        }
    })
    .then(data => {
        if (data.success) {
            alert('Profile updated successfully.');
            window.location.href = 'profile.php'; // Redirect to profile page
        } else {
            throw new Error('Failed to save profile changes.');
        }
    })
    .catch(error => {
        console.error('Error saving profile changes:', error);
        alert('Failed to save profile changes. Please try again.');
    });
}

// Function to cancel camera capture
function cancelCapture() {
    var video = document.getElementById('camera-preview');
    video.pause();
    video.srcObject.getTracks().forEach(function(track) {
        track.stop();
    });
    video.style.display = 'none';

    // Remove capture and cancel buttons
    document.getElementById('btnCapture').style.display = 'none';
    document.getElementById('btnCancelCapture').style.display = 'none';

    // Show open camera button
    document.getElementById('btnOpenCamera').style.display = 'inline-block';
}
</script>

</body>
</html>
