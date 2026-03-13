<?php
// Disable error display
ini_set('display_errors', 0);
error_reporting(0);

// Security headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header("Content-Security-Policy: default-src 'self'");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Optional: Check origin to prevent CSRF on API (though not strictly needed)
$allowed_origins = ['http://localhost', 'https://yourdomain.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (!in_array($origin, $allowed_origins)) {
    // Still allow if no origin (direct request) – you may tighten this
}

// Start secure session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Initialize history
if (!isset($_SESSION['password_history'])) {
    $_SESSION['password_history'] = [];
}

require_once dirname(__DIR__) . '/includes/functions.php';

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate with defaults
$length = isset($input['length']) ? filter_var($input['length'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 4, 'max_range' => 64]]) : 16;
if ($length === false) $length = 16;

$useUppercase = isset($input['uppercase']) ? filter_var($input['uppercase'], FILTER_VALIDATE_BOOLEAN) : true;
$useLowercase = isset($input['lowercase']) ? filter_var($input['lowercase'], FILTER_VALIDATE_BOOLEAN) : true;
$useNumbers = isset($input['numbers']) ? filter_var($input['numbers'], FILTER_VALIDATE_BOOLEAN) : true;
$useSymbols = isset($input['symbols']) ? filter_var($input['symbols'], FILTER_VALIDATE_BOOLEAN) : true;
$excludeSimilar = isset($input['excludeSimilar']) ? filter_var($input['excludeSimilar'], FILTER_VALIDATE_BOOLEAN) : false;
$generatorType = isset($input['generatorType']) && in_array($input['generatorType'], ['random', 'passphrase']) ? $input['generatorType'] : 'random';
$wordCount = isset($input['wordCount']) ? filter_var($input['wordCount'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 2, 'max_range' => 8]]) : 4;
if ($wordCount === false) $wordCount = 4;
$separator = isset($input['separator']) ? substr(preg_replace('/[^a-zA-Z0-9\-_]/', '', $input['separator']), 0, 2) : '-';

// Ensure at least one character type
if (!$useUppercase && !$useLowercase && !$useNumbers && !$useSymbols) {
    $useLowercase = true;
}

// Generate password
if ($generatorType === 'passphrase') {
    $password = generatePassphrase($wordCount, $separator);
} else {
    $password = generateSecurePassword($length, $useUppercase, $useLowercase, $useNumbers, $useSymbols, $excludeSimilar);
}

// Calculate strength
$strength = calculatePasswordStrength($password);

// Add to history (limit to 10)
array_unshift($_SESSION['password_history'], $password);
$_SESSION['password_history'] = array_slice($_SESSION['password_history'], 0, 10);

// Return response
echo json_encode([
    'success' => true,
    'password' => $password,
    'strength' => $strength,
    'entropy' => calculateEntropy($password),
    'history' => $_SESSION['password_history']
]);