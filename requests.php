<?php $title = 'Blood requests'; require 'includes/header.php';
$u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login(); check_csrf(); $rid = (int)($_POST['request_id'] ?? 0);
    if (($_POST['action'] ?? '') === 'accept') {
        $mine = $pdo->prepare('SELECT blood_group FROM donor_profiles WHERE user_id=?'); $mine->execute([$u['id']]); $p = $mine->fetch();
        $r = $pdo->prepare("SELECT blood_group,requester_id FROM blood_requests WHERE id=? AND status='open'"); $r->execute([$rid]); $req = $r->fetch();
        if (!$p) flash('Create a donor profile first.', 'warn');
        elseif (!$req || $req['requester_id'] == $u['id']) flash('You cannot accept this request.', 'err');
        elseif (!in_array($p['blood_group'], COMPATIBLE[$req['blood_group']], true)) flash('Your blood group is not compatible with this request.', 'err');
        else { $pdo->prepare('INSERT IGNORE INTO request_responses (request_id,donor_id) VALUES (?,?)')->execute([$rid, $u['id']]); flash('Thank you! The requester can now see your phone number.'); }
    }
    if (($_POST['action'] ?? '') === 'fulfil') {
        $pdo->prepare("UPDATE blood_requests SET status='fulfilled' WHERE id=? AND requester_id=?")->execute([$rid, $u['id']]); flash('Marked as fulfilled.');
    }
    redirect('requests.php');
}
$reqs = $pdo->query("SELECT r.*,h.name hname,u.name uname FROM blood_requests r JOIN hospitals h ON h.id=r.hospital_id JOIN users u ON u.id=r.requester_id
    WHERE r.status='open' ORDER BY FIELD(r.urgency,'critical','urgent','planned'), r.needed_by")->fetchAll();
$myGroup = null; $accepted = [];
if ($u) {
    $g = $pdo->prepare('SELECT blood_group FROM donor_profiles WHERE user_id=?'); $g->execute([$u['id']]); $myGroup = $g->fetchColumn() ?: null;
    $a = $pdo->prepare('SELECT request_id FROM request_responses WHERE donor_id=?'); $a->execute([$u['id']]); $accepted = $a->fetchAll(PDO::FETCH_COLUMN);
}
?>
<section>
  <div class="head"><div><p class="eyebrow">Requests</p><h2>Who needs blood</h2></div><a class="btn" href="request.php">Post a request</a></div>
  <div class="card" style="padding-top:8px;padding-bottom:8px">
  <?php foreach ($reqs as $r): $mineReq = $u && $r['requester_id'] == $u['id'];
        $match = $myGroup && in_array($myGroup, COMPATIBLE[$r['blood_group']], true); ?>
    <div class="item"><span class="bt solid"><?= e($r['blood_group']) ?></span>
      <div class="grow"><strong><?= (int)$r['units'] ?> unit<?= $r['units'] > 1 ? 's' : '' ?> at <?= e($r['hname']) ?></strong> <span class="pill <?= e($r['urgency']) ?>"><?= e(ucfirst($r['urgency'])) ?></span>
        <div class="muted small">Needed by <?= date('j M, g:i A', strtotime($r['needed_by'])) ?> · posted by <?= e($r['uname']) ?><?= $r['note'] ? ' · ' . e($r['note']) : '' ?></div>
        <?php if ($mineReq) {
            $d = $pdo->prepare('SELECT u.name,u.phone FROM request_responses rr JOIN users u ON u.id=rr.donor_id WHERE rr.request_id=?'); $d->execute([$r['id']]);
            foreach ($d->fetchAll() as $x) echo '<div class="small" style="margin-top:6px"><span class="pill ok">Accepted</span> ' . e($x['name']) . ' · <strong>' . e($x['phone']) . '</strong></div>';
        } ?></div>
      <?php if ($mineReq): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="request_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="fulfil"><button class="btn ghost small">Mark fulfilled</button></form>
      <?php elseif (in_array($r['id'], $accepted)): ?><span class="pill ok">You accepted</span>
      <?php elseif ($match): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="request_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="accept"><button class="btn small" data-confirm="Accept? The requester will see your phone number.">I can donate</button></form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if (!$reqs): ?><div style="padding:32px;text-align:center"><h3>No open requests</h3><p class="muted">Good news. Check back later or register as a donor so you are ready.</p></div><?php endif; ?>
  </div>
  <?php if (!$u): ?><p class="note" style="margin-top:16px"><a href="login.php">Log in</a> to accept a request or post your own.</p><?php endif; ?>
</section>
<?php require 'includes/footer.php'; ?>
