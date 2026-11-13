<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) redirect('index.php');
$errors = [];
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if ($password === '') $errors[] = 'Password is required.';
    if (!$errors) {
        $stmt = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];
            flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect('index.php');
        }
        $errors[] = 'Email or password is incorrect.';
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <section class="auth-card">
    <p class="eyebrow">WELCOME BACK</p><h1>Log in</h1>
    <p class="muted">Sign in to view and manage your tasks.</p>
    <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" class="form-stack">
      <?= csrf_field() ?>
      <label>Email address<input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email"></label>
      <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
      <button class="button primary full" type="submit">Log in</button>
    </form>
    <p class="auth-switch">Don't have an account? <a href="register.php">Create one</a></p>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
