<?php
require_once '../includes/db_connection.php';
// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $batch = $_POST['batch'];
    $file = $_FILES['image'];
    $target_dir = "../uploads/media_gallery/";
    $file_name = basename($file["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO ojt_media_gallery (batch, image) VALUES (?, ?)");
        $stmt->bind_param("ss", $batch, $target_file);
        $stmt->execute();
        $stmt->close();
        $message = "Image uploaded successfully!";
    } else {
        $error = "Error uploading image.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $result = $conn->query("SELECT image FROM ojt_media_gallery WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        @unlink($row['image']);
    }
    $conn->query("DELETE FROM ojt_media_gallery WHERE id = $id");
    $message = "Image deleted.";
}

// Fetch images
$images = [];
$result = $conn->query("SELECT * FROM ojt_media_gallery ORDER BY batch, uploaded_at DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin OJT Media Gallery</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .gallery { display: flex; flex-wrap: wrap; gap: 1rem; }
        .gallery-item { border: 1px solid #eee; padding: 0.5rem; border-radius: 8px; width: 180px; text-align: center; }
        .gallery-item img { width: 100%; height: 120px; object-fit: cover; border-radius: 4px; }
        .success { color: green; }
        .error { color: red; }
        .delete-btn { color: #fff; background: #e53e3e; border: none; padding: 0.3rem 0.7rem; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h2>OJT Media Gallery Admin</h2>
    <?php if (isset($message)) echo "<div class='success'>$message</div>"; ?>
    <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data" style="margin-bottom:2rem;">
        <label>Batch:
            <select name="batch" required>
                <option value="Batch 1">Batch 1</option>
                <option value="Batch 2">Batch 2</option>
            </select>
        </label>
        <label>Image:
            <input type="file" name="image" accept="image/*" required>
        </label>
        <button type="submit">Upload</button>
    </form>

    <div class="gallery">
        <?php foreach ($images as $img): ?>
            <div class="gallery-item">
                <div><strong><?php echo htmlspecialchars($img['batch']); ?></strong></div>
                <img src="<?php echo htmlspecialchars($img['image']); ?>" alt="">
                <form method="get" onsubmit="return confirm('Delete this image?');">
                    <input type="hidden" name="delete" value="<?php echo $img['id']; ?>">
                    <button class="delete-btn" type="submit">Delete</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>