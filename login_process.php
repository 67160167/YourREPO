<?php

session_start();
require_once __DIR__ . '/config_mysqli.php';
require_once __DIR__ . '/csrf.php'; 

function flash_and_back($msg, $to = 'login.php'){
  $_SESSION['flash_error'] = $msg;
  header('Location: '.$to);
  exit;
}


if (function_exists('csrf_validate_request')) {
  if (!csrf_validate_request($_POST)) flash_and_back('Invalid request.');
} elseif (isset($_POST['csrf_token']) && function_exists('csrf_verify_token')) {
  if (!csrf_verify_token($_POST['csrf_token'])) flash_and_back('Invalid request.');
}

$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';
if ($email === '' || $pass === '') flash_and_back('Please fill in all fields.');

$mysqli = $conn ?? (new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME));
if ($mysqli->connect_errno) flash_and_back('Database error.');

$stmt = $mysqli->prepare('SELECT id,password_hash,display_name FROM users WHERE email=? LIMIT 1');
$stmt->bind_param('s',$email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user || !password_verify($pass, $user['password_hash'])) {
  flash_and_back('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
}

// success
$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['display_name'] = $user['display_name'] ?: $email;

$upd = $mysqli->prepare('UPDATE users SET last_login=NOW() WHERE id=?');
$upd->bind_param('i', $_SESSION['user_id']);
$upd->execute();

header('Location: dashboard.php');
exit;