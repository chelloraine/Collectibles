<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) {
    $upload_dir = "uploads/";  // Target folder
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create uploads folder if not exists
    }

    // Generate a unique file name
    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $upload_dir . $file_name;

    // Check file type (allow JPG, PNG, GIF)
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_types)) {
        die("Invalid file type. Only JPG, PNG, and GIF allowed.");
    }

    // Move file to uploads folder
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "Image uploaded successfully! <br>";
        echo "<img src='$target_file' width='200'>";
    } else {
        echo "Error uploading file.";
    }
}
?>
