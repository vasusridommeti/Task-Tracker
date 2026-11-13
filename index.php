<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$userId = (int) $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$allowedStatuses = ['Pending', 'In Progress', 'Completed'];

$sql = "SELECT id, title, description, status, created_at FROM tasks WHERE user_id = :user_id";
$params = [':user_id' => $userId];
if ($q !== '') {
    $sql .= " AND title LIKE :q";
    $params[':q'] = '%' . $q . '%';
}
if (in_array($status, $allowedStatuses, true)) {
    $sql .= " AND status = :status";
    $params[':status'] = $status;
}
$sql .= " ORDER BY created_at DESC, id DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$countStmt = db()->prepare(
    "SELECT COUNT(*) AS total,
    SUM(status = 'Pending') AS pending,
    SUM(status = 'In Progress') AS in_progress,
    SUM(status = 'Completed') AS completed
    FROM tasks WHERE user_id = ?"
);
$countStmt->execute([$userId]);
$counts = $countStmt->fetch() ?: ['total'=>0, 'pending'=>0, 'in_progress'=>0, 'completed'=>0];

$pageTitle = 'My Tasks';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row">
  <div>
    <p class="eyebrow">YOUR WORKSPACE</p>
    <h1>My tasks</h1>
    <p class="muted">Keep track of what needs to get done.</p>
  </div>
  <a class="button primary" href="task_form.php">＋ Add task</a>
</section>

<section class="stats-grid" aria-label="Task summary">
  <article class="stat-card"><span>Total tasks</span><strong><?= (int)$counts['total'] ?></strong></article>
  <article class="stat-card"><span>Pending</span><strong><?= (int)$counts['pending'] ?></strong></article>
  <article class="stat-card"><span>In progress</span><strong><?= (int)$counts['in_progress'] ?></strong></article>
  <article class="stat-card"><span>Completed</span><strong><?= (int)$counts['completed'] ?></strong></article>
</section>

<section class="panel">
  <form class="filters" method="get" action="index.php">
    <label class="search-field">
      <span class="sr-only">Search tasks by title</span>
      <input type="search" name="q" placeholder="Search tasks by title..." value="<?= e($q) ?>">
    </label>
    <label>
      <span class="sr-only">Filter by status</span>
      <select name="status">
        <option value="">All statuses</option>
        <?php foreach ($allowedStatuses as $item): ?>
          <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= e($item) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="button secondary" type="submit">Apply</button>
    <?php if ($q !== '' || $status !== ''): ?><a class="text-link" href="index.php">Clear</a><?php endif; ?>
  </form>

  <?php if (!$tasks): ?>
    <div class="empty-state">
      <div class="empty-icon">✓</div>
      <h2><?= ($q !== '' || $status !== '') ? 'No matching tasks' : 'No tasks yet' ?></h2>
      <p class="muted"><?= ($q !== '' || $status !== '') ? 'Try changing your search or filter.' : 'Add your first task to get started.' ?></p>
      <?php if ($q === '' && $status === ''): ?><a class="button primary" href="task_form.php">Create a task</a><?php endif; ?>
    </div>
  <?php else: ?>
    <div class="task-list">
      <?php foreach ($tasks as $task): ?>
        <article class="task-item">
          <div class="task-main">
            <div class="task-title-row">
              <h2><?= e($task['title']) ?></h2>
              <span class="status status-<?= strtolower(str_replace(' ', '-', $task['status'])) ?>"><?= e($task['status']) ?></span>
            </div>
            <?php if ($task['description'] !== null && $task['description'] !== ''): ?>
              <p><?= nl2br(e($task['description'])) ?></p>
            <?php else: ?><p class="muted">No description provided.</p><?php endif; ?>
            <small class="muted">Created <?= e(date('d M Y, h:i A', strtotime($task['created_at']))) ?></small>
          </div>
          <div class="task-actions">
            <a class="button small secondary" href="task_form.php?id=<?= (int)$task['id'] ?>">Edit</a>
            <form method="post" action="task_delete.php" onsubmit="return confirm('Delete this task? This cannot be undone.');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
              <button class="button small danger" type="submit">Delete</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
