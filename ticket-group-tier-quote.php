<?php
/* Unreal Fest Seoul 2026 — 단체 견적서 생성 (ticket-group-tier-quote.php) [티어 단체등록]
 * 무통장 입금 단체 등록용 견적서 출력. 라이브 배포 전 검증용 복제본.
 *
 * 입력(둘 중 하나):
 *   A) POST : 확인 화면(ticket-group-tier-confirm.php)의 입력 필드 → "등록 전" 견적
 *   B) GET  : g=<grp_no>&t=<grp_code>          → "등록 후"(완료 화면) DB 로드 견적
 * 출력(fmt):
 *   fmt=print (기본) : 인쇄 최적화 HTML (브라우저에서 PDF로 저장)
 *   fmt=doc          : HTML 기반 Word(.doc) 다운로드 (라이브러리 불필요·한글 안전)
 *
 * 금액: 티켓가는 부가세 포함가 → 공급가액 = round(합계/1.1), 부가세 = 합계 - 공급가액.
 * PHP 7.0 호환.
 */
require __DIR__ . '/_ticket_init.php'; // common.php + e() + $UFS_TRACKS + _pricing
require_once __DIR__ . '/_reg_gate.php';
if (ufs_reg_group_closed()) ufs_reg_closed_page('단체 등록 마감');   // 단체 마감

/* ── 공급자(그리프) 정보 — footer(_pf_footer.php)와 동일 ── */
$SUPPLIER = array(
    'name'   => '주식회사 그리프',
    'ceo'    => '황성현',
    'biznum' => '859-88-00263',
    'addr'   => '서울 성동구 광나루로8길 31, SK V1 CENTER2, 1102-1103호',
    'tel'    => '02-326-3701',
    'email'  => 'info@epiclounge.co.kr',
    'bank'   => '국민은행 98983700004185 (주)그리프',
);
$BANK_DAYS = 5;   // 입금 기한(일) — confirm과 동일
$QUOTE_VALID_DAYS = 14; // 견적 유효기간(일)

$PRODNAME = array('NORMAL_ALL'=>'양일권 (8.20~21)','NORMAL_20'=>'1일권 (Day1·8.20)','NORMAL_21'=>'1일권 (Day2·8.21)');
$PRODDAYS = array('NORMAL_ALL'=>array('1','2'),'NORMAL_20'=>array('1'),'NORMAL_21'=>array('2'));

function qp($k){ return isset($_POST[$k]) ? trim($_POST[$k]) : ''; }
function qarr($k){ return (isset($_POST[$k]) && is_array($_POST[$k])) ? $_POST[$k] : array(); }

// 서버 쿠폰 검증 → 할인율(%) 또는 0 (confirm과 동일)
function q_coupon_percent($code){
    $code = strtoupper(trim($code)); if ($code==='') return 0;
    $r = @sql_fetch("SELECT * FROM cb_unreal_2026_coupon WHERE cp_code='".sql_real_escape_string($code)."' LIMIT 1");
    if (!$r || $r['cp_active']!=='Y') return 0;
    if (!empty($r['cp_expire']) && $r['cp_expire']!=='0000-00-00' && $r['cp_expire'] < date('Y-m-d')) return 0;
    if ((int)$r['cp_max']>0 && (int)$r['cp_used']>=(int)$r['cp_max']) return 0;
    return (int)$r['cp_percent'];
}

/* ───────────────────────── 데이터 수집 ───────────────────────── */
$rep = array('name'=>'','company'=>'','email'=>'','phone'=>'','biznum'=>'');
$tax = array('req'=>'N','addr'=>'','ceo'=>'','biztype'=>'','bizitem'=>'');
$attendees = array();          // 각: role,ticket,price
$sumOrig = 0; $total = 0; $eff = 0; $disc_src = ''; $coupon_code = '';
$grp_code = ''; $reg_date = date('Y-m-d');

$grp_no = isset($_GET['g']) ? (int)$_GET['g'] : 0;
$tok    = isset($_GET['t']) ? trim($_GET['t']) : '';

if ($grp_no > 0) {
    /* ── B) DB 로드 (등록 후) ── */
    $g = sql_fetch("SELECT * FROM cb_unreal_2026_group WHERE grp_no=".$grp_no." LIMIT 1");
    if (!$g || $g['grp_code'] !== $tok) { exit('잘못된 접근입니다.'); }
    $rep = array('name'=>$g['rep_name'],'company'=>$g['rep_company'],'email'=>$g['rep_email'],'phone'=>$g['rep_phone'],'biznum'=>$g['rep_biznum']);
    $tax = array('req'=>$g['tax_request'],'addr'=>$g['tax_addr'],'ceo'=>$g['tax_ceo'],'biztype'=>$g['tax_biztype'],'bizitem'=>$g['tax_bizitem']);
    $eff = (int)$g['discount_pct']; $coupon_code = $g['coupon_code'];
    $disc_src = ($coupon_code!=='') ? 'coupon' : (($eff>0)?'group':'');
    $grp_code = $g['grp_code'];
    $reg_date = $g['reg'] ? substr($g['reg'],0,10) : date('Y-m-d');
    $total = (int)$g['total_amount'];
    $res = sql_query("SELECT role,ticket,price FROM cb_unreal_2026_group_member WHERE grp_no=".$grp_no." ORDER BY gm_no ASC");
    if ($res) { while ($m = $res->fetch_assoc()) {
        if (!isset($PRODNAME[$m['ticket']])) continue; // NONE(결제만) 등 제외
        $attendees[] = array('role'=>$m['role'],'ticket'=>$m['ticket'],'price'=>(int)$m['price']);
        $sumOrig += ufs_ticket_orig($m['ticket']);
    }}
} else {
    /* ── A) POST 재계산 (등록 전) — confirm과 동일 로직 ── */
    $rep = array(
        'name'=>qp('apply_user_name'), 'company'=>qp('apply_user_company'), 'email'=>qp('apply_user_email'),
        'phone'=>qp('apply_user_phone'), 'biznum'=>qp('apply_user_biznum'),
        'ticket'=>qp('rep_ticket'),
    );
    $paymethod = (qp('group_paymethod')==='bank') ? 'bank' : 'card';
    $coupon_code = strtoupper(qp('coupon_code'));
    $tax = array(
        'req'=>(($paymethod==='bank' && qp('tax_request')==='Y')?'Y':'N'),
        'addr'=>qp('tax_addr'),'ceo'=>qp('tax_ceo'),'biztype'=>qp('tax_biztype'),'bizitem'=>qp('tax_bizitem'),
    );
    if ($tax['req']!=='Y') { $tax['addr']=$tax['ceo']=$tax['biztype']=$tax['bizitem']=''; }

    // 참석자(대표자 + 멤버). 대표자 NONE(결제만)=제외 (confirm과 동일)
    $rep_attend = ($rep['ticket']!=='' && $rep['ticket']!=='NONE') ? 'Y' : 'N';
    if ($rep_attend==='Y' && isset($PRODNAME[$rep['ticket']])) {
        $attendees[] = array('role'=>'rep','ticket'=>$rep['ticket'],'price'=>0);
    }
    $mName=qarr('member_name'); $mTicket=qarr('member_ticket');
    foreach (array_keys($mName) as $k) {
        if (trim($mName[$k])==='') continue;
        $tk = isset($mTicket[$k]) ? trim($mTicket[$k]) : '';
        if (!isset($PRODNAME[$tk])) continue;
        $attendees[] = array('role'=>'member','ticket'=>$tk,'price'=>0);
    }

    // 유효 할인율 (confirm과 동일: 쿠폰 모드=쿠폰% / 그 외=[티어 단체등록] 인원 구간별 티어)
    require_once __DIR__ . '/_group_tier.php';
    $cpct = q_coupon_percent($coupon_code);
    if (ufs_group_coupon_mode()) {
        $eff = (int)$cpct;
        $disc_src = ($cpct>0) ? 'coupon' : ''; if ($cpct<=0) $coupon_code='';
    } else {
        $eff = ufs_group_tier_pct(count($attendees));
        $disc_src = ($eff>0) ? 'group' : '';
        $coupon_code=''; $cpct=0;
    }
    foreach ($attendees as &$a) {
        $o = ufs_ticket_orig($a['ticket']);
        $a['price'] = (int)(round(($o*(100-$eff)/100)/100)*100);
        $sumOrig += $o; $total += $a['price'];
    }
    unset($a);
    $grp_code = ''; // 등록 전 → 견적번호 임시 생성
}

if (count($attendees) === 0) { exit('견적 대상 인원이 없습니다. 입력값을 확인해 주세요.'); }

/* ── 라인 아이템: 티켓 종류별 묶음 (수량 × 단가) ── */
$lines = array(); // ticket => array(qty, orig, unit, amount)
foreach ($attendees as $a) {
    $c = $a['ticket'];
    $o = ufs_ticket_orig($c);
    if (!isset($lines[$c])) $lines[$c] = array('qty'=>0,'orig'=>$o,'unit'=>$a['price'],'amount'=>0);
    $lines[$c]['qty']    += 1;
    $lines[$c]['orig']    = $o;         // 정상가(단가 표기용)
    $lines[$c]['unit']    = $a['price'];
    $lines[$c]['amount'] += $a['price'];
}
// 코드 표시 순서 고정
$ORDER = array('NORMAL_ALL','NORMAL_20','NORMAL_21');
$ordered = array();
foreach ($ORDER as $c) if (isset($lines[$c])) $ordered[$c] = $lines[$c];
foreach ($lines as $c=>$v) if (!isset($ordered[$c])) $ordered[$c] = $v;
$lines = $ordered;

$headcount = count($attendees);
$supply = (int)round($total / 1.1);  // 공급가액 (부가세 포함가 → 역산)
$vat    = $total - $supply;          // 부가세(10%)

/* ── 견적 메타 ── */
$quote_no  = ($grp_code!=='') ? $grp_code : 'Q'.date('ymdHis').rand(10,99);
$quote_date= date('Y-m-d');
$valid_to  = date('Y-m-d', strtotime('+'.$QUOTE_VALID_DAYS.' days'));

$fmt = (isset($_GET['fmt']) && $_GET['fmt']==='doc') ? 'doc'
     : ((isset($_POST['fmt']) && $_POST['fmt']==='doc') ? 'doc' : 'print');

/* ── 견적서 본문(공용) 렌더 ── */
function render_quote_body($SUPPLIER,$rep,$tax,$lines,$PRODNAME,$headcount,$sumOrig,$total,$supply,$vat,$eff,$disc_src,$coupon_code,$quote_no,$quote_date,$valid_to,$BANK_DAYS,$SEAL){
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
      <tr><td colspan="4" class="r" style="color:#c0392b"><?= $disc_src==='coupon'?'쿠폰 할인 ('.e($coupon_code).' ':'단체 할인 ('.(int)$headcount.'인 · ' ?><?= (int)$eff ?>%)</td><td class="r" style="color:#c0392b">-₩<?= number_format($sumOrig-$total) ?></td></tr>
      <?php endif; ?>
      <tr><td colspan="4" class="r">공급가액</td><td class="r">₩<?= number_format($supply) ?></td></tr>
      <tr><td colspan="4" class="r">부가세 (10%)</td><td class="r">₩<?= number_format($vat) ?></td></tr>
      <tr class="q-total"><td colspan="4" class="r">합계 (VAT 포함)</td><td class="r">₩<?= number_format($total) ?></td></tr>
    </tfoot>
  </table>

  <table class="q-kv q-bank">
    <tr><th>총 인원</th><td><?= (int)$headcount ?>명</td></tr>
    <tr><th>결제 방법</th><td>무통장 입금</td></tr>
    <tr><th>입금 계좌</th><td><?= e($SUPPLIER['bank']) ?></td></tr>
    <tr><th>입금 기한</th><td><?= e(date('Y년 m월 d일', strtotime('+'.$BANK_DAYS.' days'))) ?> (견적일 기준 <?= (int)$BANK_DAYS ?>일 이내)</td></tr>
  </table>

  <ul class="q-note">
    <li>본 견적서는 참가 신청 확인용으로, 정식 세금계산서는 입금 확인 후 발행됩니다.</li>
    <li>티켓 가격은 부가가치세가 포함된 금액입니다.</li>
    <li>단체 등록 및 문의 : <?= e($SUPPLIER['tel']) ?> / <?= e($SUPPLIER['email']) ?></li>
  </ul>

  <div class="q-sign">
    <?= e($quote_date) ?><br>
    <span class="q-sign-line"><span class="q-sign-co"><?= e($SUPPLIER['name']) ?></span> &nbsp; 대표이사 <?= e($SUPPLIER['ceo']) ?><?php if ($SEAL!==''): ?><img class="q-seal" src="<?= $SEAL ?>" alt="직인"><?php else: ?> &nbsp;(인)<?php endif; ?></span>
  </div>
<?php
    return ob_get_clean();
}

/* 한글 금액(간이) */
function num_kr($n){
    $n = (int)$n;
    if ($n === 0) return '영';
    $units4 = array('','만','억','조');
    $small  = array('','십','백','천');
    $digit  = array('영','일','이','삼','사','오','육','칠','팔','구');
    $s = (string)$n; $len = strlen($s);
    $groups = array();
    for ($p=$len; $p>0; $p-=4) { $st=max(0,$p-4); $groups[] = substr($s,$st,$p-$st); }
    $out = '';
    foreach ($groups as $idx=>$grp) {  // $idx: 0=일, 1=만, 2=억, 3=조 (낮은 자리부터)
        if ((int)$grp === 0) continue;
        $gs=''; $glen=strlen($grp);
        for ($j=0;$j<$glen;$j++){ $d=(int)$grp[$j]; $pos=$glen-1-$j; if($d===0)continue; $gs.=$digit[$d].$small[$pos]; }
        $out = $gs.$units4[$idx].$out; // 낮은 자리부터 앞에 붙임
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

/* 직인 이미지 → base64 data URI (인쇄·Word 양쪽 자립 임베드) */
$SEAL = '';
$seal_path = __DIR__ . '/quote_seal.png';
if (is_file($seal_path)) {
    $bin = @file_get_contents($seal_path);
    if ($bin !== false && $bin !== '') $SEAL = 'data:image/png;base64,'.base64_encode($bin);
}

$body = render_quote_body($SUPPLIER,$rep,$tax,$lines,$PRODNAME,$headcount,$sumOrig,$total,$supply,$vat,$eff,$disc_src,$coupon_code,$quote_no,$quote_date,$valid_to,$BANK_DAYS,$SEAL);

if ($fmt === 'doc') {
    /* ── Word(.doc) 다운로드 ── */
    $fname = 'UFS2026_quote_'.preg_replace('/[^A-Za-z0-9_\-]/','',$quote_no).'.doc';
    header('Content-Type: application/msword; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM (Word 한글 인코딩 안전)
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word"><head><meta charset="utf-8">';
    echo '<style>'.$BODY_STYLE.'@page{size:A4;margin:1.5cm}</style></head><body><div class="q-wrap">';
    echo $body;
    echo '</div></body></html>';
    exit;
}

/* ── 인쇄용 HTML ── */
?><!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
  <a class="q-btn sec" href="ticket-group-tier-quote.php?<?= $grp_no>0 ? 'g='.(int)$grp_no.'&t='.rawurlencode($tok) : '' ?>&fmt=doc"
     <?php if ($grp_no<=0): ?>onclick="return docFromForm(event)"<?php endif; ?>>Word(.doc) 저장</a>
  <a class="q-btn" href="javascript:window.print()">인쇄 / PDF 저장</a>
</div>
<div class="q-wrap">
<?= $body ?>
</div>
<?php if ($grp_no<=0): /* 등록 전: doc 다운로드는 POST 재전송 필요 */ ?>
<form id="qdoc" method="post" action="ticket-group-tier-quote.php" style="display:none">
<?php
  foreach (array('apply_user_name','apply_user_company','apply_user_email','apply_user_phone','apply_user_biznum','rep_ticket','group_paymethod','coupon_code','tax_request','tax_addr','tax_ceo','tax_biztype','tax_bizitem') as $hf)
    echo '<input type="hidden" name="'.e($hf).'" value="'.e(qp($hf)).'">';
  foreach (array('member_name'=>qarr('member_name'),'member_ticket'=>qarr('member_ticket')) as $fn=>$arr)
    foreach ($arr as $k=>$v) echo '<input type="hidden" name="'.e($fn).'['.e($k).']" value="'.e($v).'">';
?>
<input type="hidden" name="fmt" value="doc">
</form>
<script>function docFromForm(ev){ev.preventDefault();document.getElementById('qdoc').submit();return false;}</script>
<?php endif; ?>
</body></html>
