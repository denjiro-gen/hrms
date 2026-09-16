<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    logAudit('Logout', 'Auth', (string)$_SESSION['user_id'], 'User logged out.');
}
session_unset();
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
