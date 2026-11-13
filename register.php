<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) redirect('index.php');
$errors = [];
$name = ''; $email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '' || mb_strlen($name) > 80) $errors[] = 'Name is required and must be 80 characters or fewer.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address (maximum 190 characters).';
    if (strlen($password) < 8) $errors[] = 'Password must contain at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'An account with this email already exists.';
    }
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)db()->lastInsertId();
        $_SESSION['user_name'] = $name;
        flash('success', 'Your account has been created.');
        redirect('index.php');
    }
}
$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <section class="auth-card">
    <p class="eyebrow">GET STARTED</p><h1>Create account</h1>
    <p class="muted">Create an account to keep your tasks private.</p>
    <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" class="form-stack">
      <?= csrf_field() ?>
      <label>Your name<input type="text" name="name" maxlength="80" value="<?= e($name) ?>" required autocomplete="name"></label>
      <label>Email address<input type="email" name="email" maxlength="190" value="<?= e($email) ?>" required autocomplete="email"></label>
      <label>Password <span class="hint">(at least 8 characters)</span><input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
      <label>Confirm password<input type="password" name="confirm_password" minlength="8" required autocomplete="new-password"></label>
      <button class="button primary full" type="submit">Create account</button>
    </form>
    <p class="auth-switch">Already registered? <a href="login.php">Log in</a></p>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
