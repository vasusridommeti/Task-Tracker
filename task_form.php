<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
$userId = (int)$_SESSION['user_id'];
$statuses = ['Pending', 'In Progress', 'Completed'];
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEdit = $id !== null && $id !== false;
$task = ['title'=>'', 'description'=>'', 'status'=>'Pending'];
$errors = [];
if ($isEdit) {
    $stmt = db()->prepare('SELECT id, title, description, status FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    $existing = $stmt->fetch();
    if (!$existing) { http_response_code(404); exit('Task not found.'); }
    $task = $existing;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $task['title'] = trim($_POST['title'] ?? '');
    $task['description'] = trim($_POST['description'] ?? '');
    $task['status'] = $_POST['status'] ?? 'Pending';
    if ($task['title'] === '') $errors[] = 'Task title is required.';
    if (mb_strlen($task['title']) > 150) $errors[] = 'Task title must be 150 characters or fewer.';
    if (strlen($task['description']) > 10000) $errors[] = 'Description is too long (maximum 10,000 characters).';
    if (!in_array($task['status'], $statuses, true)) $errors[] = 'Choose a valid task status.';
    if (!$errors) {
        if ($isEdit) {
            $stmt = db()->prepare('UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ? AND user_id = ?');
            $stmt->execute([$task['title'], $task['description'], $task['status'], $id, $userId]);
            flash('success', 'Task updated successfully.');
        } else {
            $stmt = db()->prepare('INSERT INTO tasks (user_id, title, description, status) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $task['title'], $task['description'], $task['status']]);
            flash('success', 'Task added successfully.');
        }
        redirect('index.php');
    }
}
$pageTitle = $isEdit ? 'Edit task' : 'Add task';
require __DIR__ . '/includes/header.php';
?>
<div class="form-page">
  <a class="back-link" href="index.php">← Back to tasks</a>
  <section class="panel form-panel">
    <p class="eyebrow"><?= $isEdit ? 'UPDATE TASK' : 'NEW TASK' ?></p>
    <h1><?= $isEdit ? 'Edit task' : 'Add a task' ?></h1>
    <p class="muted">Enter the details below. You can change them later.</p>
    <?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" class="form-stack">
      <?= csrf_field() ?>
      <label>Title <span class="required">*</span><input type="text" name="title" maxlength="150" value="<?= e($task['title']) ?>" required autofocus></label>
      <label>Description<textarea name="description" rows="5" maxlength="10000" placeholder="Add more details (optional)"><?= e($task['description'] ?? '') ?></textarea></label>
      <label>Status<select name="status"><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $task['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?></select></label>
      <div class="form-actions"><button class="button primary" type="submit"><?= $isEdit ? 'Save changes' : 'Create task' ?></button><a class="button secondary" href="index.php">Cancel</a></div>
    </form>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
