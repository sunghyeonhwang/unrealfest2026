<?php
/* Unreal Fest Seoul 2026 — 등록 확인 (myticket.php)
 * 디자인: src/app/pages/MyTicket.tsx. 조회(이메일+연락처) → 정보(QR+상세) → 취소.
 * PHP 7.0 호환.
 */
include_once "../common.php";
require __DIR__ . '/_assets.php';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$row = null; $error = ''; $cancelled = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $em = sql_real_escape_string($email); $ph = sql_real_escape_string($phone);
    if ($email !== '' && $phone !== '') {
        $row = sql_fetch("select * from cb_unreal_2026_event2_apply where apply_user_email = '$em' and apply_user_phone = '$ph' and apply_temp_yn = 'N' order by apply_no desc limit 1");
        if (!$row) { $error = '등록 정보를 찾을 수 없습니다. 이메일과 연락처를 확인해주세요.'; }
        elseif ($action === 'cancel' && $row) {
            // 취소: 상태 0 (환불은 사무국/PG 별도 — v1)
            sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status = 0, refund_date = now() WHERE apply_no = '".intval($row['apply_no'])."'");
            $cancelled = true; $row = null;
        }
    } else {
        $error = '이메일과 연락처를 모두 입력해주세요.';
    }
}
$is_paid = $row && $row['free_yn'] === 'N' && $row['apply_product_code'] !== 'ONLINE';
$qr_jpg = ($row && $is_paid && file_exists(__DIR__."/qrdata/".$row['apply_no'].".jpg")) ? "qrdata/".$row['apply_no'].".jpg" : '';
?>
<!DOCTYPE html>
<html lang="ko" class="dark"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>등록 확인 — Unreal Fest Seoul 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_v('assets/style.css') ?>">
<style>*{word-break:keep-all}</style>
</head>
<body class="bg-[#09090b] text-white" style="font-family:system-ui,'Apple SD Gothic Neo','Noto Sans KR',sans-serif">
<header class="fixed top-0 inset-x-0 z-50 bg-[#09090b]/95 backdrop-blur border-b border-[#27272a]">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <a href="index.html"><img src="white_logo.svg" alt="Unreal Fest Seoul 2026" class="h-7 w-auto"></a>
    <a href="index.html" class="text-sm text-[#a1a1aa] hover:text-white">홈으로</a>
  </div>
</header>

<main class="pt-32 pb-24 min-h-screen">
  <div class="max-w-2xl mx-auto px-6">
  <?php if ($cancelled): ?>
    <div class="text-center">
      <h1 class="text-3xl font-bold mb-3">등록이 취소되었습니다</h1>
      <p class="text-[#a1a1aa] mb-10">유료 등록의 환불은 영업일 기준 최대 5일 이내 처리됩니다.</p>
      <a href="myticket.php" class="clip-btn inline-block bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] px-8 py-4 font-bold">확인</a>
    </div>

  <?php elseif (!$row): ?>
    <!-- 조회 -->
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 확인</h1>
    <p class="text-[#a1a1aa] mb-10">등록 시 사용한 이메일과 전화번호로 등록 정보를 조회할 수 있습니다.</p>
    <form method="post" class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8">
      <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2"><svg class="w-5 h-5 text-[#00C1D5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg> 등록 조회</h2>
      <?php if ($error): ?><p class="text-[#ff8674] text-sm mb-4"><?= e($error) ?></p><?php endif; ?>
      <div class="grid md:grid-cols-2 gap-6">
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">이메일 *</label><input type="email" name="email" placeholder="등록 시 사용한 이메일" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
        <div class="space-y-2"><label class="text-sm font-medium text-[#a1a1aa]">연락처 *</label><input type="tel" name="phone" placeholder="010-1234-5678" class="w-full bg-[#0e0f14] border border-[#27272a] px-4 py-3 text-white placeholder-[#71717a] outline-none focus:border-[#00C1D5] text-sm"></div>
      </div>
      <button type="submit" class="mt-6 w-full bg-[#00C1D5] hover:bg-[#00a8ba] text-[#09090b] py-3 font-bold transition-all">조회하기</button>
    </form>

  <?php else: ?>
    <!-- 정보 -->
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-2 tracking-tight">등록 정보</h1>
    <p class="text-[#a1a1aa] mb-10">등록하신 정보를 확인하고 취소할 수 있습니다.</p>

    <?php if ($is_paid): ?>
    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4 text-center">
      <?php if ($qr_jpg): ?>
        <div class="bg-white p-4 inline-block clip-tr-16 mb-3"><img src="<?= asset_v($qr_jpg) ?>" alt="체크인 QR" class="w-40 h-40"></div>
        <p class="text-xs text-[#71717a]">현장 체크인 시 QR코드를 제시해주세요</p>
      <?php else: ?>
        <p class="text-sm text-[#a1a1aa]">QR 코드는 결제 완료 후 생성됩니다.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-5">참가자 정보</h2>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between"><span class="text-[#71717a]">이름</span><span class="font-bold"><?= e($row['apply_user_name']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">이메일</span><span><?= e($row['apply_user_email']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">연락처</span><span><?= e($row['apply_user_phone']) ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">소속</span><span><?= e($row['apply_user_company']) ?></span></div>
      </div>
    </div>

    <div class="bg-[#0e0f14] border border-[#27272a] p-6 md:p-8 mb-4">
      <h2 class="text-lg font-bold text-white mb-5">등록 정보</h2>
      <div class="space-y-3 text-sm">
        <div class="flex justify-between"><span class="text-[#71717a]">등록 유형</span><span class="font-bold text-[#00C1D5]"><?= $is_paid ? '오프라인' : '온라인 무료' ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">티켓</span><span class="font-bold"><?= e($row['apply_product_name']) ?></span></div>
        <?php if ($is_paid): ?>
        <div class="flex justify-between"><span class="text-[#71717a]">결제 금액</span><span>₩<?= e(number_format((int)$row['apply_product_price'])) ?></span></div>
        <div class="flex justify-between"><span class="text-[#71717a]">트랙</span><span><?= e($row['apply_track']) ?></span></div>
        <?php endif; ?>
        <div class="flex justify-between"><span class="text-[#71717a]">상태</span><span class="font-bold"><?= ((int)$row['apply_pay_status'] === 10) ? '등록 완료' : (((int)$row['apply_pay_status'] === 1) ? '입금 대기' : '확인 필요') ?></span></div>
      </div>
    </div>

    <form method="post" onsubmit="return confirm('정말 등록을 취소하시겠습니까?');">
      <input type="hidden" name="email" value="<?= e($row['apply_user_email']) ?>">
      <input type="hidden" name="phone" value="<?= e($row['apply_user_phone']) ?>">
      <input type="hidden" name="action" value="cancel">
      <div class="flex gap-3 mt-6">
        <a href="myticket.php" class="flex-1 text-center border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-white hover:border-white/20 transition-colors">목록</a>
        <button type="submit" class="flex-1 border border-[#27272a] text-[#a1a1aa] py-3 font-bold hover:text-[#ff8674] hover:border-[rgba(250,70,22,0.3)] transition-all">등록 취소</button>
      </div>
    </form>
  <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/_pf_footer.php'; ?>
</body></html>
