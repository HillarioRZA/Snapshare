<?php
// Fungsi untuk menerapkan filter ke gambar
function applyFilters($image, $filters)
{
    foreach ($filters as $filter => $value) {
        switch ($filter) {
            case 'grayscale':
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                break;
            case 'brightness':
                $brightnessLevel = intval($value); // Pastikan nilai brightnessLevel berupa integer
                imagefilter($image, IMG_FILTER_BRIGHTNESS, $brightnessLevel);
                break;
            case 'contrast':
                $contrastLevel = intval($value); // Pastikan nilai contrastLevel berupa integer
                imagefilter($image, IMG_FILTER_CONTRAST, $contrastLevel);
                break;
            case 'sepia':
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 90, 60, 40);
                break;
            case 'invert':
                imagefilter($image, IMG_FILTER_NEGATE);
                break;
            case 'edges':
                imagefilter($image, IMG_FILTER_EDGEDETECT);
                break;
            case 'emboss':
                imagefilter($image, IMG_FILTER_EMBOSS);
                break;
            case 'gaussian_blur':
                imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
                break;
            case 'selective_blur':
                imagefilter($image, IMG_FILTER_SELECTIVE_BLUR);
                break;
            case 'mean_removal':
                imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
                break;
            case 'smooth':
                $smoothness = intval($value); // Pastikan nilai smoothness berupa integer
                imagefilter($image, IMG_FILTER_SMOOTH, $smoothness);
                break;
            default:
                die('Filter tidak dikenali');
        }
    }
}

// Inisialisasi variabel
$upload_error = '';
$previewImageSrc = '';

// Proses jika form dikirim (saat tombol Apply Filter ditekan)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Allowed file types
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        // Validate file type
        if (in_array($filetype, $allowed)) {
            $tempfile = $_FILES['image']['tmp_name'];

            // Create GD image resource based on file type
            switch ($filetype) {
                case 'jpg':
                case 'jpeg':
                    $image = imagecreatefromjpeg($tempfile);
                    break;
                case 'png':
                    $image = imagecreatefrompng($tempfile);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($tempfile);
                    break;
                default:
                    die('Format gambar tidak didukung');
            }

            // Process selected filters
            $filters = [];
            if (isset($_POST['filters']) && is_array($_POST['filters'])) {
                foreach ($_POST['filters'] as $filter) {
                    if ($filter === 'brightness' || $filter === 'contrast' || $filter === 'smooth') {
                        // Ambil nilai dari input range terkait
                        $filters[$filter] = $_POST[$filter . '_level'] ?? 0;
                    } else {
                        // Filter tanpa parameter khusus
                        $filters[$filter] = true;
                    }
                }
            }
            applyFilters($image, $filters);

            // Generate unique filename with prefix 'filtered_' and save processed image
            $newfilename = 'filtered_' . basename($_FILES['image']['name'], '.' . $filetype) . '_' . uniqid() . '.' . $filetype;
            switch ($filetype) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $newfilename);
                    break;
                case 'png':
                    imagepng($image, $newfilename);
                    break;
                case 'gif':
                    imagegif($image, $newfilename);
                    break;
                default:
                    die('Format gambar tidak didukung');
            }

            // Destroy image resource to free memory
            imagedestroy($image);

            // Set preview image source for display
            $previewImageSrc = $newfilename;
        } else {
            $upload_error = 'Format file tidak didukung. Silakan upload file JPG, JPEG, PNG, atau GIF.';
        }
    } else {
        $upload_error = 'Ada masalah dengan upload gambar Anda.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Filters - SnapShare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container{
            margin-top: 5rem;
        }
        img {
            max-width: 100%;
            height: auto;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2 class="text-center mb-5">Apply Filters to Image</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" id="filterForm">
                    <div class="form-group">
                        <label for="image">Select Image:</label>
                        <input type="file" class="form-control-file" id="image" name="image" accept="image/*" required>
                    </div>
                    <div class="form-group">
                        <label>Choose Filters:</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="grayscale" name="filters[]" value="grayscale">
                            <label class="form-check-label" for="grayscale">Grayscale</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="brightness" name="filters[]" value="brightness">
                            <label class="form-check-label" for="brightness">Brightness</label>
                            <input type="range" min="-100" max="100" value="0" class="form-control-range" id="brightness_level" name="brightness_level">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="contrast" name="filters[]" value="contrast">
                            <label class="form-check-label" for="contrast">Contrast</label>
                            <input type="range" min="-100" max="100" value="0" class="form-control-range" id="contrast_level" name="contrast_level">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sepia" name="filters[]" value="sepia">
                            <label class="form-check-label" for="sepia">Sepia</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="invert" name="filters[]" value="invert">
                            <label class="form-check-label" for="invert">Invert</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="edges" name="filters[]" value="edges">
                            <label class="form-check-label" for="edges">Edges</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="emboss" name="filters[]" value="emboss">
                            <label class="form-check-label" for="emboss">Emboss</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="gaussian_blur" name="filters[]" value="gaussian_blur">
                            <label class="form-check-label" for="gaussian_blur">Gaussian Blur</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selective_blur" name="filters[]" value="selective_blur">
                            <label class="form-check-label" for="selective_blur">Selective Blur</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mean_removal" name="filters[]" value="mean_removal">
                            <label class="form-check-label" for="mean_removal">Mean Removal</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="smooth" name="filters[]" value="smooth">
                            <label class="form-check-label" for="smooth">Smooth</label>
                            <input type="range" min="0" max="20" value="0" class="form-control-range" id="smooth_level" name="smooth_level">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </form>

                <!-- Preview Image -->
                <?php if (!empty($previewImageSrc)): ?>
                    <div class="mt-5">
                        <h4 class="text-center">Preview:</h4>
                        <img src="<?php echo $previewImageSrc; ?>" alt="Filtered Image" class="img-fluid">
                        <p class="text-center mt-3"><a href="<?php echo $previewImageSrc; ?>" class="btn btn-success" download>Download Preview</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <!-- Bootstrap JS and Custom Script -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Event listener for form submission
        document.getElementById('filterForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Submit the form programmatically after processing
            this.submit();
        });
    </script>
</body>
</html>
