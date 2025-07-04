<?php
require_once __DIR__ . '/../config/admin-auth.php';

$auth = new AdminAuth();
$auth->logout();

// Redirect to login page
header("Location: login.php?message=logged_out");
exit();
?>