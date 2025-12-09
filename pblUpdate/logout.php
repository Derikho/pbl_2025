<?php
// Handle logout untuk semua role

// 1. Cek jika ini logout member
if(isset($_GET['type']) && $_GET['type'] === 'member') {
    session_name('MEMBER_SESSION');
    session_start();
    
    // Clear semua session member
    unset($_SESSION['member_logged_in']);
    unset($_SESSION['member_user_id']);
    unset($_SESSION['member_username']);
    unset($_SESSION['member_full_name']);
    unset($_SESSION['member_nim']);
    unset($_SESSION['member_institution']);
    unset($_SESSION['member_email']);
    unset($_SESSION['member_role']);
    unset($_SESSION['member_student_type']);
    unset($_SESSION['member_login_time']);
    
    session_destroy();
    setcookie('MEMBER_SESSION', '', time() - 3600, '/');
    
    header("location: login.php");
    exit;
}

// 2. Logout biasa (admin/dosen)
session_start();
$_SESSION = array();
session_destroy();
header("location: login.php");
exit;
?>