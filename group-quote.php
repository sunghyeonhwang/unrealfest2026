<?php
/* Unreal Fest Seoul 2026 — 단체 견적서 독립 생성기 (group-quote.php) [비공개·noindex]
 * 등록 흐름과 무관하게, 티켓 수량만 입력하면 티어 할인이 자동 적용된 견적서를 출력.
 * 입력: qty_all(양일권 수량), qty_day(1일권 수량) + (선택) 업체/담당자/연락처/이메일/사업자번호.
 * 출력: fmt=print(기본) 인쇄용 HTML / fmt=doc Word 다운로드.
 * 티어: 5~9=10% / 10~19=20% / 20~29=30% / 30~39=40% / 40+=50% (5인 미만=할인없음).
 * PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php'; // common.php + e() + _pricing

$SUPPLIER = array(
    'name'   => '주식회사 그리프',
    'ceo'    => '황성현',
    'biznum' => '859-88-00263',
    'addr'   => '서울 성동구 광나루로8길 31, SK V1 CENTER2, 1102-1103호',
    'tel'    => '02-326-3701',
    'email'  => 'info@epiclounge.co.kr',
    'bank'   => '국민은행 98983700004185 (주)그리프',
);
$BANK_DAYS = 5;
$QUOTE_VALID_DAYS = 14;
$PRODNAME = array('NORMAL_ALL'=>'양일권 (8.20~21)','NORMAL_20'=>'1일권 (8.20 또는 8.21 중 택1)');

/* 인원 구간별 티어 할인율(%) */
function ufs_tier_pct($n) {
    $n = (int)$n;
    if ($n >= 40) return 50;
    if ($n >= 30) return 40;
    if ($n >= 20) return 30;
    if ($n >= 10) return 20;
    if ($n >= 5)  return 10;
    return 0; // 5인 미만 = 단체 할인 없음
}

function gq($k){ return isset($_REQUEST[$k]) ? trim($_REQUEST[$k]) : ''; }

$qty_all = max(0, (int)gq('qty_all'));
$qty_day = max(0, (int)gq('qty_day'));
$has_input = ($qty_all + $qty_day) > 0;

/* ───────── 입력 폼 (수량 없을 때) ───────── */
if (!$has_input) {
    ?><!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>단체 견적서 생성 — Unreal Fest Seoul 2026</title>
<style>
body{font-family:'Malgun Gothic','Apple SD Gothic Neo',sans-serif;background:#0e0f14;color:#e4e4e7;margin:0;padding:0}
.wrap{max-width:560px;margin:0 auto;padding:40px 24px}
h1{font-size:22px;font-weight:800;color:#fff;margin:0 0 4px}
p.sub{color:#a1a1aa;font-size:14px;margin:0 0 24px}
.card{background:#16161c;border:1px solid #27272a;border-radius:8px;padding:22px}
label{display:block;font-size:13px;color:#a1a1aa;margin:14px 0 6px}
input[type=number],input[type=text],input[type=email]{width:100%;box-sizing:border-box;background:#0e0f14;border:1px solid #333;border-radius:5px;color:#fff;padding:11px 12px;font-size:15px;outline:none}
input:focus{border-color:#00C1D5}
.row{display:flex;gap:12px}.row>div{flex:1}
.tiers{margin:18px 0 4px;font-size:12px;color:#71717a;line-height:1.7}
.opt{margin-top:18px;border-top:1px solid #27272a;padding-top:6px}
.opt-h{font-size:12px;color:#71717a;margin:10px 0 0}
.chk{display:flex;align-items:center;gap:8px;margin-top:14px;font-size:13px;color:#a1a1aa;cursor:pointer}
.btns{display:flex;gap:10px;margin-top:24px}
button{flex:1;padding:13px;border:0;border-radius:6px;font-weight:800;font-size:15px;cursor:pointer}
.b1{background:#00C1D5;color:#001014}.b2{background:transparent;border:1px solid #333;color:#a1a1aa}
.note{color:#71717a;font-size:12px;margin-top:14px;line-height:1.6}
</style></head>
<body>
<div class="wrap">
  <h1>단체 견적서 생성</h1>
  <p class="sub">티켓 수량만 입력하면 인원 기준 티어 할인이 자동 적용된 견적서가 나옵니다. (내부용)</p>
  <form method="get" class="card">
    <div class="row">
      <div><label>양일권 수량</label><input type="number" name="qty_all" min="0" value="0" inputmode="numeric"></div>
      <div><label>1일권 수량</label><input type="number" name="qty_day" min="0" value="0" inputmode="numeric"></div>
    </div>
    <div class="tiers">· 티어 할인: 5~9인 10% · 10~19인 20% · 20~29인 30% · 30~39인 40% · 40인↑ 50% (5인 미만 할인 없음)<br>· 정상가: 양일권 ₩120,000 / 1일권 ₩60,000</div>

    <div class="row">
      <div>
        <label>결제 방법</label>
        <select name="paymethod" style="width:100%;box-sizing:border-box;background:#0e0f14;border:1px solid #333;border-radius:5px;color:#fff;padding:11px 12px;font-size:15px;outline:none">
          <option value="bank">무통장 입금</option>
          <option value="card">신용카드</option>
        </select>
      </div>
      <div>
        <label>입금 기한 (무통장 · 비우면 견적일+5일)</label>
        <input type="date" name="deadline">
      </div>
    </div>

    <div class="opt">
      <p class="opt-h">수신처 (선택 · 견적서 상단에 표기)</p>
      <label>업체/기관명</label><input type="text" name="company" placeholder="예: 주식회사 OOO">
      <div class="row">
        <div><label>담당자</label><input type="text" name="name" placeholder="홍길동"></div>
        <div><label>연락처</label><input type="text" name="phone" placeholder="010-0000-0000"></div>
      </div>
      <label>이메일</label><input type="email" name="email" placeholder="name@company.com">
      <label class="chk"><input type="checkbox" name="tax_req" value="Y"> 세금계산서 발행용 (사업자번호 표기)</label>
      <label>사업자등록번호 (세금계산서 시)</label><input type="text" name="biznum" placeholder="000-00-00000">
    </div>

    <div class="btns">
      <button type="submit" name="fmt" value="print" class="b1">견적서 보기 (인쇄/PDF)</button>
      <button type="submit" name="fmt" value="doc" class="b2">Word 다운로드</button>
    </div>
    <p class="note">· 견적서는 부가세 포함가 기준, 공급가액/부가세 분리 표기됩니다.<br>· 무통장 입금 안내가 포함됩니다.</p>
  </form>
</div>
</body></html><?php
    exit;
}

/* ───────── 견적 계산 (티어 적용) ───────── */
$rep = array(
    'company'=>gq('company'), 'name'=>gq('name'), 'phone'=>gq('phone'),
    'email'=>gq('email'), 'biznum'=>gq('biznum'),
);
$tax = array('req'=>(gq('tax_req')==='Y'?'Y':'N'),'addr'=>'','ceo'=>'','biztype'=>'','bizitem'=>'');
$paymethod = (gq('paymethod')==='card') ? 'card' : 'bank';

$headcount = $qty_all + $qty_day;
$eff = ufs_tier_pct($headcount);
$disc_src = ($eff > 0) ? 'group' : '';
$coupon_code = '';

$orig_all = ufs_ticket_orig('NORMAL_ALL'); if ($orig_all <= 0) $orig_all = 120000;
$orig_day = ufs_ticket_orig('NORMAL_20');  if ($orig_day <= 0) $orig_day = 60000;

$lines = array();
$sumOrig = 0; $total = 0;
if ($qty_all > 0) {
    $unit = (int)(round(($orig_all*(100-$eff)/100)/100)*100);
    $lines['NORMAL_ALL'] = array('qty'=>$qty_all,'orig'=>$orig_all,'unit'=>$unit,'amount'=>$unit*$qty_all);
    $sumOrig += $orig_all*$qty_all; $total += $unit*$qty_all;
}
if ($qty_day > 0) {
    $unit = (int)(round(($orig_day*(100-$eff)/100)/100)*100);
    $lines['NORMAL_20'] = array('qty'=>$qty_day,'orig'=>$orig_day,'unit'=>$unit,'amount'=>$unit*$qty_day);
    $sumOrig += $orig_day*$qty_day; $total += $unit*$qty_day;
}
$supply = (int)round($total / 1.1);
$vat    = $total - $supply;

$quote_no  = 'Q'.date('ymdHis').rand(10,99);
$quote_date= date('Y-m-d');
$valid_to  = date('Y-m-d', strtotime('+'.$QUOTE_VALID_DAYS.' days'));
$fmt = (gq('fmt')==='doc') ? 'doc' : 'print';

/* ── 견적서 본문 렌더 (ticket-group-quote-test.php와 동일) ── */
function render_quote_body($SUPPLIER,$rep,$tax,$lines,$PRODNAME,$headcount,$sumOrig,$total,$supply,$vat,$eff,$disc_src,$coupon_code,$quote_no,$quote_date,$valid_to,$deadline_str,$SEAL,$paymethod){
    ob_start(); ?>
  <div class="q-head">
    <div class="q-title">견 &nbsp;적 &nbsp;서</div>
    <div class="q-meta">
      <div>견적번호 : <b><?= e($quote_no) ?></b></div>
      <div>견적일자 : <?= e($quote_date) ?></div>
      <div>유효기간 : <?= e($valid_to) ?>까지</div>
    </div>
  </div>
  <table class="q-party">
    <tr>
      <td class="q-party-col">
        <div class="q-party-label">수신 (구매자)</div>
        <table class="q-kv">
          <tr><th>상호</th><td><?= e($rep['company']) ?></td></tr>
          <tr><th>담당자</th><td><?= e($rep['name']) ?></td></tr>
          <?php if ($tax['req']==='Y' && $rep['biznum']!==''): ?><tr><th>사업자번호</th><td><?= e($rep['biznum']) ?></td></tr><?php endif; ?>
          <tr><th>연락처</th><td><?= e($rep['phone']) ?></td></tr>
          <tr><th>이메일</th><td><?= e($rep['email']) ?></td></tr>
        </table>
      </td>
      <td class="q-party-col">
        <div class="q-party-label">공급자</div>
        <table class="q-kv">
          <tr><th>상호</th><td><?= e($SUPPLIER['name']) ?> (대표 <?= e($SUPPLIER['ceo']) ?>)</td></tr>
          <tr><th>사업자번호</th><td><?= e($SUPPLIER['biznum']) ?></td></tr>
          <tr><th>주소</th><td><?= e($SUPPLIER['addr']) ?></td></tr>
          <tr><th>연락처</th><td><?= e($SUPPLIER['tel']) ?></td></tr>
          <tr><th>이메일</th><td><?= e($SUPPLIER['email']) ?></td></tr>
        </table>
      </td>
    </tr>
  </table>
  <div class="q-amt">
    합계금액(부가세 포함) : <b>일금 <?= e(num_kr($total)) ?>원정 (₩<?= number_format($total) ?>)</b>
  </div>
  <table class="q-items">
    <thead>
      <tr><th style="width:6%">No</th><th>품명</th><th style="width:12%">수량</th><th style="width:20%">단가 (정상가)</th><th style="width:22%">금액</th></tr>
    </thead>
    <tbody>
      <?php $i=1; foreach ($lines as $code=>$v): ?>
      <tr>
        <td class="c"><?= $i++ ?></td>
        <td>Unreal Fest Seoul 2026 · <?= e($PRODNAME[$code]) ?></td>
        <td class="c"><?= (int)$v['qty'] ?></td>
        <td class="r">₩<?= number_format($v['orig']) ?></td>
        <td class="r">₩<?= number_format($v['orig']*$v['qty']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="4" class="r muted">정상가 소계</td><td class="r muted">₩<?= number_format($sumOrig) ?></td></tr>
      <?php if ($eff>0): ?>
      <tr><td colspan="4" class="r" style="color:#c0392b">단체 할인 (<?= (int)$headcount ?>인 · <?= (int)$eff ?>%)</td><td class="r" style="color:#c0392b">-₩<?= number_format($sumOrig-$total) ?></td></tr>
      <?php endif; ?>
      <tr><td colspan="4" class="r">공급가액</td><td class="r">₩<?= number_format($supply) ?></td></tr>
      <tr><td colspan="4" class="r">부가세 (10%)</td><td class="r">₩<?= number_format($vat) ?></td></tr>
      <tr class="q-total"><td colspan="4" class="r">합계 (VAT 포함)</td><td class="r">₩<?= number_format($total) ?></td></tr>
    </tfoot>
  </table>
  <table class="q-kv q-bank">
    <tr><th>총 인원</th><td><?= (int)$headcount ?>명</td></tr>
    <tr><th>결제 방법</th><td><?= $paymethod==='card' ? '신용카드' : '무통장 입금' ?></td></tr>
    <?php if ($paymethod !== 'card'): ?>
    <tr><th>입금 계좌</th><td><?= e($SUPPLIER['bank']) ?></td></tr>
    <?php if ($deadline_str !== ''): ?><tr><th>입금 기한</th><td><?= e($deadline_str) ?></td></tr><?php endif; ?>
    <?php endif; ?>
  </table>
  <ul class="q-note">
    <li>본 견적서는 참가 신청 확인용으로, 정식 세금계산서는 결제(입금) 확인 후 발행되며, 카드결제시에는 세금계산서가 발행되지 않습니다.</li>
    <li>티켓 가격은 부가가치세가 포함된 금액입니다. · 단체 할인은 등록 인원 기준으로 적용됩니다.</li>
    <li>단체 등록 및 문의 : <?= e($SUPPLIER['tel']) ?> / <?= e($SUPPLIER['email']) ?></li>
  </ul>
  <div class="q-sign">
    <?= e($quote_date) ?><br>
    <span class="q-sign-line"><span class="q-sign-co"><?= e($SUPPLIER['name']) ?></span> &nbsp; 대표이사 <?= e($SUPPLIER['ceo']) ?><?php if ($SEAL!==''): ?><img class="q-seal" src="<?= $SEAL ?>" alt="직인"><?php else: ?> &nbsp;(인)<?php endif; ?></span>
  </div>
<?php
    return ob_get_clean();
}

function num_kr($n){
    $n = (int)$n; if ($n === 0) return '영';
    $units4 = array('','만','억','조'); $small = array('','십','백','천');
    $digit  = array('영','일','이','삼','사','오','육','칠','팔','구');
    $s = (string)$n; $len = strlen($s); $groups = array();
    for ($p=$len; $p>0; $p-=4) { $st=max(0,$p-4); $groups[] = substr($s,$st,$p-$st); }
    $out = '';
    foreach ($groups as $idx=>$grp) { if ((int)$grp === 0) continue;
        $gs=''; $glen=strlen($grp);
        for ($j=0;$j<$glen;$j++){ $d=(int)$grp[$j]; $pos=$glen-1-$j; if($d===0)continue; $gs.=$digit[$d].$small[$pos]; }
        $out = $gs.$units4[$idx].$out;
    }
    return $out;
}

$BODY_STYLE = <<<CSS
body{font-family:'Malgun Gothic','맑은 고딕','Apple SD Gothic Neo',sans-serif;color:#111;background:#fff;margin:0;font-size:13px;line-height:1.5}
.q-wrap{max-width:760px;margin:0 auto;padding:28px 32px}
.q-head{display:flex;justify-content:space-between;align-items:flex-end;border-bottom:3px solid #111;padding-bottom:10px;margin-bottom:18px}
.q-title{font-size:30px;font-weight:800;letter-spacing:6px}
.q-meta{font-size:12px;text-align:right;line-height:1.7}
.q-party{width:100%;border-collapse:collapse;margin-bottom:16px;table-layout:fixed}
.q-party-col{width:50%;vertical-align:top;padding:0 6px}
.q-party-label{font-weight:700;font-size:12px;color:#555;border-bottom:1px solid #ddd;padding-bottom:4px;margin-bottom:6px}
.q-kv{width:100%;border-collapse:collapse}
.q-kv th{text-align:left;color:#666;font-weight:600;width:78px;padding:2px 6px 2px 0;vertical-align:top;white-space:nowrap}
.q-kv td{padding:2px 0;vertical-align:top;word-break:break-all}
.q-amt{border:1px solid #111;background:#f5f7f8;padding:10px 12px;margin-bottom:14px;font-size:14px}
.q-items{width:100%;border-collapse:collapse;margin-bottom:16px}
.q-items th,.q-items td{border:1px solid #bbb;padding:7px 8px}
.q-items thead th{background:#111;color:#fff;font-weight:600;text-align:center}
.q-items td.c{text-align:center}.q-items td.r{text-align:right}
.q-items tfoot td{background:#fafafa}
.q-items tfoot .muted{color:#888}
.q-items tfoot .q-total td{background:#eef6f7;font-weight:800;font-size:14px}
.q-bank{border:1px solid #ddd;margin-bottom:14px}
.q-bank th,.q-bank td{padding:5px 8px;border-bottom:1px solid #eee}
.q-note{font-size:11.5px;color:#555;padding-left:16px;margin:0 0 22px}
.q-note li{margin:2px 0}
.q-sign{text-align:right;font-size:13px;line-height:1.9}
.q-sign-line{display:inline-block;position:relative}
.q-sign-co{font-weight:800;font-size:15px}
.q-seal{width:60px;height:60px;vertical-align:middle;margin-left:2px;margin-top:-4px}
CSS;

/* 직인 base64 임베드 */
$SEAL = '';
$seal_path = __DIR__ . '/quote_seal.png';
if (is_file($seal_path)) { $bin = @file_get_contents($seal_path); if ($bin !== false && $bin !== '') $SEAL = 'data:image/png;base64,'.base64_encode($bin); }

/* 입금 기한: 입력값(deadline=YYYY-MM-DD) 있으면 지정일, 없으면 견적일+기본일수. 카드는 미표기. */
$deadline_in = gq('deadline');
if ($paymethod === 'card') {
    $deadline_str = '';
} elseif ($deadline_in !== '' && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $deadline_in, $dm)) {
    $deadline_str = sprintf('%d년 %02d월 %02d일까지', (int)$dm[1], (int)$dm[2], (int)$dm[3]);
} else {
    $deadline_str = date('Y년 m월 d일', strtotime('+'.$BANK_DAYS.' days')).' (견적일 기준 '.$BANK_DAYS.'일 이내)';
}

$body = render_quote_body($SUPPLIER,$rep,$tax,$lines,$PRODNAME,$headcount,$sumOrig,$total,$supply,$vat,$eff,$disc_src,$coupon_code,$quote_no,$quote_date,$valid_to,$deadline_str,$SEAL,$paymethod);

if ($fmt === 'doc') {
    $fname = 'UFS2026_quote_'.preg_replace('/[^A-Za-z0-9_\-]/','',$quote_no).'.doc';
    header('Content-Type: application/msword; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Cache-Control: private, max-age=0, must-revalidate'); header('Pragma: public');
    echo "\xEF\xBB\xBF";
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word"><head><meta charset="utf-8">';
    echo '<style>'.$BODY_STYLE.'@page{size:A4;margin:1.5cm}</style></head><body><div class="q-wrap">';
    echo $body;
    echo '</div></body></html>';
    exit;
}
?><!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>견적서 <?= e($quote_no) ?> — Unreal Fest Seoul 2026</title>
<style>
<?= $BODY_STYLE ?>
.q-toolbar{max-width:760px;margin:16px auto 0;padding:0 32px;display:flex;gap:8px;justify-content:flex-end}
.q-btn{display:inline-block;padding:9px 16px;font-size:13px;font-weight:700;border:1px solid #111;background:#111;color:#fff;text-decoration:none;cursor:pointer;border-radius:4px}
.q-btn.sec{background:#fff;color:#111}
@media print{.q-toolbar{display:none}.q-wrap{padding:0}body{font-size:12px}}
</style>
</head>
<body>
<div class="q-toolbar">
  <a class="q-btn sec" href="group-quote.php?qty_all=<?= (int)$qty_all ?>&qty_day=<?= (int)$qty_day ?>&paymethod=<?= e($paymethod) ?>&deadline=<?= rawurlencode($deadline_in) ?>&company=<?= rawurlencode($rep['company']) ?>&name=<?= rawurlencode($rep['name']) ?>&phone=<?= rawurlencode($rep['phone']) ?>&email=<?= rawurlencode($rep['email']) ?>&biznum=<?= rawurlencode($rep['biznum']) ?>&tax_req=<?= e($tax['req']) ?>&fmt=doc">Word(.doc) 저장</a>
  <a class="q-btn" href="javascript:window.print()">인쇄 / PDF 저장</a>
  <a class="q-btn sec" href="group-quote.php">← 다시 입력</a>
</div>
<div class="q-wrap">
<?= $body ?>
</div>
</body></html>
