<?php
/* Unreal Fest Seoul 2026 — 관리자: 등록 현황 (adm/2026_event2_list.php)
 * Gnuboard 관리자 레이아웃(admin.head/tail = 왼쪽 메뉴 포함) + 자체 scoped CSS.
 * 통계 + 트랙별 인원 그래픽(토글) + 목록 + CSV. PHP 7.0 호환.
 */
$sub_menu = '700320';
include_once('./_common.php');
if (!function_exists('is_admin') || !is_admin($member['mb_id'])) {
    alert('관리자 로그인이 필요합니다.', G5_ADMIN_URL);
}
$g5['title'] = 'UFS 2026 등록 현황';   // 상단 기본 타이틀('시작해요 언리얼 2026') 대체
function e2($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// 검색/필터
$q  = isset($_GET['q'])  ? trim($_GET['q'])  : '';
$st = isset($_GET['st']) ? $_GET['st'] : 'all';
$where = "apply_temp_yn = 'N'";
if ($q !== '') {
    $qe = sql_real_escape_string($q);
    $where .= " AND (apply_user_name LIKE '%$qe%' OR apply_user_email LIKE '%$qe%' OR apply_user_phone LIKE '%$qe%')";
}
if ($st === 'allday')     $where .= " AND apply_pay_status=10 AND apply_product_code='NORMAL_ALL'";
elseif ($st === 'day1')   $where .= " AND apply_pay_status=10 AND apply_product_code='NORMAL_20'";
elseif ($st === 'day2')   $where .= " AND apply_pay_status=10 AND apply_product_code='NORMAL_21'";
elseif ($st === 'online') $where .= " AND apply_pay_status<>0 AND (free_yn='Y' OR apply_product_code='ONLINE')";
elseif ($st === 'cancel') $where .= " AND apply_pay_status=0";

// CSV 내보내기 (admin.head 출력 전에 처리)
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ufs2026_apply_'.date('Ymd').'.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, array('번호','이름','이메일','연락처','직업','회사','직무','산업','광고동의','유형','상품','금액','트랙','티셔츠','상태','TID','등록일'));
    $res = sql_query("SELECT * FROM cb_unreal_2026_event2_apply WHERE $where ORDER BY apply_no DESC");
    if ($res) { while ($r = $res->fetch_assoc()) {
        $type = ($r['free_yn']==='Y'||$r['apply_product_code']==='ONLINE') ? '온라인' : '오프라인';
        $stt = ((int)$r['apply_pay_status']===10)?'완료':(((int)$r['apply_pay_status']===1)?'입금대기':'취소');
        $ad = ($r['apply_user_event_agree']==='1')?'동의':'미동의';
        fputcsv($out, array($r['apply_no'],$r['apply_user_name'],$r['apply_user_email'],$r['apply_user_phone'],$r['apply_user_job'],$r['apply_user_company'],$r['apply_user_grade'],$r['apply_user_ex1'],$ad,$type,$r['apply_product_name'],$r['apply_product_price'],$r['apply_track'],$r['apply_tshirt'],$stt,$r['pay_tid'],$r['apply_reg_datetime']));
    }}
    fclose($out); exit;
}

// 결제 취소 처리 (관리자) — admin.head 출력 전에 처리 후 redirect
// 유료건은 INICIS 자동 환불 시도(운영모드에서만 실제 환불). 환불 실패 시 상태 미변경 + 안내.
if (isset($_POST['cancel_no'])) {
    $cno = (int)$_POST['cancel_no'];
    $done = 'cancel';
    if ($cno > 0) {
        $crow = sql_fetch("SELECT free_yn, apply_product_code, pay_tid, pay_paymethod FROM cb_unreal_2026_event2_apply WHERE apply_no='".$cno."' AND apply_temp_yn='N'");
        $refund_failed = false;
        if ($crow) {
            $paid_cancel = ($crow['free_yn']==='N' && $crow['apply_product_code']!=='ONLINE' && trim((string)$crow['pay_tid'])!=='');
            if ($paid_cancel) {
                require_once(__DIR__.'/../unrealfest2026/_refund.php');
                $rf = ufs_inicis_refund($crow['pay_tid'], isset($crow['pay_paymethod'])?$crow['pay_paymethod']:'', '관리자 취소');
                if (empty($rf['skipped']) && empty($rf['ok'])) { $refund_failed = true; }
            }
            if (!$refund_failed) {
                sql_query("UPDATE cb_unreal_2026_event2_apply SET apply_pay_status=0, refund_date=now() WHERE apply_no='".$cno."' AND apply_temp_yn='N'");
            } else {
                $done = 'refundfail';
            }
        }
    }
    $rp = isset($_GET['p']) ? max(1,(int)$_GET['p']) : 1;
    header('Location: 2026_event2_list.php?'.http_build_query(array('q'=>$q,'st'=>$st,'p'=>$rp,'done'=>$done)));
    exit;
}

// 등록 삭제 처리 (관리자) — DB 행 영구 삭제 + QR 파일 정리
if (isset($_POST['delete_no'])) {
    $dno = (int)$_POST['delete_no'];
    if ($dno > 0) {
        sql_query("DELETE FROM cb_unreal_2026_event2_apply WHERE apply_no='".$dno."'");
        @unlink(__DIR__.'/../unrealfest2026/qrdata/'.$dno.'.jpg');
        @unlink(__DIR__.'/../unrealfest2026/qrdata/'.$dno.'.png');
    }
    $rp = isset($_GET['p']) ? max(1,(int)$_GET['p']) : 1;
    header('Location: 2026_event2_list.php?'.http_build_query(array('q'=>$q,'st'=>$st,'p'=>$rp,'done'=>'delete')));
    exit;
}

function cnt2($w){ $r = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE $w"); return $r ? (int)$r['c'] : 0; }
$stat = array(
  'total'  => cnt2("apply_temp_yn='N'"),
  'allday' => cnt2("apply_temp_yn='N' AND apply_pay_status=10 AND apply_product_code='NORMAL_ALL'"),
  'day1'   => cnt2("apply_temp_yn='N' AND apply_pay_status=10 AND apply_product_code='NORMAL_20'"),
  'day2'   => cnt2("apply_temp_yn='N' AND apply_pay_status=10 AND apply_product_code='NORMAL_21'"),
  'online' => cnt2("apply_temp_yn='N' AND apply_pay_status<>0 AND (free_yn='Y' OR apply_product_code='ONLINE')"),
  'cancel' => cnt2("apply_temp_yn='N' AND apply_pay_status=0"),
);

// 트랙별 인원 (확정 오프라인)
$trackDefs = array(
  'DAY1_TR1'=>array('게임: 프로그래밍','#307FE2'), 'DAY1_TR2'=>array('게임: 아트','#FF8F1C'),
  'DAY1_TR3'=>array('미디어 & 엔터','#FA4616'),   'DAY1_TR4'=>array('산업 & 시뮬','#DD0AB2'),
  'DAY2_TR1'=>array('게임: 프로그래밍','#307FE2'), 'DAY2_TR2'=>array('게임: 아트','#FF8F1C'),
  'DAY2_TR3'=>array('미디어 & 엔터','#FA4616'),   'DAY2_TR4'=>array('산업 & 시뮬','#DD0AB2'),
);
$trackCnt = array();
foreach ($trackDefs as $k=>$d) {
    $trackCnt[$k] = cnt2("apply_temp_yn='N' AND apply_pay_status<>0 AND apply_track LIKE '%".sql_real_escape_string($k)."%'");
}
// 정원(2026_event_ticket.date1) 연동
$trackCap = array();
$rc = sql_query("SELECT name,date1 FROM 2026_event_ticket");
if ($rc) { while ($x = $rc->fetch_assoc()) { $trackCap[$x['name']] = (int)$x['date1']; } }

$page = isset($_GET['p']) ? max(1,(int)$_GET['p']) : 1;
$per = 50; $off = ($page-1)*$per;
$tr = sql_fetch("SELECT count(*) c FROM cb_unreal_2026_event2_apply WHERE $where");
$total = $tr ? (int)$tr['c'] : 0;
$pages = max(1, ceil($total/$per));
$list = sql_query("SELECT * FROM cb_unreal_2026_event2_apply WHERE $where ORDER BY apply_no DESC LIMIT $off,$per");
$self = '2026_event2_list.php';

include_once('./admin.head.php');   // ← 왼쪽 관리자 메뉴 + 상단 chrome
?>
<style>
#container_title{display:none}   /* admin.head 기본 타이틀 숨김(자체 타이틀 사용) */
.ufs26{padding:8px 4px 40px;font-family:'Apple SD Gothic Neo','Malgun Gothic',sans-serif;color:#1f2330}
.ufs26 h1{font-size:1.4em;font-weight:800;margin:0 0 18px}
.ufs26 .cards{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:22px}
.ufs26 .card{background:#fff;border:1px solid #e6e8ee;border-radius:10px;padding:16px}
.ufs26 .card .lbl{font-size:12px;color:#8a90a2;margin-bottom:4px}
.ufs26 .card .num{font-size:26px;font-weight:800}
.ufs26 .toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:14px}
.ufs26 .toolbar input,.ufs26 .toolbar select{height:38px;box-sizing:border-box;border:1px solid #d6dae3;border-radius:8px;font-size:13px;padding:0 12px;background:#fff}
.ufs26 .toolbar input{width:240px}
.ufs26 .btn{height:38px;box-sizing:border-box;border:1px solid #d6dae3;border-radius:8px;padding:0 16px;font-size:13px;font-weight:700;cursor:pointer;background:#fff;color:#3a4153;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;line-height:1}
.ufs26 .btn.pri{background:#00C1D5;border-color:#00C1D5;color:#062a2f}
.ufs26 .bar-rem{width:74px;text-align:right;font-size:12px;font-weight:700;flex:none}
.ufs26 .tabs{display:flex;gap:6px;margin-bottom:14px}
.ufs26 .tab{border:1px solid #d6dae3;background:#fff;border-radius:8px;padding:8px 18px;font-size:13px;font-weight:700;cursor:pointer;color:#6b7280}
.ufs26 .tab.on{background:#0e1424;color:#fff;border-color:#0e1424}
.ufs26 table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e6e8ee;border-radius:10px;overflow:hidden;font-size:13px}
.ufs26 th{background:#f5f6fa;text-align:left;padding:11px 12px;color:#6b7280;font-weight:700}
.ufs26 td{padding:10px 12px;border-top:1px solid #eef0f5}
.ufs26 tr:hover td{background:#fafbfd}
.ufs26 .badge{font-weight:800}
.ufs26 .trackwrap{display:grid;grid-template-columns:1fr 1fr;gap:30px;background:#fff;border:1px solid #e6e8ee;border-radius:10px;padding:24px}
.ufs26 .daytitle{font-weight:800;margin:0 0 14px;font-size:15px}
.ufs26 .bar-row{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.ufs26 .bar-label{width:120px;font-size:13px;color:#3a4153;flex:none}
.ufs26 .bar-track{flex:1;background:#eef0f5;border-radius:6px;height:24px;position:relative;overflow:hidden}
.ufs26 .bar-fill{height:100%;border-radius:6px;min-width:2px;transition:width .4s}
.ufs26 .bar-num{width:48px;text-align:right;font-weight:800;font-size:14px;flex:none}
.ufs26 .pg{display:flex;gap:4px;justify-content:center;margin-top:20px;flex-wrap:wrap}
.ufs26 .pg a{border:1px solid #d6dae3;border-radius:6px;padding:6px 11px;font-size:13px;color:#3a4153;text-decoration:none}
.ufs26 .pg a.on{background:#00C1D5;border-color:#00C1D5;color:#062a2f;font-weight:800}
@media(max-width:900px){.ufs26 .cards{grid-template-columns:repeat(2,1fr)}.ufs26 .trackwrap{grid-template-columns:1fr}}
</style>

<div class="ufs26">
  <h1>UFS 2026 등록 현황</h1>

  <div class="cards">
    <div class="card"><div class="lbl">총 등록</div><div class="num"><?= number_format($stat['total']) ?></div></div>
    <div class="card"><div class="lbl">오프라인(양일권)</div><div class="num" style="color:#00C1D5"><?= number_format($stat['allday']) ?></div></div>
    <div class="card"><div class="lbl">오프라인 20일</div><div class="num" style="color:#307FE2"><?= number_format($stat['day1']) ?></div></div>
    <div class="card"><div class="lbl">오프라인 21일</div><div class="num" style="color:#DD0AB2"><?= number_format($stat['day2']) ?></div></div>
    <div class="card"><div class="lbl">온라인</div><div class="num" style="color:#2aa7b5"><?= number_format($stat['online']) ?></div></div>
    <div class="card"><div class="lbl">취소</div><div class="num" style="color:#9aa0af"><?= number_format($stat['cancel']) ?></div></div>
  </div>

  <!-- 토글 -->
  <div class="tabs">
    <button type="button" class="tab on" id="tabList" onclick="ufsView('list')">목록</button>
    <button type="button" class="tab" id="tabTrack" onclick="ufsView('track')">트랙별 인원 현황</button>
  </div>

  <!-- 트랙 그래픽 -->
  <div id="viewTrack" style="display:none">
    <div style="display:flex;justify-content:flex-end;margin-bottom:10px"><a href="2026_event2_remain.php" class="btn">정원 설정</a></div>
    <div class="trackwrap">
      <?php foreach (array('DAY1'=>'Day 1 · 8월 20일(목)','DAY2'=>'Day 2 · 8월 21일(금)') as $day=>$dtitle): ?>
      <div>
        <p class="daytitle"><?= e2($dtitle) ?></p>
        <?php foreach ($trackDefs as $k=>$d): if (strpos($k,$day)!==0) continue;
          $c = $trackCnt[$k]; $cap = isset($trackCap[$k]) ? $trackCap[$k] : 0;
          $w = $cap>0 ? min(100, round($c / $cap * 100)) : 0; $rem = $cap - $c; ?>
        <div class="bar-row">
          <div class="bar-label"><?= e2($d[0]) ?></div>
          <div class="bar-track"><div class="bar-fill" style="width:<?= $w ?>%;background:<?= $d[1] ?>"></div></div>
          <div class="bar-num"><?= number_format($c) ?><span style="color:#9aa0af;font-weight:500">/<?= number_format($cap) ?></span></div>
          <div class="bar-rem" style="color:<?= $rem<=0?'#e0492f':($rem<50?'#FF8F1C':'#0c8b5a') ?>">잔여 <?= number_format($rem) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 목록 -->
  <div id="viewList">
    <form method="get" class="toolbar">
      <input type="text" name="q" value="<?= e2($q) ?>" placeholder="이름·이메일·연락처 검색">
      <select name="st">
        <?php foreach (array('all'=>'전체','allday'=>'오프라인(양일권)','day1'=>'오프라인 20일','day2'=>'오프라인 21일','online'=>'온라인','cancel'=>'취소') as $k=>$v): ?>
        <option value="<?= $k ?>" <?= $st===$k?'selected':'' ?>><?= e2($v) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn pri">검색</button>
      <a href="<?= $self ?>?<?= e2(http_build_query(array('q'=>$q,'st'=>$st,'export'=>1))) ?>" class="btn">CSV 내보내기</a>
      <span style="margin-left:auto;color:#8a90a2;font-size:13px">총 <?= number_format($total) ?>건</span>
    </form>

    <?php if (isset($_GET['done']) && $_GET['done']==='cancel'): ?>
    <div style="background:rgba(57,217,138,.1);border:1px solid rgba(57,217,138,.3);color:#39d98a;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:13px">선택한 등록이 취소 처리되었습니다.</div>
    <?php elseif (isset($_GET['done']) && $_GET['done']==='delete'): ?>
    <div style="background:rgba(255,107,107,.1);border:1px solid rgba(255,107,107,.3);color:#ff6b6b;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:13px">선택한 등록이 삭제되었습니다.</div>
    <?php elseif (isset($_GET['done']) && $_GET['done']==='refundfail'): ?>
    <div style="background:rgba(255,143,28,.12);border:1px solid rgba(255,143,28,.4);color:#FF8F1C;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:13px">INICIS 환불에 실패하여 취소 처리되지 않았습니다. INICIS 상점관리자에서 결제 상태를 확인해 주세요.</div>
    <?php endif; ?>

    <div style="overflow-x:auto">
    <table>
      <thead><tr><th>이름</th><th>이메일</th><th>연락처</th><th>유형</th><th>상품</th><th>트랙</th><th>광고</th><th>상태</th><th>등록일</th><th>취소</th><th>삭제</th></tr></thead>
      <tbody>
      <?php if ($list && $list->num_rows): while ($r = $list->fetch_assoc()):
        $type = ($r['free_yn']==='Y'||$r['apply_product_code']==='ONLINE') ? '온라인' : '오프라인';
        $ps = (int)$r['apply_pay_status'];
        $stt = $ps===10?'완료':($ps===1?'입금대기':'취소');
        $stc = $ps===10?'#00C1D5':($ps===1?'#FF8F1C':'#9aa0af');
        $ad = ($r['apply_user_event_agree']==='1'); ?>
        <tr>
          <td style="font-weight:600"><?= e2($r['apply_user_name']) ?></td>
          <td style="color:#6b7280"><?= e2($r['apply_user_email']) ?></td>
          <td style="color:#6b7280"><?= e2($r['apply_user_phone']) ?></td>
          <td><?= e2($type) ?></td>
          <td><?= e2($r['apply_product_name']) ?></td>
          <td style="color:#9aa0af;font-size:12px"><?= e2($r['apply_track']) ?></td>
          <td class="badge" style="color:<?= $ad?'#39d98a':'#9aa0af' ?>"><?= $ad?'동의':'미동의' ?></td>
          <td class="badge" style="color:<?= $stc ?>"><?= e2($stt) ?></td>
          <td style="color:#9aa0af;font-size:12px"><?= e2($r['apply_reg_datetime']) ?></td>
          <td style="white-space:nowrap">
            <?php if ($ps !== 0): ?>
              <form method="post" action="2026_event2_list.php?<?= e2(http_build_query(array('q'=>$q,'st'=>$st,'p'=>$page))) ?>" style="display:inline" onsubmit="return confirm('[<?= e2($r['apply_user_name']) ?>] 님의 등록을 취소 처리할까요?\n(상태가 취소로 바뀝니다. 실결제 환불은 INICIS에서 별도 처리)');">
                <input type="hidden" name="cancel_no" value="<?= e2($r['apply_no']) ?>">
                <button type="submit" class="btn" style="border-color:rgba(255,143,28,.4);color:#FF8F1C;padding:4px 10px;font-size:12px"><?= $type==='온라인'?'등록취소':'결제취소' ?></button>
              </form>
            <?php else: ?>
              <span style="color:#9aa0af;font-size:12px">취소됨</span>
            <?php endif; ?>
          </td>
          <td style="white-space:nowrap">
            <form method="post" action="2026_event2_list.php?<?= e2(http_build_query(array('q'=>$q,'st'=>$st,'p'=>$page))) ?>" style="display:inline" onsubmit="return confirm('[<?= e2($r['apply_user_name']) ?>] 등록을 완전히 삭제할까요?\n삭제하면 복구할 수 없습니다.');">
              <input type="hidden" name="delete_no" value="<?= e2($r['apply_no']) ?>">
              <button type="submit" class="btn" style="border-color:rgba(255,107,107,.5);color:#ff6b6b;padding:4px 10px;font-size:12px">삭제</button>
            </form>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="11" style="padding:40px;text-align:center;color:#9aa0af">등록 내역이 없습니다.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="pg">
      <?php for ($i=1;$i<=$pages;$i++): ?>
        <a href="<?= $self ?>?<?= e2(http_build_query(array('q'=>$q,'st'=>$st,'p'=>$i))) ?>" class="<?= $i===$page?'on':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function ufsView(v){
  document.getElementById('viewList').style.display  = (v==='list')?'':'none';
  document.getElementById('viewTrack').style.display = (v==='track')?'':'none';
  document.getElementById('tabList').className  = 'tab'+(v==='list'?' on':'');
  document.getElementById('tabTrack').className = 'tab'+(v==='track'?' on':'');
}
</script>
<?php
include_once('./admin.tail.php');
