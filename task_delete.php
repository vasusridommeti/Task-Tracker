<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { flash('error', 'Invalid task.'); redirect('index.php'); }
$stmt = db()->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, (int)$_SESSION['user_id']]);
flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Task deleted successfully.' : 'Task not found or you do not have permission.');
redirect('index.php');
