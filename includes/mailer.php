<?php
/**
 * Mailer Helper — Bestlink HRMS
 * Uses PHPMailer + Gmail SMTP for reliable email delivery.
 * Improved for better inbox delivery (anti-spam headers).
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

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

// ── SMTP Credentials (loaded from .env) ─────────────────────────────────────
define('MAIL_HOST',     getenv('MAIL_HOST')     ?: ($_ENV['MAIL_HOST']     ?? 'smtp.gmail.com'));
define('MAIL_PORT',     getenv('MAIL_PORT')     ?: ($_ENV['MAIL_PORT']     ?? 587));
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: ($_ENV['MAIL_USERNAME'] ?? 'bestlinkcollegeoftheph@gmail.com'));
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: ($_ENV['MAIL_PASSWORD'] ?? 'aqrhrpabsyuusovv'));
define('MAIL_FROM',     getenv('MAIL_FROM')     ?: ($_ENV['MAIL_FROM']     ?? 'bestlinkcollegeoftheph@gmail.com'));
define('MAIL_FROM_NAME','Bestlink College HR Department');
// ───────────────────────────────────────────────────────────────────────────

/**
 * Send an email using PHPMailer over Gmail SMTP.
 * Includes anti-spam improvements: proper headers, plain-text alt body.
 *
 * @param string       $to          Recipient email address
 * @param string       $toName      Recipient name
 * @param string       $subject     Email subject
 * @param string       $htmlBody    HTML body content
 * @param string|null  $plainText   Plain-text fallback (auto-generated if null)
 * @return array ['ok' => bool, 'error' => string]
 */
function sendMail(string $to, string $toName, string $subject, string $htmlBody, ?string $plainText = null): array
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->XMailer    = ' '; // Hide mailer identity

        // Anti-spam: proper message ID with a real domain (not .local)
        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'bestlink.edu.ph';
        $mail->MessageID  = '<' . uniqid('hrms_', true) . '@' . $domain . '>';

        // Sender & recipient
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to, $toName);
        $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);

        // Anti-spam headers
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->addCustomHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:' . MAIL_FROM . '?subject=unsubscribe>');
        $mail->addCustomHeader('Organization', 'Bestlink College of the Philippines');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        // Generate a clean plain-text fallback to help avoid spam filters
        $mail->AltBody = $plainText ?? wordwrap(
            strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>', '</div>', '</td>'], "\n",
                preg_replace('/<style[^>]*>.*?<\/style>/si', '', $htmlBody)
            )),
            72, "\n"
        );

        $mail->send();
        return ['ok' => true, 'error' => ''];

    } catch (Exception $e) {
        error_log('[Mailer] Failed to send to ' . $to . ': ' . $mail->ErrorInfo);
        return ['ok' => false, 'error' => $mail->ErrorInfo];
    }
}

// ─── Shared HTML email wrapper ────────────────────────────────────────────────
function mailWrapper(string $headerBg, string $statusIcon, string $statusLabel, string $bodyContent, string $firstName): string
{
    $portalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/login.php';
    $year = date('Y');
    return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#F3F4F6;font-family:Arial,Helvetica,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F3F4F6;padding:32px 0;">
    <tr><td align="center">
      <table width="580" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,0.09);">

        <!-- Header Banner -->
        <tr><td style="background:' . $headerBg . ';padding:32px 40px;text-align:center;">
          <div style="font-size:36px;margin-bottom:10px;">' . $statusIcon . '</div>
          <h1 style="margin:0;font-size:20px;font-weight:800;color:#fff;letter-spacing:-0.5px;">Bestlink College HRMS</h1>
          <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,0.75);">Human Resource Management System</p>
        </td></tr>

        <!-- Status Badge -->
        <tr><td style="padding:0 40px;">
          <div style="background:#F0F4FF;border-left:4px solid ' . $headerBg . ';padding:14px 18px;margin:24px 0 0;border-radius:0 8px 8px 0;">
            <span style="font-size:12px;font-weight:700;text-transform:uppercase;color:#6B7280;letter-spacing:0.08em;">Application Status Update</span><br>
            <span style="font-size:18px;font-weight:800;color:#111;">' . $statusLabel . '</span>
          </div>
        </td></tr>

        <!-- Body -->
        <tr><td style="padding:24px 40px 32px;">
          ' . $bodyContent . '
        </td></tr>

        <!-- CTA Button -->
        <tr><td style="padding:0 40px 32px;text-align:center;">
          <a href="' . $portalUrl . '" style="display:inline-block;background:#1D4ED8;color:#fff;text-decoration:none;padding:13px 32px;border-radius:8px;font-size:14px;font-weight:700;">
            Visit Employee Portal →
          </a>
        </td></tr>

        <!-- Footer -->
        <tr><td style="background:#F9FAFB;border-top:1px solid #E5E7EB;padding:18px 40px;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9CA3AF;">
            © ' . $year . ' Bestlink College of the Philippines &bull; HR Department<br>
            This is an automated notification. Please do not reply to this email.
          </p>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>';
}

// ─── Email: Hired (with login credentials) ────────────────────────────────────
function sendHiredEmail(string $to, string $firstName, string $position, string $username, string $rawPassword): array
{
    $portalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
               . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/login.php';

    $subject = 'Congratulations! You Are Officially Hired — Bestlink College HRMS';

    $body = '
      <h2 style="margin:0 0 6px;font-size:22px;font-weight:800;color:#111;">Congratulations, ' . htmlspecialchars($firstName) . '! 🎉</h2>
      <p style="margin:0 0 20px;font-size:15px;color:#6B7280;">We are thrilled to officially welcome you to the Bestlink College family. You have been successfully hired!</p>

      <div style="background:#F0F9FF;border:1.5px solid #BAE6FD;border-radius:10px;padding:18px 22px;margin-bottom:22px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:700;text-transform:uppercase;color:#0369A1;letter-spacing:0.06em;">Position</p>
        <p style="margin:0;font-size:17px;font-weight:700;color:#0C4A6E;">' . htmlspecialchars($position) . '</p>
      </div>

      <p style="margin:0 0 12px;font-size:14px;color:#374151;font-weight:700;">Your Employee Portal Login Credentials:</p>
      <table width="100%" cellpadding="0" cellspacing="0" style="background:#F9FAFB;border:1.5px solid #E5E7EB;border-radius:10px;margin-bottom:20px;">
        <tr><td style="padding:13px 18px;border-bottom:1px solid #E5E7EB;">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Login URL</span><br>
          <a href="' . $portalUrl . '" style="font-size:14px;color:#2563EB;font-weight:600;">' . $portalUrl . '</a>
        </td></tr>
        <tr><td style="padding:13px 18px;border-bottom:1px solid #E5E7EB;">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Email (Username)</span><br>
          <span style="font-size:14px;color:#111;font-weight:600;font-family:monospace;">' . htmlspecialchars($to) . '</span>
        </td></tr>
        <tr><td style="padding:13px 18px;">
          <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Temporary Password</span><br>
          <span style="font-size:20px;font-weight:800;color:#111;font-family:monospace;letter-spacing:3px;">' . htmlspecialchars($rawPassword) . '</span>
        </td></tr>
      </table>

      <div style="background:#FEF3C7;border:1.5px solid #FDE68A;border-radius:8px;padding:13px 18px;">
        <p style="margin:0;font-size:13px;color:#92400E;">
          ⚠️ <strong>Security Notice:</strong> Please change your password immediately after your first login. You will be required to do so before accessing any features.
        </p>
      </div>';

    $html = mailWrapper('linear-gradient(135deg,#065F46,#059669)', '🎉', 'Hired — Welcome Aboard!', $body, $firstName);
    return sendMail($to, $firstName, $subject, $html);
}

// ─── Email: Application Status Update (generic) ───────────────────────────────
function sendStatusUpdateEmail(string $to, string $firstName, string $position, string $newStatus): array
{
    $configs = [
        'Screening' => [
            'subject' => 'Your Application is Under Review — Bestlink College HRMS',
            'icon'    => '🔍',
            'label'   => 'Under Review / Screening',
            'bg'      => 'linear-gradient(135deg,#1E3A5F,#2F7BEE)',
            'message' => 'Good news! Your application for <strong>' . htmlspecialchars($position) . '</strong> is now being reviewed by our HR team. We will carefully evaluate your qualifications and get back to you soon.',
            'note'    => 'You don\'t need to do anything at this stage. Just sit tight — we\'ll be in touch!',
        ],
        'Interview' => [
            'subject' => 'You Have Been Invited for an Interview — Bestlink College HRMS',
            'icon'    => '📅',
            'label'   => 'Interview Invitation',
            'bg'      => 'linear-gradient(135deg,#4338CA,#7C3AED)',
            'message' => 'Fantastic news, <strong>' . htmlspecialchars($firstName) . '</strong>! You have been selected for an interview for the position of <strong>' . htmlspecialchars($position) . '</strong>. Our HR team will contact you directly to confirm the schedule, date, and format of your interview.',
            'note'    => 'Please ensure your contact details are up to date and be ready to receive a call or email from our HR department.',
        ],
        'Shortlisted' => [
            'subject' => 'Congratulations! You Have Been Shortlisted — Bestlink College HRMS',
            'icon'    => '⭐',
            'label'   => 'Shortlisted',
            'bg'      => 'linear-gradient(135deg,#B45309,#F59E0B)',
            'message' => 'Great news, <strong>' . htmlspecialchars($firstName) . '</strong>! You have been shortlisted as one of our top candidates for the position of <strong>' . htmlspecialchars($position) . '</strong>. This is a significant step forward in the hiring process!',
            'note'    => 'Our HR team will reach out to you shortly with next steps. Thank you for your patience.',
        ],
        'Rejected' => [
            'subject' => 'Update on Your Application — Bestlink College HRMS',
            'icon'    => '📩',
            'label'   => 'Application Update',
            'bg'      => 'linear-gradient(135deg,#7F1D1D,#DC2626)',
            'message' => 'Thank you sincerely for your interest in the position of <strong>' . htmlspecialchars($position) . '</strong> at Bestlink College of the Philippines and for the time you invested in your application. After careful consideration, we regret to inform you that we will not be moving forward with your application at this time.',
            'note'    => 'We encourage you to continue developing your skills and to apply for future openings that match your qualifications. We wish you all the best in your career journey!',
        ],
    ];

    if (!isset($configs[$newStatus])) {
        return ['ok' => false, 'error' => 'No email template for status: ' . $newStatus];
    }

    $cfg = $configs[$newStatus];

    $body = '
      <h2 style="margin:0 0 6px;font-size:20px;font-weight:800;color:#111;">Dear ' . htmlspecialchars($firstName) . ',</h2>
      <p style="margin:0 0 20px;font-size:15px;color:#6B7280;">' . $cfg['message'] . '</p>

      <div style="background:#F9FAFB;border:1.5px solid #E5E7EB;border-radius:10px;padding:16px 20px;margin-bottom:20px;">
        <span style="font-size:11px;font-weight:700;text-transform:uppercase;color:#9CA3AF;">Position Applied</span><br>
        <span style="font-size:16px;font-weight:700;color:#111;">' . htmlspecialchars($position) . '</span>
      </div>

      <div style="background:#EFF6FF;border:1.5px solid #BFDBFE;border-radius:8px;padding:13px 18px;">
        <p style="margin:0;font-size:13px;color:#1E40AF;">
          ℹ️ ' . $cfg['note'] . '
        </p>
      </div>';

    $html = mailWrapper($cfg['bg'], $cfg['icon'], $cfg['label'], $body, $firstName);
    $plainText = 'Dear ' . $firstName . ",\n\nApplication Status Update: " . $newStatus . "\n\nPosition: " . $position . "\n\n" . strip_tags($cfg['message']) . "\n\n" . strip_tags($cfg['note']) . "\n\nBestlink College HR Department";

    return sendMail($to, $firstName, $cfg['subject'], $html, $plainText);
}
