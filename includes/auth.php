<?php
/**
 * Authentication & Authorization
 * Bestlink College HRMS
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['lifetime'=>SESSION_TIMEOUT,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
    session_start();
}
// Session timeout
if (!empty($_SESSION['user_id']) && !empty($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_unset(); session_destroy();
        header('Location: ' . BASE_URL . '/login.php?timeout=1'); exit;
    }
}
if (!empty($_SESSION['user_id'])) $_SESSION['last_activity'] = time();

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) { header('Location: ' . BASE_URL . '/login.php'); exit; }
}
function requireRole(array $allowed): void {
    requireLogin();
    if (!in_array($_SESSION['role_slug'] ?? '', $allowed, true)) {
        http_response_code(403);
        $pageTitle = 'Access Denied';
        $errMsg = 'You do not have permission to access this page.';
        include __DIR__ . '/../views/error.php'; exit;
    }
}
function getCurrentUser(): array { return $_SESSION['user'] ?? []; }
function isAdmin(): bool    { return ($_SESSION['role_slug'] ?? '') === 'admin'; }
function isHR(): bool       { return ($_SESSION['role_slug'] ?? '') === 'hr'; }
function isDeptHead(): bool { return ($_SESSION['role_slug'] ?? '') === 'dept_head'; }
function isSchool(): bool   { return ($_SESSION['role_slug'] ?? '') === 'school'; }
function canAccess(array $roles): bool { return in_array($_SESSION['role_slug'] ?? '', $roles, true); }

function logAudit(string $action, string $module, string $recordId = '', string $description = ''): void {
    try {
        $db = getDB();
        $db->prepare("INSERT INTO audit_logs (user_id,username,role,action,module,record_id,description,ip_address) VALUES (?,?,?,?,?,?,?,?)")
           ->execute([$_SESSION['user_id']??null,$_SESSION['username']??null,$_SESSION['role_name']??null,$action,$module,$recordId,$description,$_SERVER['REMOTE_ADDR']??null]);
    } catch (PDOException $e) { error_log('Audit log: '.$e->getMessage()); }
}
function getUnreadNotifCount(): int {
    if (empty($_SESSION['user_id'])) return 0;
    try {
        $s = getDB()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
        $s->execute([$_SESSION['user_id']]); return (int)$s->fetchColumn();
    } catch (PDOException $e) { return 0; }
}
function getRecentNotifications(int $limit = 5): array {
    if (empty($_SESSION['user_id'])) return [];
    try {
        $s = getDB()->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT ?");
        $s->execute([$_SESSION['user_id'], $limit]); return $s->fetchAll();
    } catch (PDOException $e) { return []; }
}
function loginUser(string $email, string $password): bool {
    $db   = getDB();
    $stmt = $db->prepare("SELECT u.*,r.name as role_name,r.slug as role_slug FROM users u JOIN roles r ON r.id=u.role_id WHERE u.email=? AND u.status='Active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) return false;
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['email']      = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name']  = $user['last_name'];
    $_SESSION['role_id']    = $user['role_id'];
    $_SESSION['role_name']  = $user['role_name'];
    $_SESSION['role_slug']  = $user['role_slug'];
    $_SESSION['dept_id']    = $user['department_id'];
    $_SESSION['employee_id']= $user['employee_id'];
    $_SESSION['last_activity'] = time();
    $_SESSION['user']       = $user;
    $db->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
    logAudit('Login','Auth',(string)$user['id'],'User logged in.');
    return true;
}
