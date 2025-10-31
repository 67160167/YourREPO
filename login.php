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
  <title>Login</title>
  <style>
    :root{
      --bg:#faf5ef;        /* warm cream */
      --card:#fff7ee;      /* softer warm */
      --ink:#3b2f2f;       /* deep brown */
      --sub:#6b4f4f;       /* muted cocoa */
      --accent:#e07a5f;    /* terracotta */
      --accent-2:#f2cc8f;  /* warm sand */
      --ring:rgba(224,122,95,.35);
    }
    *{box-sizing:border-box;font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif}
    body{margin:0;background:linear-gradient(180deg,var(--bg),#fff);color:var(--ink);}
    .wrap{min-height:100dvh;display:grid;place-items:center;padding:24px}
    .card{background:var(--card);width:100%;max-width:420px;border-radius:18px;padding:28px 24px;box-shadow:0 6px 20px rgba(0,0,0,.08),inset 0 0 0 1px #f3eadf}
    h1{margin:0 0 4px;font-size:28px;letter-spacing:.2px}
    p.sub{margin:.25rem 0 1rem;color:var(--sub);font-size:14px}
    label{display:block;font-size:13px;margin:14px 0 6px;color:var(--sub)}
    input[type=email],input[type=password]{
      width:100%;padding:12px 14px;border-radius:12px;border:1px solid #eadfd3;background:white;color:var(--ink);
      outline:none;box-shadow:none;transition:border .2s, box-shadow .2s
    }
    input:focus{border-color:var(--accent);box-shadow:0 0 0 4px var(--ring)}
    .row{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-top:14px}
    .btn{appearance:none;border:none;cursor:pointer;border-radius:12px;padding:12px 14px;font-weight:600}
    .btn.primary{background:var(--accent);color:#fff}
    .btn.ghost{background:transparent;color:var(--accent);border:1px solid var(--accent);}
    .muted{font-size:13px;color:var(--sub);}
    .sep{height:1px;background:#efdfcf;margin:18px 0}
    a{color:var(--accent);text-decoration:none}
    a:hover{text-decoration:underline}
  </style>
</head>
<body>
  <div class="wrap">
    <form class="card" action="login_process.php" method="post" autocomplete="on">
      <h1>Welcome back</h1>
      <p class="sub">ลงชื่อเข้าใช้เพื่อไปยัง Dashboard</p>

      <?php if (!empty($_SESSION['flash_error'])): ?>
        <div style="background:#ffe8e3;color:#7d2d2d;border:1px solid #ffcabf;padding:10px 12px;border-radius:10px;font-size:13px;margin:10px 0;">
          <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
      <?php endif; ?>

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autofocus />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <?php 
      if (function_exists('csrf_token_field')) { echo csrf_token_field(); }
      else if (function_exists('csrf_token')) { echo '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token()).'" />'; }
      ?>

      <div class="row">
        <button class="btn primary" type="submit">Login</button>
        <a class="btn ghost" href="register.php">Register</a>
      </div>

      <div class="sep"></div>
      <div class="muted">ลืมรหัสผ่าน? ติดต่อผู้ดูแลระบบ</div>
    </form>
  </div>
</body>
</html>
