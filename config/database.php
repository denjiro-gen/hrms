<?php
/**
 * Database Configuration — Bestlink College HRMS
 *
 * LOCAL  → uses MySQL on XAMPP (DB_HOST, DB_NAME, DB_USER, DB_PASS)
 * HOSTED → set the environment variable DATABASE_URL to your Supabase
 *          PostgreSQL connection string and the app will switch automatically.
 *
 * Supabase connection string format:
 *   postgresql://postgres:[PASSWORD]@db.[PROJECT_REF].supabase.co:5432/postgres
 */

// ── Load .env file if it exists (for local overrides) ──────────────────────
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
        putenv(trim($key) . '=' . trim($val));
    }
}

// ── Detect environment ──────────────────────────────────────────────────────
$databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);

if (!$databaseUrl) {
    // ── LOCAL: MySQL / XAMPP ────────────────────────────────────────────────
    define('DB_DRIVER',  'mysql');
    define('DB_HOST',    getenv('DB_HOST')  ?: ($_ENV['DB_HOST']  ?? 'localhost'));
    define('DB_NAME',    getenv('DB_NAME')  ?: ($_ENV['DB_NAME']  ?? 'bestlink_hrms'));
    define('DB_USER',    getenv('DB_USER')  ?: ($_ENV['DB_USER']  ?? 'root'));
    define('DB_PASS',    getenv('DB_PASS')  ?: ($_ENV['DB_PASS']  ?? ''));
    define('DB_CHARSET', 'utf8mb4');
    define('DB_PORT',    '3306');
}

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => true, // Huge speed boost for cloud databases
    ];

    // Check for Supabase PostgreSQL connection string
    $databaseUrl = getenv('DATABASE_URL') ?: ($_ENV['DATABASE_URL'] ?? null);

    try {
        if ($databaseUrl) {
            // ── SUPABASE: PostgreSQL via DATABASE_URL ───────────────────────
            // Parse the URL: postgresql://user:pass@host:port/dbname
            $parts = parse_url($databaseUrl);
            $dsn   = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s;sslmode=require',
                $parts['host'],
                $parts['port'] ?? 5432,
                ltrim($parts['path'], '/')
            );
            $user = $parts['user'] ?? 'postgres';
            $pass = urldecode($parts['pass'] ?? '');
            $pdo  = new PDO($dsn, $user, $pass, $options);
        } else {
            // ── LOCAL: MySQL ────────────────────────────────────────────────
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        }
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        die('Database connection failed. Please check your configuration.');
    }

    return $pdo;
}

