<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['admin', 'hr']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: jobs/index.php");
    exit;
}
verifyCsrf();

$applicationId = (int)($_POST['application_id'] ?? 0);
if (!$applicationId) {
    die("Invalid application ID.");
}

$db = getDB();

// Fetch application and job details (including resume_path for CV analysis)
$stmt = $db->prepare("SELECT a.*, app.skills as applicant_skills, app.highest_education, app.years_experience,
                      app.resume_path,
                      j.required_skills, j.preferred_skills, j.min_education, j.experience_required 
                      FROM applications a 
                      JOIN applicants app ON a.applicant_id = app.id 
                      JOIN job_postings j ON a.job_posting_id = j.id
                      WHERE a.id = ?");
$stmt->execute([$applicationId]);
$data = $stmt->fetch();

if (!$data) {
    die("Application not found.");
}

// Check if AI Service is enabled
$settings = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'ai_service_enabled'")->fetch();
$aiEnabled = $settings ? (int)$settings['setting_value'] : 1;

if (!$aiEnabled) {
    flash('error', 'AI Service is currently disabled in system settings.');
    header("Location: applicants/view.php?id=$applicationId");
    exit;
}

// Build absolute path to the CV file so Python can read it directly
$cvAbsPath = null;
if (!empty($data['resume_path'])) {
    $candidate = __DIR__ . '/../../uploads/resumes/' . $data['resume_path'];
    if (file_exists($candidate)) {
        $cvAbsPath = realpath($candidate);
    }
}

// Prepare payload for FastAPI Python service
$payload = [
    'applicant' => [
        'skills'    => array_filter(array_map('trim', explode(',', $data['applicant_skills'] ?? ''))),
        'education' => $data['highest_education'] ?? '',
        'experience'=> (float)($data['years_experience'] ?? 0),
        'cv_path'   => $cvAbsPath,   // Absolute path — Python reads file directly
    ],
    'job' => [
        'required_skills'   => array_filter(array_map('trim', explode(',', $data['required_skills'] ?? ''))),
        'preferred_skills'  => array_filter(array_map('trim', explode(',', $data['preferred_skills'] ?? ''))),
        'min_education'     => $data['min_education'] ?? '',
        'experience_required' => $data['experience_required'] ?? ''
    ]
];

// In a real scenario, this cURL points to the FastAPI service.
// As the Python service might not be running yet, we'll gracefully fallback to a heuristic match in PHP.
$aiUrl = AI_SERVICE_URL . '/api/match';

$ch = curl_init($aiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 30s — CV parsing can take a moment

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$matchedSkills = [];
$missingSkills = [];
$matchScore = 0.0;
$recommendation = 'Review Manually';

if ($httpCode === 200 && $response) {
    $result = json_decode($response, true);
    if (isset($result['match_score'])) {
        $matchScore      = $result['match_score'];
        $matchedSkills   = $result['matched_skills'] ?? [];
        $missingSkills   = $result['missing_skills'] ?? [];
        $recommendation  = $result['recommendation'] ?? 'Review Manually';
        $cvSkillsFound   = $result['cv_skills_extracted'] ?? 0;
        $cvNote = $cvAbsPath ? " (CV analyzed: $cvSkillsFound extra skills extracted from document)" : " (No CV file found for deep analysis)";
    }
} else {
    // GRACEFUL FALLBACK: Basic heuristic matching if AI server is down
    $appSkills = array_map('strtolower', $payload['applicant']['skills']);
    $reqSkills = array_map('strtolower', $payload['job']['required_skills']);
    
    foreach ($reqSkills as $rs) {
        if (empty($rs)) continue;
        $found = false;
        foreach ($appSkills as $as) {
            if (str_contains($as, $rs) || str_contains($rs, $as)) {
                $found = true;
                break;
            }
        }
        if ($found) {
            // Find original case
            foreach($payload['job']['required_skills'] as $orig) {
                if(strtolower($orig) === $rs) { $matchedSkills[] = $orig; break; }
            }
        } else {
            foreach($payload['job']['required_skills'] as $orig) {
                if(strtolower($orig) === $rs) { $missingSkills[] = $orig; break; }
            }
        }
    }
    
    $reqCount = count(array_filter($reqSkills));
    $matchScore = $reqCount > 0 ? (count($matchedSkills) / $reqCount) * 100 : 80; // base score if no skills required
    
    // Add education/experience weight
    if ($payload['applicant']['experience'] >= (float)$payload['job']['experience_required']) {
        $matchScore = min(100, $matchScore + 5);
    }
    
    if ($matchScore >= 85) $recommendation = 'Excellent Match';
    elseif ($matchScore >= 70) $recommendation = 'Good Match';
    elseif ($matchScore >= 50) $recommendation = 'Moderate Match';
    else $recommendation = 'Low Match';
    
    error_log("AI Service unreachable ($httpCode). Using fallback heuristic match.");
    flash('info', 'AI Service unreachable. Used basic heuristic matching instead.');
}

// Upsert to recruitment_ai_results table
$check = $db->prepare("SELECT id FROM recruitment_ai_results WHERE application_id = ?");
$check->execute([$applicationId]);
$exists = $check->fetchColumn();

if ($exists) {
    $stmt = $db->prepare("UPDATE recruitment_ai_results SET match_score=?, matched_skills=?, missing_skills=?, recommendation=? WHERE application_id=?");
    $stmt->execute([
        $matchScore, 
        json_encode(array_values(array_unique($matchedSkills))), 
        json_encode(array_values(array_unique($missingSkills))), 
        $recommendation, 
        $applicationId
    ]);
} else {
    $stmt = $db->prepare("INSERT INTO recruitment_ai_results (application_id, match_score, matched_skills, missing_skills, recommendation) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $applicationId, 
        $matchScore, 
        json_encode(array_values(array_unique($matchedSkills))), 
        json_encode(array_values(array_unique($missingSkills))), 
        $recommendation
    ]);
}

logAudit('AI Match Evaluation', 'Recruitment', (string)$applicationId, "Generated AI match score: " . number_format($matchScore, 1) . "%" . ($cvNote ?? ''));
flash('success', 'AI Evaluation complete! Score: ' . number_format($matchScore, 1) . '% — ' . ($recommendation ?? '') . ($cvNote ?? ''));
header("Location: applicants/view.php?id=$applicationId");
exit;
