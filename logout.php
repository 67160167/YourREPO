
<?php
session_start();
require_once __DIR__ . '/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (function_exists('csrf_validate_request') && !csrf_validate_request($_POST)) {
    header('Location: dashboard.php'); exit;
  }
  if (isset($_POST['csrf_token']) && function_exists('csrf_verify_token') && !csrf_verify_token($_POST['csrf_token'])) {
    header('Location: dashboard.php'); exit;
  }
  session_unset();
  session_destroy();
}
header('Location: login.php');
exit;