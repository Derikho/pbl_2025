<?php
include_once 'config/database.php';
include_once 'models/GuestBook.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_guestbook'])) {
    $database = new Database();
    $db = $database->getConnection();
    $guest = new GuestBook($db);

    $guest->full_name = $_POST['full_name'];
    $guest->institution = $_POST['institution'];
    $guest->email = $_POST['email'];
    $guest->phone_number = $_POST['phone_number'];
    $guest->message = $_POST['message'];

    if ($guest->create()) {
        echo "<script>
                alert('Terima kasih! Pesan Anda telah terkirim.');
                window.location.href='index.php#contact';
              </script>";
    } else {
        echo "<script>
                alert('Maaf, terjadi kesalahan saat mengirim pesan.');
                window.location.href='index.php#contact';
              </script>";
    }
} else {
    header("Location: index.php");
}
?>