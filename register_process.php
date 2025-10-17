<?php
require __DIR__ . '/config_mysqli.php';
require __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: register.php'); exit;
}

if (!csrf_check($_POST['csrf'] ?? '')) {
  $_SESSION['flash'] = 'Invalid request. Please try again.';
  header('Location: register.php'); exit;
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

$errors = [];

if ($name === '' || mb_strlen($name) > 100) {
  $errors[] = 'Please enter a valid name (max 100 chars).';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
  $errors[] = 'Please enter a valid email.';
}
if (strlen($password) < 8) {
  $errors[] = 'Password must be at least 8 characters.';
}
if ($password !== $confirm) {
  $errors[] = 'Password confirmation does not match.';
}

if ($errors) {
  $_SESSION['flash'] = implode(' ', $errors);
  header('Location: register.php'); exit;
}

try {
  $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  if (!$stmt) { throw new Exception('Prepare failed'); }
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    $stmt->close();
    $_SESSION['flash'] = 'This email is already registered.';
    header('Location: register.php'); exit;
  }
  $stmt->close();

  $hash = password_hash($password, PASSWORD_DEFAULT);


  $stmt2 = $mysqli->prepare('INSERT INTO users (email, display_name, password_hash) VALUES (?, ?, ?)');
  if (!$stmt2) { throw new Exception('Prepare failed'); }
  $stmt2->bind_param('sss', $email, $name, $hash);
  $ok = $stmt2->execute();
  $newUserId = $stmt2->insert_id;
  $stmt2->close();

  if (!$ok) {
    $_SESSION['flash'] = 'Something went wrong. Please try again.';
    header('Location: register.php'); exit;
  }

  $_SESSION['flash'] = 'Registration successful. Please sign in.';
  header('Location: login.php'); exit;
  
} catch (Throwable $e) {
  $_SESSION['flash'] = 'Server error. Please try again.';
  header('Location: register.php'); exit;
}