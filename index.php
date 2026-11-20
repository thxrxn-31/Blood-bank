<?php $title = 'Home'; require 'includes/header.php';
$donors = (int)$pdo->query("SELECT COUNT(*) FROM donor_profiles")->fetchColumn();
$units  = (int)$pdo->query("SELECT COALESCE(SUM(units),0) FROM blood_inventory")->fetchColumn();
$open   = (int)$pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status='open'")->fetchColumn();
$hosp   = (int)$pdo->query("SELECT COUNT(*) FROM hospitals WHERE verified=1")->fetchColumn();
$stock  = $pdo->query("SELECT blood_group, SUM(units) u FROM blood_inventory GROUP BY blood_group")->fetchAll(PDO::FETCH_KEY_PAIR);
$camps  = $pdo->query("SELECT * FROM camps WHERE camp_date >= CURDATE() ORDER BY camp_date LIMIT 2")->fetchAll();
?>
<section class="hero">
  <div>
    <p class="eyebrow">Blood donation network</p>
    <h1>Blood, when someone is waiting for it.</h1>
    <p class="lead">Find compatible donors and blood banks near you in seconds, or join as a donor and be the one who answers the call.</p>
    <div class="row"><a class="btn" href="donors.php">Find blood</a><a class="btn ghost" href="register.php">Become a donor</a></div>
  </div>
  <div class="sos">
    <h3>Urgent request?</h3>
    <p>Choose the patient’s blood group. We show every compatible donor right away.</p>
    <div class="pick"><?php foreach (BLOOD_GROUPS as $g): ?><a href="donors.php?bg=<?= urlencode($g) ?>"><?= e($g) ?></a><?php endforeach; ?></div>
    <a class="btn" href="request.php">Post an SOS request</a>
  </div>
</section>

<div class="stats">
  <div><b><?= $donors ?></b><span class="muted small">registered donors</span></div>
  <div><b><?= $units ?></b><span class="muted small">units in stock</span></div>
  <div><b><?= $open ?></b><span class="muted small">open requests</span></div>
  <div><b><?= $hosp ?></b><span class="muted small">verified hospitals</span></div>
</div>

<section>
  <div class="head"><div><p class="eyebrow">Live stock</p><h2>What is on the shelf right now</h2></div><a class="btn ghost small" href="hospitals.php">See by hospital</a></div>
  <div class="grid g4">
    <?php foreach (BLOOD_GROUPS as $g): $u = (int)($stock[$g] ?? 0); ?>
      <div class="card stock"><span class="bt"><?= e($g) ?></span><b><?= $u ?> <span class="muted small" style="font-weight:400">units</span></b>
        <div class="bar-bg <?= $u < 12 ? 'low' : ($u < 30 ? 'mid' : '') ?>"><i style="width:<?= min(100, $u / 45 * 100) ?>%"></i></div></div>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <p class="eyebrow">How it works</p><h2>Three simple steps</h2>
  <div class="grid g3">
    <div class="card"><h3>1. Search</h3><p class="muted">Pick a blood group and city. Compatible groups are included for you.</p></div>
    <div class="card"><h3>2. Request</h3><p class="muted">Post the hospital, units and deadline so donors know exactly what is needed.</p></div>
    <div class="card"><h3>3. Connect</h3><p class="muted">When a donor accepts, their phone number unlocks. Until then it stays private.</p></div>
  </div>
</section>

<?php if ($camps): ?><section>
  <div class="head"><div><p class="eyebrow">Coming up</p><h2>Donation camps</h2></div><a class="btn ghost small" href="camps.php">All camps</a></div>
  <div class="grid g2"><?php foreach ($camps as $c): $d = new DateTime($c['camp_date']); ?>
    <div class="card item" style="border-bottom:1px solid var(--line)"><div class="date"><b><?= $d->format('j') ?></b><?= $d->format('M') ?></div>
      <div class="grow"><h3><?= e($c['title']) ?></h3><p class="muted small" style="margin:0"><?= e($c['location']) ?><br>By <?= e($c['organizer']) ?></p></div></div>
  <?php endforeach; ?></div>
</section><?php endif; ?>
<?php require 'includes/footer.php'; ?>
