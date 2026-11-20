<?php $title = 'Become a donor'; require 'includes/header.php';
if (is_logged_in()) redirect('dashboard.php');
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? ''); $email = strtolower(trim($_POST['email'] ?? '')); $pass = $_POST['password'] ?? '';
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? ''); $bg = $_POST['blood_group'] ?? ''; $age = (int)($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? ''; $city = trim($_POST['city'] ?? ''); $last = $_POST['last_donation'] ?: null;
    if ($name === '') $err[] = 'Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Enter a valid email address.';
    if (strlen($pass) < 8) $err[] = 'Password must be at least 8 characters.';
    if (strlen($phone) < 10) $err[] = 'Enter a 10-digit mobile number.';
    if (!in_array($bg, BLOOD_GROUPS, true)) $err[] = 'Choose your blood group.';
    if ($age < 18 || $age > 65) $err[] = 'Donors must be between 18 and 65 years old.';
    if (!in_array($gender, ['M','F','O'], true)) $err[] = 'Choose a gender.';
    if ($city === '') $err[] = 'Enter your city.';
    if (!$err) {
        $q = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $q->execute([$email]);
        if ($q->fetch()) $err[] = 'That email is already registered. Try logging in.';
    }
    if (!$err) {
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO users (name,email,password_hash,phone) VALUES (?,?,?,?)')->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $phone]);
        $id = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO donor_profiles (user_id,blood_group,age,gender,city,last_donation_date) VALUES (?,?,?,?,?,?)')->execute([$id, $bg, $age, $gender, $city, $last]);
        $pdo->commit();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $id, 'name' => $name, 'role' => 'user'];
        flash('Welcome, ' . $name . '! Your donor profile is live.');
        redirect('dashboard.php');
    }
}
?>
<div class="auth"><div class="card lift">
  <h2>Become a donor</h2><p class="muted">Takes about two minutes. Your phone number stays hidden until you accept a request.</p>
  <?php foreach ($err as $x) echo '<div class="alert err">' . e($x) . '</div>'; ?>
  <form class="stack" method="post"><?= csrf_field() ?>
    <label>Full name<input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
    <label>Email<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Password<input type="password" name="password" minlength="8" required><span class="muted small" style="font-weight:400">At least 8 characters. Stored hashed.</span></label>
    <label>Mobile number<input name="phone" inputmode="tel" required value="<?= e($_POST['phone'] ?? '') ?>"></label>
    <div class="grid g2">
      <label>Blood group<select name="blood_group" required><option value="">Select</option><?php foreach (BLOOD_GROUPS as $g) echo '<option' . (($_POST['blood_group'] ?? '') === $g ? ' selected' : '') . '>' . e($g) . '</option>'; ?></select></label>
      <label>Age<input type="number" name="age" min="18" max="65" required value="<?= e($_POST['age'] ?? '') ?>"></label>
      <label>Gender<select name="gender" required><option value="">Select</option><option value="F">Female</option><option value="M">Male</option><option value="O">Other</option></select></label>
      <label>City<input name="city" required value="<?= e($_POST['city'] ?? '') ?>"></label>
    </div>
    <label>Last donation date (optional)<input type="date" name="last_donation" max="<?= date('Y-m-d') ?>"></label>
    <p class="note">Final eligibility is decided by a medical professional at the time of donation.</p>
    <button class="btn">Create my account</button>
  </form>
  <p class="small muted" style="margin:16px 0 0;text-align:center">Already registered? <a href="login.php">Log in</a></p>
</div></div>
<?php require 'includes/footer.php'; ?>
