<?php
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Task Tracker') ?> | Task Tracker</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="nav-container">
    <a class="brand" href="<?= is_logged_in() ? 'index.php' : 'login.php' ?>"><span class="brand-mark">T</span><span>Task<span class="brand-light">Tracker</span></span></a>
    <nav>
      <?php if (is_logged_in()): ?>
        <span class="nav-user">Hi, <?= e($_SESSION['user_name'] ?? 'User') ?></span>
        <form method="post" action="logout.php" class="inline-form"><?= csrf_field() ?><button class="nav-logout" type="submit">Log out</button></form>
      <?php else: ?>
        <a href="login.php">Log in</a><a class="nav-register" href="register.php">Create account</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
  <?php if ($flashMessage): ?><div class="alert <?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
