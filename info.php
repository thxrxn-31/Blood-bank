<?php $title = 'Eligibility'; require 'includes/header.php'; ?>
<section style="max-width:820px">
  <p class="eyebrow">Eligibility & FAQs</p><h1 style="font-size:clamp(2rem,4vw,3rem)">Can I donate?</h1>
  <div class="grid g2" style="margin:24px 0">
    <div class="card"><h3>You can usually donate if you</h3><ul class="muted"><li>are 18 to 65 years old</li><li>weigh at least 50 kg</li><li>have haemoglobin of 12.5 g/dL or more</li><li>last donated over 90 days ago</li></ul></div>
    <div class="card"><h3>Wait or ask a doctor if you</h3><ul class="muted"><li>have a fever or infection</li><li>had a tattoo in the last 6 months</li><li>are pregnant or nursing</li><li>take blood thinners</li></ul></div>
  </div>
  <h2>Who can give to whom</h2>
  <div class="card tw"><table class="matrix"><thead><tr><th>Patient ↓ Donor →</th><?php foreach (BLOOD_GROUPS as $g) echo '<th>' . e($g) . '</th>'; ?></tr></thead><tbody>
    <?php foreach (BLOOD_GROUPS as $r): ?><tr><th><?= e($r) ?></th><?php foreach (BLOOD_GROUPS as $d) { $y = in_array($d, COMPATIBLE[$r], true); echo '<td class="' . ($y ? 'y' : '') . '">' . ($y ? '✓' : '') . '</td>'; } ?></tr><?php endforeach; ?>
  </tbody></table></div>
  <h2 style="margin-top:32px">FAQs</h2>
  <details open><summary>How often can I donate whole blood?</summary><p>Every 90 days. Your account page shows a countdown.</p></details>
  <details><summary>Does it hurt?</summary><p>A quick pinch. The donation takes about 10 minutes; plan for 45 minutes in total.</p></details>
  <details><summary>Who sees my phone number?</summary><p>Nobody, until you accept a request. You can also pause your availability at any time.</p></details>
  <p class="note" style="margin-top:20px">Final eligibility is decided by a qualified medical professional at the time of donation.</p>
</section>
<?php require 'includes/footer.php'; ?>
