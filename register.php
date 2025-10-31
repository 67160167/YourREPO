<?php

session_start();
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register</title>
  <style>
    :root{--bg:#faf5ef;--card:#fff7ee;--ink:#3b2f2f;--sub:#6b4f4f;--accent:#e07a5f;--ring:rgba(224,122,95,.35)}
    *{box-sizing:border-box;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif}
    body{margin:0;background:linear-gradient(180deg,var(--bg),#fff);color:var(--ink)}
    .wrap{min-height:100dvh;display:grid;place-items:center;padding:24px}
    .card{background:var(--card);width:100%;max-width:520px;border-radius:18px;padding:28px 24px;box-shadow:0 6px 20px rgba(0,0,0,.08),inset 0 0 0 1px #f3eadf}
    h1{margin:0 0 4px;font-size:28px}
    p.sub{margin:.25rem 0 1rem;color:var(--sub);font-size:14px}
    label{display:block;font-size:13px;margin:14px 0 6px;color:var(--sub)}
    input{width:100%;padding:12px 14px;border-radius:12px;border:1px solid #eadfd3;background:white;color:var(--ink)}
    input:focus{border-color:var(--accent);outline:none;box-shadow:0 0 0 4px var(--ring)}
    .row{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-top:16px}
    .btn{appearance:none;border:none;border-radius:12px;padding:12px 14px;font-weight:600;cursor:pointer}
    .btn.primary{background:var(--accent);color:#fff}
    .btn.ghost{background:transparent;color:var(--accent);border:1px solid var(--accent)}
    a{color:var(--accent);text-decoration:none}
    a:hover{text-decoration:underline}
    .error{background:#ffe8e3;color:#7d2d2d;border:1px solid #ffcabf;padding:10px 12px;border-radius:10px;font-size:13px;margin:10px 0}
  </style>
</head>
<body>
  <div class="wrap">
    <form class="card" action="register_process.php" method="post" autocomplete="on">
      <h1>Create account</h1>
      <p class="sub">สมัครสมาชิกใหม่</p>

      <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="error"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
      <?php endif; ?>

      <label for="display_name">Display name (optional)</label>
      <input type="text" id="display_name" name="display_name" maxlength="190" />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" minlength="6" required />

      <label for="password2">Confirm password</label>
      <input type="password" id="password2" name="password2" minlength="6" required />

      <?php 
      if (function_exists('csrf_token_field')) { echo csrf_token_field(); }
      else if (function_exists('csrf_token')) { echo '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token()).'" />'; }
      ?>

      <div class="row">
        <button class="btn primary" type="submit">Register</button>
        <a class="btn ghost" href="login.php">Back to login</a>
      </div>
    </form>
  </div>
</body>
</html>