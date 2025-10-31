?>
<?php
session_start();
require_once __DIR__ . '/config_mysqli.php';
require_once __DIR__ . '/csrf.php';

function flash_back_reg($msg){ $_SESSION['flash_error']=$msg; header('Location: register.php'); exit; }

if (function_exists('csrf_validate_request')) {
  if (!csrf_validate_request($_POST)) flash_back_reg('Invalid request.');
} elseif (isset($_POST['csrf_token']) && function_exists('csrf_verify_token')) {
  if (!csrf_verify_token($_POST['csrf_token'])) flash_back_reg('Invalid request.');
}

$display = trim($_POST['display_name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$pass    = $_POST['password'] ?? '';
$pass2   = $_POST['password2'] ?? '';

if ($email === '' || $pass === '' || $pass2 === '') flash_back_reg('Please fill in all required fields.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) flash_back_reg('Invalid email format.');
if ($pass !== $pass2) flash_back_reg('Passwords do not match.');
if (strlen($pass) < 6) flash_back_reg('Password must be at least 6 characters.');

$hash = password_hash($pass, PASSWORD_DEFAULT);

$mysqli = $conn ?? (new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME));
if ($mysqli->connect_errno) flash_back_reg('Database error.');


$chk = $mysqli->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
$chk->bind_param('s',$email);
$chk->execute();
if ($chk->get_result()->fetch_row()) flash_back_reg('Email already registered.');

$ins = $mysqli->prepare('INSERT INTO users (email, display_name, password_hash, created_at) VALUES (?,?,?,NOW())');
$ins->bind_param('sss', $email, $display, $hash);
if (!$ins->execute()) flash_back_reg('Register failed.');

$_SESSION['user_id'] = $ins->insert_id;
$_SESSION['display_name'] = $display ?: $email;
header('Location: dashboard.php');
exit;