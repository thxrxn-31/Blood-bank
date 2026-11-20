<?php $title = 'My account'; require 'includes/header.php'; require_login();
$uid = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (($_POST['action'] ?? '') === 'toggle') { $pdo->prepare('UPDATE donor_profiles SET is_available = 1 - is_available WHERE user_id=?')->execute([$uid]); flash('Availability updated.'); }
    if (($_POST['action'] ?? '') === 'profile') {
        $pdo->prepare('UPDATE users SET phone=? WHERE id=?')->execute([preg_replace('/\D/', '', $_POST['phone'] ?? ''), $uid]);
        $pdo->prepare('UPDATE donor_profiles SET city=?, last_donation_date=? WHERE user_id=?')->execute([trim($_POST['city'] ?? ''), $_POST['last_donation'] ?: null, $uid]);
        flash('Profile saved.');
    }
    redirect('dashboard.php');
}
$q = $pdo->prepare('SELECT u.name,u.email,u.phone,d.* FROM users u LEFT JOIN donor_profiles d ON d.user_id=u.id WHERE u.id=?'); $q->execute([$uid]); $me = $q->fetch();
$don = $pdo->prepare('SELECT d.*,h.name hname FROM donations d JOIN hospitals h ON h.id=d.hospital_id WHERE donor_id=? ORDER BY donated_on DESC'); $don->execute([$uid]); $dons = $don->fetchAll();
$left = days_left($me['last_donation_date'] ?? null); $n = next_eligible($me['last_donation_date'] ?? null);
$match = [];
if ($me['blood_group']) {
    $m = $pdo->prepare("SELECT r.*,h.name hname FROM blood_requests r JOIN hospitals h ON h.id=r.hospital_id WHERE r.status='open' AND r.requester_id<>? ORDER BY r.needed_by LIMIT 5"); $m->execute([$uid]);
    foreach ($m->fetchAll() as $r) if (in_array($me['blood_group'], COMPATIBLE[$r['blood_group']], true)) $match[] = $r;
}
?>
<section>
  <p class="eyebrow">My account</p><h2>Hello, <?= e(explode(' ', $me['name'])[0]) ?></h2>
  <?php if (!$me['blood_group']): ?><div class="alert warn">This account has no donor profile. <a href="register.php">Register as a donor</a> to appear in searches.</div><?php else: ?>
  <div class="grid g3">
    <div class="card"><span class="muted small">Your blood group</span><div style="margin-top:8px"><span class="bt solid"><?= e($me['blood_group']) ?></span></div></div>
    <div class="card"><span class="muted small">Next eligible date</span><div style="font-size:1.5rem;font-weight:800;margin-top:6px"><?= $left === 0 ? 'Eligible now' : e($n->format('j M Y')) ?></div><span class="muted small"><?= $left === 0 ? 'Thank you for being ready' : "$left days to go (90-day gap)" ?></span></div>
    <div class="card"><span class="muted small">Availability</span>
      <form method="post" style="margin-top:8px"><?= csrf_field() ?><input type="hidden" name="action" value="toggle">
        <div class="row"><?= $me['is_available'] ? '<span class="pill ok">Available</span>' : '<span class="pill warn">Hidden</span>' ?><button class="btn ghost small"><?= $me['is_available'] ? 'Pause' : 'Resume' ?></button></div></form></div>
  </div>
  <div class="grid g2" style="margin-top:16px">
    <div class="card"><h3>Requests you can help with</h3>
      <?php foreach ($match as $r): ?><div class="item"><span class="bt"><?= e($r['blood_group']) ?></span><div class="grow"><strong><?= (int)$r['units'] ?> unit(s) · <?= e($r['hname']) ?></strong><div class="muted small">By <?= date('j M, g:i A', strtotime($r['needed_by'])) ?></div></div><a class="btn small ghost" href="requests.php">View</a></div><?php endforeach; ?>
      <?php if (!$match) echo '<p class="muted">Nothing right now. We will show matches here.</p>'; ?></div>
    <form class="card stack" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="profile"><h3>Edit details</h3>
      <label>Mobile<input name="phone" value="<?= e($me['phone']) ?>"></label>
      <label>City<input name="city" value="<?= e($me['city']) ?>"></label>
      <label>Last donation<input type="date" name="last_donation" value="<?= e($me['last_donation_date']) ?>"></label>
      <button class="btn">Save changes</button></form>
  </div>
  <div class="card" style="margin-top:16px"><h3>My donations</h3>
    <?php foreach ($dons as $d): ?><div class="item"><span class="bt"><?= e($me['blood_group']) ?></span><div class="grow"><strong><?= date('j M Y', strtotime($d['donated_on'])) ?></strong><div class="muted small"><?= e($d['hname']) ?> · <?= (int)$d['units'] ?> unit</div></div></div><?php endforeach; ?>
    <?php if (!$dons) echo '<p class="muted">Your donations will be listed here after a hospital records them.</p>'; ?></div>
  <?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
