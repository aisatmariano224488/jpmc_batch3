<?php
// Directories to store uploaded files
$targetDirImages = $_SERVER['DOCUMENT_ROOT'] . '/images/plant_visit/';
$targetDirVideos = $_SERVER['DOCUMENT_ROOT'] . '/videos/plant_visit/';

if (!file_exists($targetDirImages)) {
    mkdir($targetDirImages, 0777, true);
}
if (!file_exists($targetDirVideos)) {
    mkdir($targetDirVideos, 0777, true);
}

// Get parameters from Resumable.js
$chunkNumber = isset($_POST['resumableChunkNumber']) ? intval($_POST['resumableChunkNumber']) : 0;
$totalChunks = isset($_POST['resumableTotalChunks']) ? intval($_POST['resumableTotalChunks']) : 0;
$identifier = isset($_POST['resumableIdentifier']) ? preg_replace('/[^0-9A-Za-z_-]/', '', $_POST['resumableIdentifier']) : '';
$filename = isset($_POST['resumableFilename']) ? basename($_POST['resumableFilename']) : '';

// Determine if it's an image or video based on file extension
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp']);
$isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'webm']);

// Choose the appropriate target directory
$targetDir = $isImage ? $targetDirImages : $targetDirVideos;

// Chunk file path
$chunkFile = $targetDir . $identifier . '.part' . $chunkNumber;

// Save the chunk
if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    move_uploaded_file($_FILES['file']['tmp_name'], $chunkFile);
}

// Check if all chunks are uploaded
$allUploaded = true;
for ($i = 1; $i <= $totalChunks; $i++) {
    if (!file_exists($targetDir . $identifier . '.part' . $i)) {
        $allUploaded = false;
        break;
    }
}

if ($allUploaded) {
    $finalFile = $targetDir . time() . '_' . $filename;
    if (($out = fopen($finalFile, 'wb')) !== false) {
        for ($i = 1; $i <= $totalChunks; $i++) {
            $chunk = $targetDir . $identifier . '.part' . $i;
            $in = fopen($chunk, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
            unlink($chunk);
        }
        fclose($out);
        
        // Return the relative path based on file type
        if ($isImage) {
            $relativePath = 'images/plant_visit/' . basename($finalFile);
        } else {
            $relativePath = 'videos/plant_visit/' . basename($finalFile);
        }
        
        echo json_encode(['success' => true, 'file' => $relativePath, 'type' => $isImage ? 'image' : 'video']);
        exit;
    }
}

echo json_encode(['success' => true]); 