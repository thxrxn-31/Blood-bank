<?php $title = 'Find donors'; require 'includes/header.php';
$bg = $_GET['bg'] ?? ''; $city = trim($_GET['city'] ?? ''); $avail = isset($_GET['avail']); $gap = isset($_GET['gap']);
$sql = "SELECT u.id,u.name,d.blood_group,d.city,d.gender,d.last_donation_date,d.is_available
        FROM donor_profiles d JOIN users u ON u.id=d.user_id WHERE 1=1";
$args = [];
if (in_array($bg, BLOOD_GROUPS, true)) {                     // include every compatible donor group
    $ok = COMPATIBLE[$bg];
    $sql .= ' AND d.blood_group IN (' . implode(',', array_fill(0, count($ok), '?')) . ')'; array_push($args, ...$ok);
} else $bg = '';
if ($city !== '') { $sql .= ' AND d.city LIKE ?'; $args[] = "%$city%"; }
if ($avail) $sql .= ' AND d.is_available = 1';
if ($gap)   $sql .= ' AND (d.last_donation_date IS NULL OR d.last_donation_date <= DATE_SUB(CURDATE(), INTERVAL 90 DAY))';
$sql .= ' ORDER BY d.is_available DESC, d.blood_group, u.name';
$q = $pdo->prepare($sql); $q->execute($args); $rows = $q->fetchAll();
?>
<section>
  <p class="eyebrow">Search</p><h2>Find donors</h2>
  <form class="card filters" method="get">
    <label>Patient blood group<select name="bg"><option value="">Any</option><?php foreach (BLOOD_GROUPS as $g) echo '<option' . ($bg === $g ? ' selected' : '') . '>' . e($g) . '</option>'; ?></select></label>
    <label>City<input name="city" value="<?= e($city) ?>" placeholder="e.g. Bengaluru"></label>
    <label style="flex-direction:row;align-items:center;gap:8px"><input type="checkbox" name="avail" <?= $avail ? 'checked' : '' ?>> Available now</label>
    <label style="flex-direction:row;align-items:center;gap:8px"><input type="checkbox" name="gap" <?= $gap ? 'checked' : '' ?>> Eligible (90+ days)</label>
    <button class="btn">Search</button>
  </form>
  <?php if ($bg): ?><p class="muted small" style="margin-top:12px">Showing <?= e(implode(', ', COMPATIBLE[$bg])) ?> donors for a <?= e($bg) ?> patient.</p><?php endif; ?>
  <div class="card" style="margin-top:16px;padding-top:8px;padding-bottom:8px">
    <p class="muted small" style="margin:12px 0 0"><?= count($rows) ?> donor<?= count($rows) === 1 ? '' : 's' ?> found</p>
    <?php foreach ($rows as $r): $left = days_left($r['last_donation_date']); $can = $r['is_available'] && $left === 0; ?>
      <div class="item"><span class="bt solid"><?= e($r['blood_group']) ?></span>
        <div class="grow"><strong><?= e($r['name']) ?></strong>
          <div class="muted small"><?= e($r['city']) ?> · <?= $r['last_donation_date'] ? 'last donated ' . date('j M Y', strtotime($r['last_donation_date'])) : 'new donor' ?> · phone hidden until accepted</div></div>
        <?= $can ? '<span class="pill ok">Available</span>' : '<span class="pill warn">' . ($left ? "Eligible in $left days" : 'Unavailable') . '</span>' ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div style="padding:32px;text-align:center"><h3>No donors match</h3><p class="muted">Try another city or switch off a filter, or post a request so donors can come to you.</p><a class="btn" href="request.php">Post a request</a></div><?php endif; ?>
  </div>
  <p class="note" style="margin-top:16px">Want a donor’s help? <a href="request.php">Post a blood request</a>. Matching donors accept it, and only then does their number unlock for you.</p>
</section>
<?php require 'includes/footer.php'; ?>
