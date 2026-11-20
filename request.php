<?php $title = 'Post a request'; require 'includes/header.php'; require_login();
$hs = $pdo->query('SELECT id,name FROM hospitals WHERE verified=1 ORDER BY name')->fetchAll();
$err = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $bg = $_POST['blood_group'] ?? ''; $units = (int)($_POST['units'] ?? 0); $hid = (int)($_POST['hospital_id'] ?? 0);
    $urg = $_POST['urgency'] ?? 'planned'; $by = str_replace('T', ' ', $_POST['needed_by'] ?? ''); $note = trim($_POST['note'] ?? '');
    if (!in_array($bg, BLOOD_GROUPS, true)) $err[] = 'Choose the patient’s blood group.';
    if ($units < 1 || $units > 10) $err[] = 'Units must be between 1 and 10.';
    if (!in_array($urg, ['planned','urgent','critical'], true)) $err[] = 'Choose an urgency.';
    if (!strtotime($by)) $err[] = 'Choose when the blood is needed.';
    $ok = $pdo->prepare('SELECT 1 FROM hospitals WHERE id=? AND verified=1'); $ok->execute([$hid]);
    if (!$ok->fetch()) $err[] = 'Choose a verified hospital.';
    if (!$err) {
        $pdo->prepare('INSERT INTO blood_requests (requester_id,blood_group,units,hospital_id,urgency,needed_by,note) VALUES (?,?,?,?,?,?,?)')
            ->execute([current_user()['id'], $bg, $units, $hid, $urg, date('Y-m-d H:i:s', strtotime($by)), mb_substr($note, 0, 255) ?: null]);
        flash('Request posted. Matching donors can now accept it.');
        redirect('requests.php');
    }
}
?>
<div class="auth"><div class="card lift">
  <h2>Post a blood request</h2><p class="muted">Matching donors will see this and can accept it.</p>
  <?php foreach ($err as $x) echo '<div class="alert err">' . e($x) . '</div>'; ?>
  <form class="stack" method="post"><?= csrf_field() ?>
    <div class="grid g2">
      <label>Patient blood group<select name="blood_group" required><option value="">Select</option><?php foreach (BLOOD_GROUPS as $g) echo '<option' . (($_POST['blood_group'] ?? $_GET['bg'] ?? '') === $g ? ' selected' : '') . '>' . e($g) . '</option>'; ?></select></label>
      <label>Units needed<input type="number" name="units" min="1" max="10" value="<?= e($_POST['units'] ?? 1) ?>" required></label>
    </div>
    <label>Hospital<select name="hospital_id" required><?php foreach ($hs as $h) echo '<option value="' . $h['id'] . '">' . e($h['name']) . '</option>'; ?></select></label>
    <div class="grid g2">
      <label>Urgency<select name="urgency"><option value="planned">Planned</option><option value="urgent">Urgent</option><option value="critical">Critical (SOS)</option></select></label>
      <label>Needed by<input type="datetime-local" name="needed_by" required></label>
    </div>
    <label>Note (optional)<input name="note" maxlength="255" placeholder="e.g. Surgery on Friday"></label>
    <button class="btn">Post request</button>
  </form>
</div></div>
<?php require 'includes/footer.php'; ?>
