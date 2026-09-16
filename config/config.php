<?php
/**
 * Application Configuration
 * Bestlink College HRMS
 */

define('BASE_URL', '/bestlink_hrms');
define('UPLOAD_PATH',     __DIR__ . '/../uploads/');
define('UPLOAD_RESUMES',  __DIR__ . '/../uploads/resumes/');
define('UPLOAD_DOCS',     __DIR__ . '/../uploads/employee_documents/');
define('UPLOAD_CERTS',    __DIR__ . '/../uploads/certificates/');
define('ALLOWED_TYPES',   ['pdf','doc','docx','jpg','jpeg','png']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);
define('AI_SERVICE_URL',  'http://127.0.0.1:8000');
define('SESSION_TIMEOUT', 3600);
date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Invalid request token.');
    }
}
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}
function flash(string $key, string $msg): void { $_SESSION['flash'][$key] = $msg; }
function getFlash(string $key): string {
    $msg = $_SESSION['flash'][$key] ?? '';
    unset($_SESSION['flash'][$key]);
    return $msg;
}
function e(string $str): string { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
function paginate(int $total, int $perPage, int $page): array {
    $totalPages = (int) ceil($total / max(1, $perPage));
    $page       = max(1, min($page, max(1, $totalPages)));
    return ['total'=>$total,'per_page'=>$perPage,'page'=>$page,'total_pages'=>$totalPages,'offset'=>($page-1)*$perPage];
}
function peso(float $amount): string { return '&#8369; ' . number_format($amount, 2); }
function generateCode(string $prefix, string $table, string $col): string {
    $db   = getDB();
    $year = date('Y');
    $stmt = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$col` LIKE ?");
    $stmt->execute(["$prefix-$year-%"]);
    $count = (int) $stmt->fetchColumn() + 1;
    return sprintf('%s-%s-%03d', $prefix, $year, $count);
}
function badgeClass(string $status): string {
    $map = [
        'Active'=>'badge-success','Approved'=>'badge-success','Present'=>'badge-success',
        'HR Approved'=>'badge-success','Completed'=>'badge-success','Paid'=>'badge-success',
        'Excellent'=>'badge-success','Hired'=>'badge-success','Selected'=>'badge-success',
        'Pending'=>'badge-warning','Dept Approved'=>'badge-warning','Shortlisted'=>'badge-warning',
        'Screening'=>'badge-warning','Interview'=>'badge-warning','Upcoming'=>'badge-warning',
        'Open'=>'badge-info','Under Review'=>'badge-info','Ongoing'=>'badge-info',
        'Draft'=>'badge-info','Enrolled'=>'badge-info','Scheduled'=>'badge-info',
        'Inactive'=>'badge-secondary','Rejected'=>'badge-danger','Absent'=>'badge-danger',
        'Terminated'=>'badge-danger','Cancelled'=>'badge-danger','Unsatisfactory'=>'badge-danger',
        'Late'=>'badge-warning','Half Day'=>'badge-warning','On Leave'=>'badge-info',
    ];
    return $map[$status] ?? 'badge-secondary';
}
