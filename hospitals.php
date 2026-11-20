<?php $title = 'Blood stock'; require 'includes/header.php';
$hs = $pdo->query('SELECT * FROM hospitals ORDER BY verified DESC, name')->fetchAll();
$inv = [];
foreach ($pdo->query('SELECT hospital_id,blood_group,units FROM blood_inventory') as $r) $inv[$r['hospital_id']][$r['blood_group']] = (int)$r['units'];
$f = $_GET['bg'] ?? '';
?>
<section>
  <p class="eyebrow">Hospitals & blood banks</p><h2>Blood availability</h2>
  <form class="card filters" method="get">
    <label>Blood group in stock<select name="bg" onchange="this.form.submit()"><option value="">Any</option><?php foreach (BLOOD_GROUPS as $g) echo '<option' . ($f === $g ? ' selected' : '') . '>' . e($g) . '</option>'; ?></select></label>
  </form>
  <div class="grid g2" style="margin-top:16px">
  <?php foreach ($hs as $h): if ($f && empty($inv[$h['id']][$f])) continue; ?>
    <div class="card">
      <div class="row" style="justify-content:space-between"><h3 style="margin:0"><?= e($h['name']) ?></h3><?= $h['verified'] ? '<span class="pill ok">Licence verified</span>' : '<span class="pill warn">Pending</span>' ?></div>
      <p class="muted small"><?= e($h['address']) ?>, <?= e($h['city']) ?> · <?= $h['open_24x7'] ? 'Open 24×7' : 'Day hours' ?><br>Phone: <?= e($h['phone']) ?></p>
      <div class="row" style="gap:6px">
        <?php foreach (BLOOD_GROUPS as $g): $u = $inv[$h['id']][$g] ?? 0; ?><span class="pill <?= $u < 3 ? 'low' : ($u < 12 ? 'warn' : 'ok') ?>"><?= e($g) ?> · <?= $u ?></span><?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <p class="muted small" style="margin-top:12px">Red: fewer than 3 units · Amber: fewer than 12 units · Green: healthy stock.</p>
</section>
<?php require 'includes/footer.php'; ?>
