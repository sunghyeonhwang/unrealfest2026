<?php
/* Unreal Fest Seoul 2026 — 초청 등록 완료 (ticket-invite-complete.php) [M2/M4]
 * ?a=<대표 apply_no> & t=<md5(email)=apply_password> 검증 → 이 초청코드 배치의 참석자 QR·조회링크 표시.
 * 언어(KO/EN) = ?lang 또는 코드 sc_lang. QR = qrdata/<apply_no>.jpg. PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php';          // common.php, e(), asset_v(), $UFS_TRACKS(트랙 KO 라벨)
require_once __DIR__ . '/data/i18n_invite.php';

$a = isset($_GET['a']) ? (int)$_GET['a'] : 0;
$t = isset($_GET['t']) ? trim($_GET['t']) : '';
$rep = $a ? sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_no=".$a." LIMIT 1") : null;

$code = ($rep && isset($rep['apply_speaker_code'])) ? $rep['apply_speaker_code'] : '';
$sc = ($code !== '') ? sql_fetch("SELECT sc_inviter, sc_lang FROM cb_unreal_2026_speaker_code WHERE sc_code='".sql_real_escape_string($code)."' LIMIT 1") : null;

// 언어: ?lang 우선, 없으면 코드 sc_lang
$rawLang = isset($_GET['lang']) ? $_GET['lang'] : (($sc && isset($sc['sc_lang'])) ? $sc['sc_lang'] : 'ko');
$lang = ufs_inv_lang($rawLang);
$L = ufs_inv_dict($lang);

if (!$rep || $t === '' || $rep['apply_password'] !== $t) { exit($L['bad']); }

$inviter = ($sc && $sc['sc_inviter'] !== '') ? $sc['sc_inviter'] : '에픽게임즈';

// 이 초청코드로 등록된 활성 참석자 전체(이번 배치)
$att = array();
if ($code !== '') {
  $rs = sql_query("SELECT apply_no, apply_user_name, apply_user_company, apply_product_code, apply_product_name, apply_track
                   FROM cb_unreal_2026_event2_apply
                   WHERE apply_speaker_code='".sql_real_escape_string($code)."' AND apply_pay_status<>0
                   ORDER BY apply_no");
  if ($rs) while ($r = $rs->fetch_assoc()) $att[] = $r;
}
if (!$att) $att[] = $rep; // 방어
?>
<!DOCTYPE html><html lang="<?= e($L['html_lang']) ?>" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow"><title><?= e($L['c_title']) ?> — Unreal Fest Seoul 2026</title>
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>"><style>*{word-break:keep-all}</style></head>
<body class="bg-[#09090b] text-white min-h-screen flex flex-col" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-3xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.php"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.php" class="text-sm text-[#a1a1aa] hover:text-white"><?= e($L['home']) ?></a>
  </div>
</header>
<main class="flex-grow px-6 pt-24 pb-12">
<div class="max-w-2xl mx-auto text-center">
  <svg class="w-16 h-16 mx-auto mb-6 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
  <h1 class="text-2xl md:text-3xl font-bold mb-2"><?= e($L['c_title']) ?></h1>
  <p class="text-[#a1a1aa] mb-8"><?= sprintf($L['c_invited'], e($inviter)) ?></p>

  <div class="space-y-4 text-left mb-8">
    <?php foreach ($att as $r):
      $has = @file_exists(__DIR__.'/qrdata/'.((int)$r['apply_no']).'.jpg');
      $pname = isset($r['apply_product_code']) ? ufs_inv_ticket_label($r['apply_product_code'], $lang) : $r['apply_product_name'];
      $trLabels = array();
      foreach (explode(',', (string)$r['apply_track']) as $tc) {
        $tc = trim($tc); if ($tc === '') continue;
        $day = (strpos($tc, 'DAY2') === 0) ? 2 : 1;
        $ko  = (isset($UFS_TRACKS[$day]) && isset($UFS_TRACKS[$day][$tc])) ? $UFS_TRACKS[$day][$tc] : '';
        $trLabels[] = ufs_inv_track_label($tc, $lang, $ko);
      }
    ?>
    <div class="bg-[#0e0f14] border border-[#27272a] p-5 md:p-6 flex items-center gap-5">
      <?php if ($has): ?>
      <img src="<?= e('qrdata/'.((int)$r['apply_no']).'.jpg') ?>" alt="<?= e($L['c_qr_alt']) ?>" class="w-24 h-24 bg-white p-1 shrink-0">
      <?php endif; ?>
      <div class="min-w-0">
        <div class="font-bold text-white"><?= e($r['apply_user_name']) ?> <span class="text-[#71717a] font-normal text-sm">(<?= e($r['apply_user_company']) ?>)</span></div>
        <div class="text-sm text-[#a1a1aa] mt-1"><?= e($pname) ?></div>
        <?php if ($trLabels): ?><div class="text-xs text-[#71717a] mt-0.5"><?= e($L['c_track']) ?> <?= e(implode(', ', $trLabels)) ?></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="bg-[#111115] border border-[#27272a] p-5 text-left text-sm text-[#a1a1aa] mb-8">
    <p class="font-bold text-[#e4e4e7] mb-1"><?= e($L['c_qr_title']) ?></p>
    <p><?= $L['c_qr_body'] ?></p>
  </div>

  <a href="index.php" class="inline-block px-8 py-3.5 bg-[#00C1D5] hover:bg-[#00a8ba] text-[#090a0f] font-extrabold transition-colors"><?= e($L['home']) ?></a>
</div>
</main>
<footer class="border-t border-[#27272a] py-8">
  <div class="max-w-3xl mx-auto px-6 text-center text-xs text-[#71717a] space-y-1">
    <p>© 2026 Unreal Fest Seoul · 주최 Epic Games · 주관 (주)그리프</p>
    <p>문의 <a href="mailto:info@epiclounge.co.kr" class="hover:text-white">info@epiclounge.co.kr</a> · <a href="tel:02-326-3701" class="hover:text-white">02-326-3701</a></p>
  </div>
</footer>
</body></html>
