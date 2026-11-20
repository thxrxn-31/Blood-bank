<?php $title = 'Camps'; require 'includes/header.php';
$camps = $pdo->query('SELECT * FROM camps WHERE camp_date >= CURDATE() ORDER BY camp_date')->fetchAll(); ?>
<section>
  <p class="eyebrow">Camps & events</p><h2>Donate with a crowd</h2>
  <div class="grid g2">
  <?php foreach ($camps as $c): $d = new DateTime($c['camp_date']); ?>
    <div class="card item" style="border-bottom:1px solid var(--line)"><div class="date"><b><?= $d->format('j') ?></b><?= $d->format('M') ?></div>
      <div class="grow"><h3><?= e($c['title']) ?></h3><p class="muted small" style="margin:0"><?= e($c['location']) ?><br>Organised by <?= e($c['organizer']) ?></p></div></div>
  <?php endforeach; ?>
  <?php if (!$camps) echo '<p class="muted">No upcoming camps. Check back soon.</p>'; ?>
  </div>
</section>
<?php require 'includes/footer.php'; ?>
