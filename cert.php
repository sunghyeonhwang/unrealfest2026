<?php
/* Unreal Fest Seoul 2026 — 참가확인증 (cert.php)
 * myticket.php에서 이메일+전화로 POST 재검증 → 오프라인(유료) 등록자에게 인쇄/PDF용 참가확인증 표시.
 * 발급 가능일: 1일권(8/21)=8/21부터, 그 외(양일권·8/20권)=8/20부터. 미리보기 ?certpv=ufscert2026.
 * 인쇄=window.print()(브라우저 PDF 저장). 직인=quote_seal.png base64 임베드. noindex. PHP 7.0.
 */
include_once __DIR__ . '/../common.php';
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

$pv = (isset($_GET['certpv']) && $_GET['certpv']==='ufscert2026');
$email = isset($_POST['email']) ? trim($_POST['email']) : (isset($_GET['email']) ? trim($_GET['email']) : '');
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : (isset($_GET['phone']) ? trim($_GET['phone']) : '');

$row = null;
if ($email !== '' && $phone !== '') {
    $em = sql_real_escape_string($email);
    $phd = sql_real_escape_string(preg_replace('/[^0-9]/', '', $phone));
    $row = sql_fetch("SELECT * FROM cb_unreal_2026_event2_apply WHERE apply_user_email='$em' AND REPLACE(REPLACE(apply_user_phone,'-',''),' ','')='$phd' AND apply_temp_yn='N' AND apply_pay_status<>0 ORDER BY apply_no DESC LIMIT 1");
}

function cert_fail($msg){
    echo '<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>참가확인증</title>'
       . '<style>body{font-family:system-ui,"Apple SD Gothic Neo",sans-serif;background:#f5f5f7;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center;color:#333}'
       . '.b{background:#fff;border:1px solid #e5e5ea;border-radius:12px;padding:36px 40px;max-width:420px;text-align:center}a{color:#00849a;font-weight:700;text-decoration:none}</style></head>'
       . '<body><div class="b"><h2 style="margin:0 0 10px">참가확인증</h2><p style="color:#666;line-height:1.7">'.$msg.'</p>'
       . '<p style="margin-top:18px"><a href="myticket.php">← 등록 확인으로</a></p></div></body></html>';
    exit;
}

$is_offline = ($row && $row['free_yn']==='N' && $row['apply_product_code']!=='ONLINE');
if (!$row)          cert_fail('등록 정보를 찾을 수 없습니다. 등록에 사용하신 이메일과 전화번호로 다시 시도해 주세요.');
if (!$is_offline)   cert_fail('참가확인증은 오프라인(현장 참가) 등록자에게만 발급됩니다.');

// 발급 가능일: 8/21권=8/21부터, 그 외=8/20부터
$avail = ($row['apply_product_code']==='NORMAL_21') ? '2026-08-21' : '2026-08-20';
if (!$pv && date('Y-m-d') < $avail) {
    $ad = ($avail==='2026-08-21') ? '8월 21일' : '8월 20일';
    cert_fail('참가확인증은 <b>'.$ad.'</b>부터 발급/다운로드하실 수 있습니다.');
}

// 참가 기간 문구
$code = $row['apply_product_code'];
if ($code==='NORMAL_ALL')      $period = '2026년 8월 20일(목) ~ 8월 21일(금)';
else if ($code==='NORMAL_20')  $period = '2026년 8월 20일(목)';
else if ($code==='NORMAL_21')  $period = '2026년 8월 21일(금)';
else                           $period = '2026년 8월 20일(목) ~ 8월 21일(금)';

$name    = $row['apply_user_name'];
$company = trim((string)$row['apply_user_company']);
$depart  = trim((string)$row['apply_user_depart']);
$affil   = $company . ($depart!=='' ? ' / '.$depart : '');
$product = $row['apply_product_name'];
$today   = date('Y년 n월 j일');
$certno  = 'UFS2026-'.str_pad((string)(int)$row['apply_no'], 6, '0', STR_PAD_LEFT);

// 직인 base64 임베드
$SEAL = '';
$seal_path = __DIR__.'/quote_seal.png';
if (is_file($seal_path)) { $bin=@file_get_contents($seal_path); if ($bin!==false && $bin!=='') $SEAL='data:image/png;base64,'.base64_encode($bin); }
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>참가확인증 — Unreal Fest Seoul 2026</title>
<link href="https://fonts.googleapis.com/css2?family=Pretendard:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Pretendard','Apple SD Gothic Neo','Malgun Gothic',sans-serif;background:#e9eaee;color:#1a1a22;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.toolbar{position:sticky;top:0;z-index:10;display:flex;gap:10px;justify-content:center;padding:16px;background:rgba(255,255,255,.85);backdrop-filter:blur(8px);border-bottom:1px solid #dcdce2}
.toolbar button,.toolbar a{font:inherit;font-weight:700;font-size:14px;border:1px solid #c9c9d2;background:#fff;color:#333;border-radius:8px;padding:10px 18px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:7px}
.toolbar button.pri{background:#00C1D5;border-color:#00C1D5;color:#062a2f}
.page{display:flex;justify-content:center;padding:28px 16px 60px}
.cert{width:210mm;max-width:100%;min-height:297mm;background:#fff;padding:22mm 20mm;position:relative;box-shadow:0 24px 60px -20px rgba(0,0,0,.35)}
.cert-top{display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #00C1D5;padding-bottom:14px}
.cert-top .brand{font-weight:800;font-size:15px;letter-spacing:.02em;color:#0a2e34}
.cert-top .brand b{color:#00849a}
.cert-no{font-size:12px;color:#8a8a95}
.cert-title{text-align:center;margin:56px 0 10px;font-size:40px;font-weight:800;letter-spacing:16px;color:#111;padding-left:16px}
.cert-sub{text-align:center;color:#8a8a95;font-size:13px;letter-spacing:.28em;margin-bottom:52px}
.cert-tbl{width:100%;border-collapse:collapse;margin:0 auto 44px;font-size:15px}
.cert-tbl th,.cert-tbl td{border:1px solid #dcdce2;padding:15px 18px;text-align:left}
.cert-tbl th{width:130px;background:#f6f7f9;color:#555;font-weight:700}
.cert-tbl td{font-weight:600}
.cert-body{text-align:center;font-size:16.5px;line-height:2;color:#222;margin:0 0 60px}
.cert-body .em{font-weight:800}
.cert-date{text-align:center;font-size:16px;font-weight:700;margin-bottom:34px;letter-spacing:.02em}
.cert-issuer{text-align:center;font-size:17px;font-weight:800;position:relative;display:inline-block;left:50%;transform:translateX(-50%)}
.cert-issuer .seal{position:absolute;right:-64px;top:50%;transform:translateY(-50%);width:58px;height:58px;opacity:.92}
.cert-foot{position:absolute;left:20mm;right:20mm;bottom:16mm;text-align:center;font-size:11px;color:#a0a0aa;border-top:1px solid #eee;padding-top:10px}
@media print{
  body{background:#fff}
  .toolbar{display:none}
  .page{padding:0}
  .cert{box-shadow:none;width:auto;min-height:auto;padding:18mm 18mm}
  @page{size:A4 portrait;margin:0}
}
</style>
</head>
<body>
<div class="toolbar">
  <button type="button" class="pri" onclick="window.print()">🖨 인쇄 / PDF 저장</button>
  <a href="myticket.php">← 등록 확인으로</a>
</div>
<div class="page">
  <div class="cert">
    <div class="cert-top">
      <div class="brand">UNREAL FEST SEOUL <b>2026</b></div>
      <div class="cert-no">발급번호 <?= e($certno) ?></div>
    </div>

    <div class="cert-title">참가확인증</div>
    <div class="cert-sub">CERTIFICATE OF PARTICIPATION</div>

    <table class="cert-tbl">
      <tr><th>성명</th><td><?= e($name) ?></td></tr>
      <?php if ($affil!==''): ?><tr><th>소속</th><td><?= e($affil) ?></td></tr><?php endif; ?>
      <tr><th>참가 티켓</th><td><?= e($product) ?></td></tr>
      <tr><th>행사명</th><td>언리얼 페스트 서울 2026 (Unreal Fest Seoul 2026)</td></tr>
      <tr><th>참가 일자</th><td><?= e($period) ?></td></tr>
      <tr><th>장소</th><td>서울 (오프라인 현장 참가)</td></tr>
    </table>

    <div class="cert-body">
      위 사람은 <span class="em">언리얼 페스트 서울 2026</span>에<br>
      참가하였음을 확인합니다.
    </div>

    <div class="cert-date"><?= e($today) ?></div>
    <div style="text-align:center">
      <span class="cert-issuer">언리얼 페스트 서울 2026 사무국 · 주식회사 그리프<?php if ($SEAL!==''): ?><img class="seal" src="<?= $SEAL ?>" alt="직인"><?php endif; ?></span>
    </div>

    <div class="cert-foot">본 확인증은 온라인으로 발급되었으며, 발급번호로 진위를 확인할 수 있습니다. · 문의: 02-326-3701 · info@epiclounge.co.kr</div>
  </div>
</div>
</body></html>
