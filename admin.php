<?php $title = 'Admin'; require 'includes/header.php'; require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $a = $_POST['action'] ?? '';
    if ($a === 'verify')  { $pdo->prepare('UPDATE hospitals SET verified = ? WHERE id = ?')->execute([(int)$_POST['v'], (int)$_POST['id']]); flash('Hospital updated.'); }
    if ($a === 'stock')   { $pdo->prepare('INSERT INTO blood_inventory (hospital_id,blood_group,units) VALUES (?,?,?) ON DUPLICATE KEY UPDATE units=VALUES(units)')
                               ->execute([(int)$_POST['hospital_id'], $_POST['blood_group'], max(0, (int)$_POST['units'])]); flash('Stock updated.'); }
    if ($a === 'camp')    { $pdo->prepare('INSERT INTO camps (title,location,camp_date,organizer) VALUES (?,?,?,?)')->execute([trim($_POST['title']), trim($_POST['location']), $_POST['camp_date'], trim($_POST['organizer'])]); flash('Camp added.'); }
    if ($a === 'delcamp') { $pdo->prepare('DELETE FROM camps WHERE id=?')->execute([(int)$_POST['id']]); flash('Camp removed.'); }
    redirect('admin.php');
}
$hs = $pdo->query('SELECT * FROM hospitals ORDER BY verified, name')->fetchAll();
$camps = $pdo->query('SELECT * FROM camps ORDER BY camp_date')->fetchAll();
$users = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$req = (int)$pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status='open'")->fetchColumn();
$per = $pdo->query('SELECT blood_group g, COUNT(*) c FROM donor_profiles GROUP BY blood_group')->fetchAll(PDO::FETCH_KEY_PAIR); $mx = max(1, ...array_map('intval', $per ?: [1]));
?>
<section>
  <p class="eyebrow">Admin panel</p><h2>Manage the platform</h2>
  <div class="stats"><div><b><?= $users ?></b><span class="muted small">users</span></div><div><b><?= array_sum($per) ?></b><span class="muted small">donors</span></div><div><b><?= $req ?></b><span class="muted small">open requests</span></div><div><b><?= count($camps) ?></b><span class="muted small">camps</span></div></div>

  <div class="card" style="margin-top:16px"><h3>Donors by blood group</h3>
    <div style="display:flex;align-items:end;gap:10px;height:140px"><?php foreach (BLOOD_GROUPS as $g): $c = (int)($per[$g] ?? 0); ?>
      <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:end;height:100%;font-size:.75rem"><span class="muted"><?= $c ?></span><i style="display:block;width:100%;max-width:36px;height:<?= max(4, $c / $mx * 100) ?>px;background:var(--red);border-radius:6px 6px 0 0"></i><b><?= e($g) ?></b></div><?php endforeach; ?></div></div>

  <div class="card" style="margin-top:16px"><h3>Hospital approvals</h3><div class="tw"><table><thead><tr><th>Name</th><th>Licence</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($hs as $h): ?><tr><td><strong><?= e($h['name']) ?></strong></td><td><?= e($h['license_no']) ?></td><td><?= $h['verified'] ? '<span class="pill ok">Verified</span>' : '<span class="pill warn">Pending</span>' ?></td>
      <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><input type="hidden" name="id" value="<?= $h['id'] ?>"><input type="hidden" name="v" value="<?= $h['verified'] ? 0 : 1 ?>"><button class="btn small <?= $h['verified'] ? 'ghost' : '' ?>"><?= $h['verified'] ? 'Block' : 'Approve' ?></button></form></td></tr><?php endforeach; ?>
  </tbody></table></div></div>

  <div class="grid g2" style="margin-top:16px">
    <form class="card stack" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="stock"><h3>Update stock</h3>
      <label>Hospital<select name="hospital_id"><?php foreach ($hs as $h) echo '<option value="' . $h['id'] . '">' . e($h['name']) . '</option>'; ?></select></label>
      <div class="grid g2"><label>Blood group<select name="blood_group"><?php foreach (BLOOD_GROUPS as $g) echo '<option>' . e($g) . '</option>'; ?></select></label><label>Units<input type="number" name="units" min="0" required></label></div>
      <button class="btn">Save stock</button></form>
    <form class="card stack" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="camp"><h3>Add a camp</h3>
      <label>Title<input name="title" required></label><label>Location<input name="location" required></label>
      <div class="grid g2"><label>Date<input type="date" name="camp_date" required></label><label>Organiser<input name="organizer" required></label></div>
      <button class="btn">Add camp</button></form>
  </div>
  <div class="card" style="margin-top:16px"><h3>Camps</h3><?php foreach ($camps as $c): ?><div class="item"><div class="grow"><strong><?= e($c['title']) ?></strong><div class="muted small"><?= e($c['location']) ?> · <?= date('j M Y', strtotime($c['camp_date'])) ?></div></div>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delcamp"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn ghost small" data-confirm="Remove this camp?">Remove</button></form></div><?php endforeach; ?></div>
</section>
<?php require 'includes/footer.php'; ?>
