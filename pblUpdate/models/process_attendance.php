<?php
session_start();
include_once 'config/database.php';
include_once 'models/Attendance.php';

// Pastikan hanya member yang bisa akses
if(!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'member'){
    header("location: index.php");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $database = new Database();
    $db = $database->getConnection();
    $attendance = new Attendance($db);

    $attendance->user_id = $_SESSION['id'] ?? $_SESSION['user_id'];
    $attendance->location_note = $_POST['location_note'];

    // --- PROSES UPLOAD FOTO ---
    $target_dir = "assets/uploads/attendance/";
    
    // Buat folder jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["photo_proof"]["name"]); // Rename file agar unik
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Cek apakah file benar-benar gambar
    $check = getimagesize($_FILES["photo_proof"]["tmp_name"]);
    if($check === false) {
        echo "<script>alert('File bukan gambar.'); window.location.href='index.php';</script>";
        exit;
    }

    // Upload File
    if (move_uploaded_file($_FILES["photo_proof"]["tmp_name"], $target_file)) {
        // Simpan path ke properti model
        $attendance->photo_url = $target_file;

        // Simpan ke Database
        $result = $attendance->checkIn();

        if($result == "success"){
            echo "<script>alert('Presensi Berhasil! Bukti foto telah terupload.'); window.location.href='index.php';</script>";
        } elseif($result == "already_checked_in"){
            echo "<script>alert('Anda sudah melakukan presensi hari ini.'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan data ke database.'); window.location.href='index.php';</script>";
        }

    } else {
        echo "<script>alert('Maaf, terjadi error saat mengupload foto.'); window.location.href='index.php';</script>";
    }
} else {
    header("location: index.php");
}
?>