<?php $title = 'Log in'; require 'includes/header.php';
if (is_logged_in()) redirect('dashboard.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $q = $pdo->prepare('SELECT id,name,role,password_hash FROM users WHERE email = ?');
    $q->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $row = $q->fetch();
    if ($row && password_verify($_POST['password'] ?? '', $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$row['id'], 'name' => $row['name'], 'role' => $row['role']];
        redirect($row['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
    }
    $err = 'Email or password is incorrect.';
}
?>
<div class="auth"><div class="card lift">
  <h2>Welcome back</h2><p class="muted">Log in to see requests that match your blood group.</p>
  <?php if ($err) echo '<div class="alert err">' . e($err) . '</div>'; ?>
  <form class="stack" method="post"><?= csrf_field() ?>
    <label>Email<input type="email" name="email" required autofocus></label>
    <label>Password<input type="password" name="password" required></label>
    <button class="btn">Log in</button>
  </form>
  <p class="small muted" style="margin:16px 0 0;text-align:center">New here? <a href="register.php">Become a donor</a></p>
</div></div>
<?php require 'includes/footer.php'; ?>
